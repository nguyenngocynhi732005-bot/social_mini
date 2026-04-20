<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilePathToSongsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('songs', 'file_path')) {
            Schema::table('songs', function (Blueprint $table) {
                $table->string('file_path')->nullable()->after('artist');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('songs', 'file_path')) {
            Schema::table('songs', function (Blueprint $table) {
                $table->dropColumn('file_path');
            });
        }
    }
}
