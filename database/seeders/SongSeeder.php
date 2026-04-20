<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SongSeeder extends Seeder
{
    public function run()
    {
        DB::table('songs')->update([
            'is_hot' => 0,
            'updated_at' => now(),
        ]);

        DB::table('songs')->upsert([
            [
                'title' => 'Bình An Là Được',
                'artist' => 'Xám',
                'file_path' => 'audio/binh_an_la_duoc.mp3',
                'is_hot' => 1,
                'hot_rank' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Đừng Làm Trái Tim Anh Đau',
                'artist' => 'Sơn Tùng M-TP',
                'file_path' => 'audio/dung_lam_trai_tim_anh_dau.mp3',
                'is_hot' => 1,
                'hot_rank' => 2,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Em Là Không Thể',
                'artist' => 'Trương Thảo Nhi',
                'file_path' => 'audio/E_la_khong_the.mp3',
                'is_hot' => 1,
                'hot_rank' => 3,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Ngày Này Năm Ấy',
                'artist' => 'Đức Phúc',
                'file_path' => 'audio/ngay_nay_nam_ay.mp3',
                'is_hot' => 1,
                'hot_rank' => 4,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Vỗ Tay',
                'artist' => 'HIEUTHUHAI ft. Double2T',
                'file_path' => 'audio/vo_tay.mp3',
                'is_hot' => 1,
                'hot_rank' => 5,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Thiệp Hồng Sai Tên',
                'artist' => 'Quang Hùng MasterD',
                'file_path' => 'audio/thiep_hong_sai_ten.mp3',
                'is_hot' => 1,
                'hot_rank' => 6,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['title'], ['artist', 'file_path', 'is_hot', 'hot_rank', 'is_active', 'updated_at']);
    }
}
