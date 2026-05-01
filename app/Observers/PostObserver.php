<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\FileUploadService;
use App\Services\JsonFileUploadService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostObserver
{
    /**
     * Flag untuk mencegah double execution.
     * Static karena observer di-share dalam satu request lifecycle.
     */
    protected static bool $isProcessingFiles = false;

    public function __construct(
        protected FileUploadService $fileUploadService,
        protected JsonFileUploadService $jsonFileUploadService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // CREATED
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Upload file setelah post dibuat.
     * File dikirim via FormData binary (media[]) — bukan base64 JSON lagi.
     */
    public function created(Post $post): void
    {
        if (self::$isProcessingFiles) {
            return;
        }

        // Guard: hanya proses jika ada file yang dikirim
        if (! request()->hasFile('media')) {
            return;
        }

        self::$isProcessingFiles = true;

        try {
            $uploadedFiles = $this->fileUploadService->handleMultipleUploads(
                request()->file('media'),
                'Post'
            );

            if (empty($uploadedFiles)) {
                throw new \Exception('Gagal mengupload file. Pastikan format file valid (jpg, jpeg, png, gif, webp).');
            }

            // updateQuietly agar tidak trigger observer lagi
            $post->updateQuietly(['media' => $uploadedFiles]);

            Log::info('PostObserver: files uploaded on create', [
                'post_id'     => $post->id,
                'media_count' => count($uploadedFiles),
            ]);

        } catch (\Exception $e) {
            Log::error('PostObserver: file upload error on create', [
                'post_id' => $post->id,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            self::$isProcessingFiles = false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPDATING
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Handle perubahan media saat post di-update.
     *
     * Strategy:
     *   - existing_media (JSON string) = file lama yang user mau tetap simpan
     *   - media[] (FormData file)      = file baru yang user upload
     *   - File lama yang tidak ada di existing_media → dihapus dari storage
     *
     * FIX: Tidak lagi membaca base64Data — menggunakan binary file upload.
     */
    public function updating(Post $post): void
    {
        if (self::$isProcessingFiles) {
            return;
        }

        $hasNewFiles      = request()->hasFile('media');
        $hasExistingMedia = request()->has('existing_media');

        // Tidak ada perubahan media sama sekali — skip
        if (! $hasNewFiles && ! $hasExistingMedia) {
            return;
        }

        self::$isProcessingFiles = true;

        try {
            $oldFiles = $post->getOriginal('media') ?? [];

            // ── 1. Parse file lama yang user mau pertahankan ─────────────────
            $existingFiles = [];
            if ($hasExistingMedia) {
                $rawJson       = request()->input('existing_media', '[]');
                $decoded       = json_decode($rawJson, true);
                $existingFiles = is_array($decoded) ? $decoded : [];
            }

            // ── 2. Upload file baru (binary FormData) ─────────────────────────
            $newUploadedFiles = [];
            if ($hasNewFiles) {
                $newUploadedFiles = $this->fileUploadService->handleMultipleUploads(
                    request()->file('media'),
                    'Post'
                );

                if (empty($newUploadedFiles)) {
                    throw new \Exception('Gagal mengupload file baru. Pastikan format file valid.');
                }
            }

            // ── 3. Gabungkan existing + newly uploaded ────────────────────────
            $finalFiles = array_merge($existingFiles, $newUploadedFiles);

            // ── 4. Hapus file lama yang sudah tidak dipakai ───────────────────
            $filesToDelete = $this->getFilesToDelete($oldFiles, $finalFiles);
            if (! empty($filesToDelete)) {
                $this->jsonFileUploadService->deleteMultipleFiles($filesToDelete);
                Log::info('PostObserver: deleted unused media on update', [
                    'post_id'       => $post->id,
                    'deleted_count' => count($filesToDelete),
                ]);
            }

            // ── 5. Set media baru ke model (akan di-save oleh Eloquent) ───────
            $post->media = $finalFiles;

            Log::info('PostObserver: file update completed', [
                'post_id'     => $post->id,
                'final_count' => count($finalFiles),
            ]);

        } catch (\Exception $e) {
            Log::error('PostObserver: file update error', [
                'post_id' => $post->id,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            self::$isProcessingFiles = false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETING
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Hapus semua file dari storage sebelum post dihapus.
     */
    public function deleting(Post $post): void
    {
        if (self::$isProcessingFiles) {
            return;
        }

        // Hapus cover_image jika ada
        if (
            $post->cover_image &&
            Storage::disk('public')->exists(str_replace('storage/', '', $post->cover_image))
        ) {
            Storage::disk('public')->delete(str_replace('storage/', '', $post->cover_image));
        }

        if (empty($post->media)) {
            return;
        }

        self::$isProcessingFiles = true;

        try {
            // Refresh agar media yang terbaru diambil dari DB
            $post->refresh();

            Log::info('PostObserver: deleting media files', [
                'post_id'     => $post->id,
                'media_count' => count($post->media),
            ]);

            $this->jsonFileUploadService->deleteMultipleFiles($post->media);

            Log::info('PostObserver: media files deleted successfully', [
                'post_id' => $post->id,
            ]);

        } catch (\Exception $e) {
            // Log error tapi jangan throw — kita tetap ingin post-nya terhapus
            Log::error('PostObserver: failed to delete media files', [
                'post_id' => $post->id,
                'error'   => $e->getMessage(),
            ]);
        } finally {
            self::$isProcessingFiles = false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stub hooks (diperlukan interface Eloquent observer)
    // ─────────────────────────────────────────────────────────────────────────

    public function updated(Post $post): void {}
    public function deleted(Post $post): void {}
    public function restored(Post $post): void {}
    public function forceDeleted(Post $post): void {}

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Tentukan file mana yang perlu dihapus dari storage.
     * File dihapus jika ada di $oldFiles tapi file_path-nya tidak ada di $newFiles.
     *
     * @param  array<int, array<string, mixed>> $oldFiles
     * @param  array<int, array<string, mixed>> $newFiles
     * @return array<int, array<string, mixed>>
     */
    private function getFilesToDelete(array $oldFiles, array $newFiles): array
    {
        $keptPaths = array_filter(
            array_map(fn (array $f) => $f['file_path'] ?? null, $newFiles)
        );

        return array_filter(
            $oldFiles,
            fn (array $f) => isset($f['file_path']) && ! in_array($f['file_path'], $keptPaths, true)
        );
    }
}
