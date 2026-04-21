<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    if (Schema::hasTable('user_blocks')) {
        return;
    }

    Schema::create('user_blocks', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('blocker_id');
        $table->unsignedBigInteger('blocked_id');
        $table->timestamps();

        $table->foreign('blocker_id')->references('ID')->on('users')->onDelete('cascade');
        $table->foreign('blocked_id')->references('ID')->on('users')->onDelete('cascade');
        $table->unique(['blocker_id', 'blocked_id']);
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_blocks');
    }
}
