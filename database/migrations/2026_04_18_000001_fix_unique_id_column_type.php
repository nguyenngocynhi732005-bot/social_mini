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
        // Sửa kiểu cột unique_id từ numeric sang text/string
        Schema::table('users', function (Blueprint $table) {
            // Sử dụng raw SQL để đảm bảo cột unique_id là VARCHAR/TEXT
            DB::statement('ALTER TABLE users MODIFY COLUMN unique_id VARCHAR(255) NOT NULL');
        });
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
