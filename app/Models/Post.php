<?php

namespace App\Models;

use App\TaglineType;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    protected $table = 'posts';
    protected $fillable = [
        'user_id',
        'media',
        'caption',
        'tag_category',
        'tag_location',
        'tagline'
    ];

    protected $casts = [
        'media' => 'array',
        'caption' => 'string',
        'tag_category' => 'array',
        'tag_location' => 'string',
        'tagline' => TaglineType::class
    ];


    public function user() : BelongsTo{
        return $this->belongsTo(User::class, 'user_id');
    }
    public function likes() : HasMany{
        return $this->hasMany(Likes::class, 'post_id');
    }
    public function reposts() : HasMany{
        return $this->hasMany(Repost::class, 'post_id');
    }
}
