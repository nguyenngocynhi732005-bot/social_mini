<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFriendshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    if (Schema::hasTable('friendships')) {
        return;
    }

    Schema::create('friendships', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('friend_id');
        $table->enum('status', ['pending', 'accepted', 'blocked', 'cancelled'])->default('pending');
        $table->timestamps();

        // Khóa ngoại trỏ đến cột ID viết hoa của bảng users
        $table->foreign('user_id')->references('ID')->on('users')->onDelete('cascade');
        $table->foreign('friend_id')->references('ID')->on('users')->onDelete('cascade');
        
        $table->unique(['user_id', 'friend_id']);
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('friendships');
    }
}
