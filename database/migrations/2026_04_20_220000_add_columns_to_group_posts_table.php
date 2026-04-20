<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToGroupPostsTable extends Migration
{
    public function up()
    {
        Schema::table('group_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('group_posts', 'group_id')) {
                $table->unsignedBigInteger('group_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('group_posts', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('group_id');
            }

            if (!Schema::hasColumn('group_posts', 'content')) {
                $table->text('content')->nullable()->after('user_id');
            }
        });

        Schema::table('group_posts', function (Blueprint $table) {
            if (Schema::hasColumn('group_posts', 'group_id')) {
                $table->foreign('group_id')->references('id')->on('social_groups')->onDelete('cascade');
            }

            if (Schema::hasColumn('group_posts', 'user_id')) {
                $table->foreign('user_id')->references('ID')->on('users')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('group_posts', function (Blueprint $table) {
            if (Schema::hasColumn('group_posts', 'group_id')) {
                $table->dropForeign(['group_id']);
                $table->dropColumn('group_id');
            }

            if (Schema::hasColumn('group_posts', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }

            if (Schema::hasColumn('group_posts', 'content')) {
                $table->dropColumn('content');
            }
        });
    }
}
