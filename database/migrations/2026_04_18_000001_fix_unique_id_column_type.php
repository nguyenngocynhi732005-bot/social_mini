<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixUniqueIdColumnType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'BirthDate')) {
            DB::table('users')
                ->where('BirthDate', '0000-00-00')
                ->update(['BirthDate' => '2000-01-01']);
        }

        DB::statement('ALTER TABLE users MODIFY COLUMN unique_id VARCHAR(255) NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
