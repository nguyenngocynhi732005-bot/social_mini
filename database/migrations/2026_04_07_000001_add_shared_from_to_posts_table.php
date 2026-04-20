<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSharedFromToPostsTable extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'shared_from')) {
                $table->unsignedBigInteger('shared_from')->nullable()->after('media_type');
                $table->foreign('shared_from')->references('id')->on('posts')->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'shared_from')) {
                try {
                    $table->dropForeign(['shared_from']);
                } catch (\Throwable $exception) {
                    // Ignore if foreign key was not created.
                }
                $table->dropColumn('shared_from');
            }
        });
    }
}
