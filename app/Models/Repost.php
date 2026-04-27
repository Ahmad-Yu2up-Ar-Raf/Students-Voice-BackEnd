<?php

namespace App\Models;

use Database\Factories\RepostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repost extends Model
{
    /** @use HasFactory<RepostFactory> */
    use HasFactory;

    protected $table = 'reposts';
    protected $fillable = [
    'post_id',
    'user_id'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function post(): BelongsTo {
        return $this->belongsTo(Post::class, 'post_id');
    }

}
