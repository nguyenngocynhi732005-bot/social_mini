<?php

namespace App\Models;

use App\Models\PostComment;
use App\Models\PostReaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'content',
        'privacy_status',
        'media_path',
        'media_type',
        'image_url',
        'post_type',
        'text_color',
        'font_family',
        'shared_from',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reactions()
    {
        return $this->hasMany(PostReaction::class);
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }

    public function sharedPosts()
    {
        return $this->hasMany(self::class, 'shared_from');
    }

    public function sharedFromPost()
    {
        return $this->belongsTo(self::class, 'shared_from');
    }
}
