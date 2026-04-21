<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    use HasFactory;

    // Khai báo tên bảng nếu Laravel không tự nhận diện đúng (tùy chọn nhưng nên có cho chắc)
    protected $table = 'user_blocks';

    public const UPDATED_AT = null;

    // Cấp phép cho các cột này được gán dữ liệu tự động
    protected $fillable = [
        'blocker_id',
        'blocked_id',
    ];
}