<?php

namespace App\Models;

use Database\Factories\LikesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Likes extends Model
{
    /** @use HasFactory<LikesFactory> */
    use HasFactory;

    protected $table = 'likes';
    protected $fillable = [
    'user_id',
    'post_id'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function post(): BelongsTo {
        return $this->belongsTo(Post::class, 'post_id');
    }

}
