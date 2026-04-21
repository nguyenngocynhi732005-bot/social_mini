<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntroFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'bio')) {
                $table->string('bio', 191)->nullable();
            }

            if (!Schema::hasColumn('users', 'work')) {
                $table->string('work')->nullable();
            }

            if (!Schema::hasColumn('users', 'education')) {
                $table->string('education')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'education')) {
                $table->dropColumn('education');
            }

            if (Schema::hasColumn('users', 'work')) {
                $table->dropColumn('work');
            }

            if (Schema::hasColumn('users', 'bio')) {
                $table->dropColumn('bio');
            }
        });
    }
}
