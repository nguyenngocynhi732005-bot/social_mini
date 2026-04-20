<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained(); // Người đăng
    $table->longText('content'); // Nội dung bài viết (Lưu cả mã HTML in đậm, in nghiêng)
    $table->string('media_path')->nullable(); // Đường dẫn ảnh hoặc video
    $table->string('media_type')->nullable(); // 'image' hoặc 'video'
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
