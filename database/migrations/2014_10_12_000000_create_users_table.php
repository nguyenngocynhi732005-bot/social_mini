<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('ID');
            $table->string('unique_id')->unique();
            $table->string('user_id')->nullable();
            $table->string('Email')->unique();
            $table->string('Phone')->nullable();
            $table->string('Password');
            $table->string('Name');
            $table->string('img')->nullable();
            $table->string('Gender')->nullable();
            $table->date('BirthDate')->nullable();
            $table->boolean('online_status')->default(false);
            $table->string('Status')->default('active');
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('is_admin')->default(false);
            $table->integer('Reputation')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
