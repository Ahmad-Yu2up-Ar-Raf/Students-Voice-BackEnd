<?php

namespace App\Http\Controllers;


use App\Models\Post;
use Illuminate\Http\Request;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::orderBy('updated_at', 'desc')
            ->withCount('likes')
            ->withCount('reposts')
            ->paginate(5);

        // Transform media URLs
        $data = $posts->map(function ($post) {
            if ($post->media && is_array($post->media)) {
                $post->media = array_map(function ($media) {
                    // If has local file_path (uploaded from mobile)
                    if (isset($media['file_path'])) {
                        $fullUrl = url('storage/' . $media['file_path']);
                        $media['preview'] = $fullUrl;
                        $media['uri'] = $fullUrl;
                    }
                    // If has external URI (from factory or other source) - keep as is
                    // uri already set - no change needed

                    // Remove sensitive base64Data from response
                    unset($media['base64Data']);
                    return $media;
                }, $post->media);
            }
            return $post;
        });

        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => 'Posts retrieved successfully!'
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


    public function store(Request $request)
    {

        $request->validate([
            'media' => 'array|nullable',
            'media.*.file' => 'array|nullable',
            'media.*.file.name' => 'string|nullable',
            'media.*.file.size' => 'integer|nullable',
            'media.*.file.type' => 'string|nullable',
            'media.*.base64Data' => 'string|nullable',
            'tag_category' => 'string|required',
            'caption' => 'string|required',
            'tagline' => 'string|required|max:250',
            'visibility' => 'string|nullable|max:250',
            'tag_location' => 'string|nullable|max:250',
        ]);

        try {

            $media = $request->input('media', []);
              $hasData = request()->hasFile('media') || (request()->has('media') && is_array(request('media')));
            if($hasData) {

            if (count($media) > 10) {
                return response()->json([
                    'data' => null,
                    'success' => false,
                    'message' => 'Maximum 10 files allowed per post'
                ], 422);
            }


            $totalSize = 0;
            $maxTotalSize = 100 * 1024 * 1024;
            $maxFileSize = 10 * 1024 * 1024;

            foreach ($media as $index => $file) {
                $fileSize = $file['file']['size'] ?? 0;

                if ($fileSize > $maxFileSize) {
                    $sizeMB = ($fileSize / 1024 / 1024);
                    return response()->json([
                        'data' => null,
                        'success' => false,
                        'message' => "File " . ($index + 1) . " exceeds 10MB limit (current: " . number_format($sizeMB, 2) . "MB)"
                    ], 422);
                }

                $totalSize += $fileSize;
            }

            if ($totalSize > $maxTotalSize) {
                return response()->json([
                    'data' => null,
                    'success' => false,
                    'message' => 'Total file size exceeds 100MB limit'
                ], 422);
            }
            }




            $data = Post::create([
                ...$request->all(),
                'user_id' => 1, // TODO: Replace with Auth::id() when authentication is implemented
            ]);

            return response()->json([
                'data' => $data,
                'success' => true,
                'message' => 'Post created successfully!'
            ], 201);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Post creation error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'data' => null,
                'success' => false,
                'message' => 'Error creating post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Post::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Post not found',
                'success' => false,
                'data' => null
            ], 404);
        }

        // Transform media URLs
        if ($data->media && is_array($data->media)) {
            $data->media = array_map(function ($media) {
                // If has local file_path (uploaded from mobile)
                if (isset($media['file_path'])) {
                    $fullUrl = url('storage/' . $media['file_path']);
                    $media['preview'] = $fullUrl;
                    $media['uri'] = $fullUrl;
                }
                // If has external URI (from factory or other source) - keep as is
                // uri already set - no change needed

                // Remove sensitive base64Data from response
                unset($media['base64Data']);
                return $media;
            }, $data->media);
        }

        return response()->json([
            'message' => 'Post retrieved successfully',
            'success' => true,
            'data' => $data
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'media' => 'array|required',
            'media.*.file' => 'array|required',
            'media.*.file.name' => 'string|required',
            'media.*.file.size' => 'integer|required',
            'media.*.file.type' => 'string|required',
            'media.*.base64Data' => 'string|required',
            'tag_category' => 'string|required',
            'caption' => 'string|nullable',
            'tagline' => 'string|required|max:250',
            'tag_location' => 'string|required|max:250',
        ]);

        try {
            $data = Post::findOrFail($id);

            $update = $data->update([
                ...$request->all(),
                'user_id' => 1, // TODO: Replace with Auth::id() when authentication is implemented
            ]);

            return response()->json([
                'data' => $data->refresh(),
                'success' => $update,
                'message' => 'Post updated successfully!'
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Post update error:', [
                'post_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'data' => null,
                'success' => false,
                'message' => 'Error updating post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Post::findOrFail($id);
        $delete = $data->delete();

        return response()->json([
            'success' => $delete,
            'message' => 'Post deleted successfully!'
        ], 200);
    }
}
