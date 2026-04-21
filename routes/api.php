<?php

use App\Http\Controllers\LikesController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RepostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::apiResource('/posts', PostController::class);
Route::apiResource('/likes', LikesController::class);
Route::apiResource('/repost', RepostController::class);
