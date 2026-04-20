<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationRelationshipToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'location')) {
                $table->string('location')->nullable();
            }

            if (!Schema::hasColumn('users', 'relationship')) {
                $table->string('relationship')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'relationship')) {
                $table->dropColumn('relationship');
            }

            if (Schema::hasColumn('users', 'location')) {
                $table->dropColumn('location');
            }
        });
    }
}
