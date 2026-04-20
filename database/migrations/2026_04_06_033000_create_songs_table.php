<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSongsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('file_path')->nullable();
            $table->string('preview_url')->nullable();
            $table->boolean('is_hot')->default(true);
            $table->unsignedInteger('hot_rank')->default(999);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('songs')->insert([
    [
        'title' => 'Bình An Là Được', 
        'artist' => 'Xám', 
        'file_path' => 'audio/binh_an_la_duoc.mp3',
        'preview_url' => null, 
        'is_hot' => 1, 'hot_rank' => 1, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()
    ],
    [
        'title' => 'Đừng Làm Trái Tim Anh Đau', 
        'artist' => 'Sơn Tùng M-TP', 
        'file_path' => 'audio/dung_lam_trai_tim_anh_dau.mp3',
        'preview_url' => null, 
        'is_hot' => 1, 'hot_rank' => 2, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()
    ],
    [
        'title' => 'Em Là Không Thể', 
        'artist' => 'Trương Thảo Nhi', 
        'file_path' => 'audio/E_la_khong_the.mp3',
        'preview_url' => null, 
        'is_hot' => 1, 'hot_rank' => 3, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()
    ],
    [
        'title' => 'Ngày Này Năm Ấy', 
        'artist' => 'Đức Phúc', 
        'file_path' => 'audio/ngay_nay_nam_ay.mp3',
        'preview_url' => null, 
        'is_hot' => 1, 'hot_rank' => 4, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()
    ],
    [
        'title' => 'Vỗ Tay', 
        'artist' => 'HIEUTHUHAI ft. Double2T', 
        'file_path' => 'audio/vo_tay.mp3',
        'preview_url' => null, 
        'is_hot' => 1, 'hot_rank' => 5, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()
    ],
    [
        'title' => 'Thiệp Hồng Sai Tên', 
        'artist' => 'Quang Hùng MasterD', 
        'file_path' => 'audio/thiep_hong_sai_ten.mp3',
        'preview_url' => null, 
        'is_hot' => 1, 'hot_rank' => 6, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()
    ],
]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('songs');
    }
}
