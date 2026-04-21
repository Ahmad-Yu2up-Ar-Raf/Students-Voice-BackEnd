<?php

namespace App\Http\Controllers;

use App\Models\Repost;
use Illuminate\Http\Request;

class RepostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'post_id' => 'required|min:0|numeric|exists:posts,id'

        ]);


        $data = Repost::create([
       ...$request->all(),
        'user_id' => 1
        ]);

        return response()->json([
        'succes' => true,
        'data' => $data,
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Repost $repost)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Repost $repost)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Repost $repost)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Repost::findOrFail($id);

        $delete = $data->delete();

        return response()->json([
                'succes' => true,
            'data' => $delete
        ], 200);
    }
}
