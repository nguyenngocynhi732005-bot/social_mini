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
        'shared_from_id', // THÊM: Để khớp với database bạn đã sửa
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
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

    // --- CHỈ THÊM CÁC DÒNG DƯỚI ĐÂY ---

    /**
     * Quan hệ để lấy bài viết gốc (Dùng cho hiển thị trên Profile)
     */
    public function sharedFromPost()
    {
        // Sử dụng shared_from_id cho đúng với cột bạn đã thêm vào DB
        return $this->belongsTo(Post::class, 'shared_from_id');
    }

    /**
     * Quan hệ để đếm số lượt share của một bài viết
     */
    public function shares()
    {
        return $this->hasMany(Post::class, 'shared_from_id');
    }
}