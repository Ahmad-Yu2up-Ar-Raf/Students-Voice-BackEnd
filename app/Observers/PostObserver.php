<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\FileUploadService;
use App\Services\JsonFileUploadService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostObserver
{
    protected $fileUploadService;

    protected $jsonFileUploadService;

    // Flag untuk prproduct double execution
    protected static $isProcessingFiles = false;

    public function __construct(
        FileUploadService $fileUploadService,
        JsonFileUploadService $jsonFileUploadService
    ) {
        $this->fileUploadService = $fileUploadService;
        $this->jsonFileUploadService = $jsonFileUploadService;
    }

    /**
     * Handle the Post "created" product.
     */
    public function created(Post $Post): void
    {
        // Prevent double execution
        if (self::$isProcessingFiles) {
            return;
        }

        // Get media from request
        $media = request()->input('media', []);
        $hasFile = request()->hasFile('media');
        
        // Media is optional - only process if media provided and not empty
        if ($hasFile || (is_array($media) && !empty($media))) {
            self::$isProcessingFiles = true;

            try {
                $uploadedFiles = $this->handleFileUploads();

                // Only throw error if user explicitly tried to upload files but failed
                if (($hasFile || !empty($media)) && empty($uploadedFiles)) {
                    throw new \Exception('Gagal mengupload file. Pastikan file valid.');
                }

                // Update post with uploaded files if any
                if (!empty($uploadedFiles)) {
                    $Post->updateQuietly(['media' => $uploadedFiles]);
                }

                Log::info('Files uploaded on create', [
                    'Post_id' => $Post->id,
                    'media_count' => count($uploadedFiles),
                ]);

            } catch (\Exception $e) {
                Log::error('File upload error on create: '.$e->getMessage());
                throw $e;
            } finally {
                self::$isProcessingFiles = false;
            }
        }
        // Media is optional - no error if not provided
    }


    public function updating(Post $product)
    {


        if (self::$isProcessingFiles) {
            return;
        }


        if (! request()->has('media')) {
            return;
        }

        self::$isProcessingFiles = true;

        try {
            $requestFiles = request('media', []);
            $oldFiles = $product->getOriginal('media') ?? [];

            Log::info('Processing file update', [
                'Post_id' => $product->id,
                'old_media_count' => count($oldFiles),
                'request_media_count' => count($requestFiles),
            ]);

            // Jika media kosong, hapus semua file lama
            if (empty($requestFiles)) {
                if (! empty($oldFiles)) {
                    $this->jsonFileUploadService->deleteMultipleFiles($oldFiles);
                }
                $product->media = [];

                return;
            }

            // Pisahkan media menjadi existing dan new media
            $existingFiles = [];
            $newFilesData = [];

            foreach ($requestFiles as $fileData) {
                if (isset($fileData['file_path']) && ! isset($fileData['base64Data'])) {
                    // File existing (sudah ada di storage)
                    $existingFiles[] = $fileData;
                } elseif (isset($fileData['base64Data']) && isset($fileData['file'])) {
                    // File baru dengan base64 data
                    $newFilesData[] = $fileData;
                }
            }

            Log::info('File categorization', [
                'existing_media' => count($existingFiles),
                'new_media' => count($newFilesData),
            ]);

            // Upload file-file baru saja
            $newUploadedFiles = [];
            if (! empty($newFilesData)) {
                $newUploadedFiles = $this->jsonFileUploadService->handleJsonFileUploads(
                    $newFilesData,
                    'Post'
                );

                if (count($newUploadedFiles) !== count($newFilesData)) {
                    throw new \Exception('Gagal mengupload beberapa file baru.');
                }
            }

            // Gabungkan existing media dengan newly uploaded media
            $finalFiles = array_merge($existingFiles, $newUploadedFiles);

            // Tentukan file mana yang harus dihapus
            $mediaToDelete = $this->getFilesToDelete($oldFiles, $finalFiles);

            // Hapus file yang tidak digunakan lagi
            if (! empty($mediaToDelete)) {
                $this->jsonFileUploadService->deleteMultipleFiles($mediaToDelete);
                Log::info('Deleted unused media', [
                    'Post_id' => $product->id,
                    'deleted_count' => count($mediaToDelete),
                ]);
            }

            // Update media
            $product->media = $finalFiles;

            Log::info('File update completed', [
                'Post_id' => $product->id,
                'final_media_count' => count($finalFiles),
            ]);

        } catch (\Exception $e) {
            Log::error('File update error: '.$e->getMessage());
            throw $e;
        } finally {
            self::$isProcessingFiles = false;
        }
    }

    /**
     * Handle the Post "updated" product.
     */
    public function updated(Post $Post): void
    {
        //
    }

    /**
     * Handle file deletion before Post is deleted
     */
    public function deleting(Post $Post)
    {
        // Prproduct double execution
        if (self::$isProcessingFiles) {
            return;
        }

        if ($Post->cover_image && Storage::disk('public')->exists(str_replace('storage/', '', $Post->cover_image))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $Post->cover_image));
        }

        if (! empty($Post->media)) {
            self::$isProcessingFiles = true;

            try {
                $Post->refresh();

                Log::info('Attempting to delete media for Post deletion', [
                    'Post_id' => $Post->id,
                    'media_count' => count($Post->media),
                ]);

                $this->jsonFileUploadService->deleteMultipleFiles($Post->media);

                Log::info("Files deleted successfully for Post ID: {$Post->id}");

            } catch (\Exception $e) {
                Log::error("Failed to delete media for Post ID: {$Post->id}. Error: ".$e->getMessage());
                // Uncomment jika ingin gagal delete file menggagalkan delete Post
                // throw new \Exception('Gagal menghapus file terkait: ' . $e->getMessage());
            } finally {
                self::$isProcessingFiles = false;
            }
        }
    }

    /**
     * Handle the Post "deleted" product.
     */
    public function deleted(Post $Post): void
    {
        //
    }

    /**
     * Handle the Post "restored" product.
     */
    public function restored(Post $Post): void
    {
        //
    }

    /**
     * Handle the Post "force deleted" product.
     */
    public function forceDeleted(Post $Post): void
    {
        //
    }

    /**
     * Handle file uploads untuk create
     */
    private function handleFileUploads(): array
    {
        $uploadedFiles = [];

        try {
            // Check if media are UploadedFile objects or JSON data
            if (request()->hasFile('media')) {
                // Traditional file upload
                $uploadedFiles = $this->fileUploadService->handleMultipleUploads(
                    request()->file('media'),
                    'Post'
                );

                Log::info('Traditional file upload completed', [
                    'uploaded_count' => count($uploadedFiles),
                ]);

            } elseif (request()->has('media') && is_array(request('media'))) {
                // JSON file data upload
                $uploadedFiles = $this->jsonFileUploadService->handleJsonFileUploads(
                    request('media'),
                    'Post'
                );

                Log::info('JSON file upload completed', [
                    'uploaded_count' => count($uploadedFiles),
                ]);
            }

            return $uploadedFiles;

        } catch (\Exception $e) {
            Log::error('File upload error in observer: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Determine which media should be deleted
     */
    private function getFilesToDelete(array $oldFiles, array $newFiles): array
    {
        $newFilePaths = [];

        // Ambil semua file_path dari file baru
        foreach ($newFiles as $newFile) {
            if (isset($newFile['file_path'])) {
                $newFilePaths[] = $newFile['file_path'];
            }
        }

        $mediaToDelete = [];

        // Cari file lama yang tidak ada lagi di file baru
        foreach ($oldFiles as $oldFile) {
            if (isset($oldFile['file_path']) && ! in_array($oldFile['file_path'], $newFilePaths)) {
                $mediaToDelete[] = $oldFile;
            }
        }

        return $mediaToDelete;
    }
}
