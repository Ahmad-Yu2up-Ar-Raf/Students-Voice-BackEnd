<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostStore;
use App\Models\Post;

use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Post::withCount('likes')->withCount('reposts')->paginate(5);

        return response()->json([
        'data' => $data,
        'succes' => true,
        'message' => 'Succes!'
        ],200);
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
    public function store(PostStore $request)
    {


         $data = Post::create([
            ...$request->validate(),
            'user_id' => 1,
         ]);

         return response()->json([
            'data' => $data,
            'succes' => true,
            'user_id' => Auth::id(),
            'message' => 'New Post!'
         ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Post::find($id);

        if(!$data){
        return response()->json([
        'message' => 'Post Not Found',
        'succes' => false,
        'data' => null
        ],404);
        }else{
             return response()->json([
        'message' => 'Post Not Found',
        'succes' => $data,
        'data' => null ], 200);
        }
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
    public function update(PostStore $request, string $id)
    {
        //

        $data = Post::findOrFail($id);

        $update = $data->update([
        ...$request->validate(),
         'user_id' => 1,
        ]);

        return response()->json([
            'succes' => $update,

        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Post::findOrFail($id);
        $delete = $data->delete();

        return response()->json([
            'succes' => $delete
        ],201);
    }
}
