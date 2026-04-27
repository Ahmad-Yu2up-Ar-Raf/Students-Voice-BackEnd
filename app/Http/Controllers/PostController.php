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
        $data = Post::orderBy('updated_at', 'desc')
            ->withCount('likes')
            ->withCount('reposts')
            ->paginate(5);

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'media' => 'array|required',
            'media.*.uri' => 'string|required',
            'media.*.name' => 'string|required',
            'media.*.type' => 'string|nullable',
            'media.*.size' => 'integer|nullable',
            'media.*.mimeType' => 'string|nullable',
            'tag_category' => 'array|required',
            'caption' => 'string|nullable',
            'tagline' => 'string|required|max:250',
            'tag_location' => 'string|required|max:250',
        ]);

        $data = Post::create([
            ...$request->all(),
            'user_id' => 1, // TODO: Replace with Auth::id() when authentication is implemented
        ]);

        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => 'Post created successfully!'
        ], 201);
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
            'media.*.uri' => 'string|required',
            'media.*.name' => 'string|required',
            'media.*.type' => 'string|nullable',
            'media.*.size' => 'integer|nullable',
            'media.*.mimeType' => 'string|nullable',
            'tag_category' => 'array|required',
            'caption' => 'string|nullable',
            'tagline' => 'string|required|max:250',
            'tag_location' => 'string|required|max:250',
        ]);

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
