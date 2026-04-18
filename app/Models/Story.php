<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;

    // 1. Khai báo các cột được phép thêm dữ liệu vào Database
    protected $fillable = [
        'user_id',
        'image_path',
        'caption',
        'text_color',
        'music_name',
        'music_path',
        'image_scale'
    ];

    // 2. Thiết lập mối quan hệ: Một Story thuộc về Một User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }
}
