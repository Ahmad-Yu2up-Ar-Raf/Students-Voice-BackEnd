<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repost extends Model
{
      protected $table = 'repost';
    protected $fillable = [
    'post_id'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function post(): BelongsTo {
        return $this->belongsTo(Post::class, 'post_id');
    }

}
