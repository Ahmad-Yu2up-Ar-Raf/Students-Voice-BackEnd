<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * Ambil daftar post dengan pagination.
     */
    public function index(): JsonResponse
    {
        $posts = Post::orderBy('updated_at', 'desc')
            ->withCount('likes')
            ->withCount('reposts')
            ->paginate(5);

        $data = $posts->map(fn ($post) => $this->transformMediaUrls($post));

        return response()->json([
            'data'    => $data,
            'success' => true,
            'message' => 'Posts retrieved successfully!',
        ], 200);
    }

    /**
     * Buat post baru.
     * Menerima multipart/form-data — file dikirim sebagai binary, BUKAN base64.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            // File upload via FormData binary (bukan base64 JSON lagi)
            'media'          => 'nullable|array|max:10',
            'media.*'        => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',

            // Field teks
            'caption'        => 'required|string|max:1000',
            'tag_category'   => 'required|string',
            'tagline'        => 'required|string|max:250',
            'visibility'     => 'nullable|string|in:public,private',
            'tag_location'   => 'nullable|string|max:250',
        ]);

        try {
            // Validasi ukuran total (Laravel per-file sudah handle max:10240, ini cek total)
            if ($request->hasFile('media')) {
                $totalSize    = 0;
                $maxTotalSize = 100 * 1024 * 1024; // 100 MB

                foreach ($request->file('media') as $file) {
                    $totalSize += $file->getSize();
                }

                if ($totalSize > $maxTotalSize) {
                    return response()->json([
                        'data'    => null,
                        'success' => false,
                        'message' => 'Total file size exceeds 100MB limit',
                    ], 422);
                }
            }

            $post = Post::create([
                'caption'      => $request->caption,
                'tag_category' => $request->tag_category,
                'tagline'      => $request->tagline,
                'visibility'   => $request->visibility ?? 'public',
                'tag_location' => $request->tag_location,
                'user_id'      => 1,
            ]);

            return response()->json([
                'data'    => $post->fresh(),
                'success' => true,
                'message' => 'Post created successfully!',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Post creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'data'    => null,
                'success' => false,
                'message' => 'Error creating post: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan satu post.
     */
    public function show(string $id): JsonResponse
    {
        $post = Post::find($id);

        if (! $post) {
            return response()->json([
                'message' => 'Post not found',
                'success' => false,
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Post retrieved successfully',
            'success' => true,
            'data'    => $this->transformMediaUrls($post),
        ], 200);
    }

    /**
     * Update post.
     * Menerima multipart/form-data dengan:
     *   - media[]        : file baru (binary)
     *   - existing_media : JSON string berisi file lama yang tetap disimpan
     *
     * FIX: Sebelumnya validasi memaksa base64Data:required yang gagal untuk file existing.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            // File baru — nullable, tidak wajib ada jika hanya edit teks
            'media'          => 'nullable|array',
            'media.*'        => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',

            // File lama yang masih dipertahankan — JSON string dari frontend
            'existing_media' => 'nullable|string',

            // Field teks
            'caption'        => 'nullable|string|max:1000',
            'tag_category'   => 'required|string',
            'tagline'        => 'required|string|max:250',
            'visibility'     => 'nullable|string|in:public,private',
            'tag_location'   => 'nullable|string|max:250',
        ]);

        try {
            $post = Post::findOrFail($id);

            $updated = $post->update([
                'caption'      => $request->caption,
                'tag_category' => $request->tag_category,
                'tagline'      => $request->tagline,
                'visibility'   => $request->visibility,
                'tag_location' => $request->tag_location,
                'user_id'      => 1, // TODO: Auth::id()
            ]);

            return response()->json([
                'data'    => $this->transformMediaUrls($post->fresh()),
                'success' => $updated,
                'message' => 'Post updated successfully!',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Post update error', [
                'post_id' => $id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'data'    => null,
                'success' => false,
                'message' => 'Error updating post: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus post.
     */
    public function destroy(string $id): JsonResponse
    {
        $post   = Post::findOrFail($id);
        $result = $post->delete();

        return response()->json([
            'success' => $result,
            'message' => 'Post deleted successfully!',
        ], 200);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /**
     * Transform media array:
     *  - Tambahkan full URL untuk file lokal (punya file_path)
     *  - Hilangkan base64Data dari response (tidak perlu dikirim ke client)
     */
    private function transformMediaUrls(Post $post): Post
    {
        if (! $post->media || ! is_array($post->media)) {
            return $post;
        }

        $post->media = array_map(function (array $media): array {
            if (isset($media['file_path'])) {
                $fullUrl         = url('storage/' . $media['file_path']);
                $media['preview'] = $fullUrl;
                $media['uri']     = $fullUrl;
            }

            unset($media['base64Data']); // Jangan expose base64 ke client
            return $media;
        }, $post->media);

        return $post;
    }
}
