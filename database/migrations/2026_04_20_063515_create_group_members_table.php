<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('group_members', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('group_id'); // trỏ về id của social_groups
        $table->unsignedBigInteger('user_id');  // trỏ về ID của users
        $table->enum('role', ['admin', 'mod', 'member'])->default('member');
        $table->timestamp('joined_at')->useCurrent();
        
        $table->foreign('group_id')->references('id')->on('social_groups')->onDelete('cascade');
        $table->foreign('user_id')->references('ID')->on('users')->onDelete('cascade');
        $table->unique(['group_id', 'user_id']);
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_members');
    }
}
