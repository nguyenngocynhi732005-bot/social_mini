<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddColumnsToGroupPostsTable extends Migration
{
    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $database = DB::getDatabaseName();
        $result = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();

        return (bool) $result;
    }

    public function up()
    {
        if (!Schema::hasTable('group_posts')) {
            return;
        }

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
            if (Schema::hasColumn('group_posts', 'group_id') && !$this->foreignKeyExists('group_posts', 'group_posts_group_id_foreign')) {
                $table->foreign('group_id')->references('id')->on('social_groups')->onDelete('cascade');
            }

            if (Schema::hasColumn('group_posts', 'user_id') && !$this->foreignKeyExists('group_posts', 'group_posts_user_id_foreign')) {
                $table->foreign('user_id')->references('ID')->on('users')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('group_posts')) {
            return;
        }

        Schema::table('group_posts', function (Blueprint $table) {
            if (Schema::hasColumn('group_posts', 'group_id') && $this->foreignKeyExists('group_posts', 'group_posts_group_id_foreign')) {
                $table->dropForeign(['group_id']);
            }
            if (Schema::hasColumn('group_posts', 'group_id')) {
                $table->dropColumn('group_id');
            }

            if (Schema::hasColumn('group_posts', 'user_id') && $this->foreignKeyExists('group_posts', 'group_posts_user_id_foreign')) {
                $table->dropForeign(['user_id']);
            }
            if (Schema::hasColumn('group_posts', 'user_id')) {
                $table->dropColumn('user_id');
            }

            if (Schema::hasColumn('group_posts', 'content')) {
                $table->dropColumn('content');
            }
        });
    }
}
