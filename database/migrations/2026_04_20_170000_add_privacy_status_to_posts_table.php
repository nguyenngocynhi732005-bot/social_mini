<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrivacyStatusToPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('posts') || Schema::hasColumn('posts', 'privacy_status')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->enum('privacy_status', ['public', 'friends', 'private'])
                ->default('public')
                ->after('content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('posts') || !Schema::hasColumn('posts', 'privacy_status')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('privacy_status');
        });
    }
}
