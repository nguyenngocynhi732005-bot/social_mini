<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            // ID người đăng (liên kết với bảng users)
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            
            $table->string('image_path');           // Đường dẫn file ảnh/video
            $table->text('caption')->nullable();      // Nội dung chữ Nhi nhập
            $table->string('text_color')->default('#ffffff'); // Màu chữ Nhi chọn
            $table->string('music_name')->nullable(); // Tên bài hát Nhi chèn
            
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
        Schema::dropIfExists('stories');
    }
}
