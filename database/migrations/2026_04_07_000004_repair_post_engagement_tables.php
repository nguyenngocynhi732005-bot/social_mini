<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RepairPostEngagementTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('post_reactions')) {
            Schema::create('post_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('reaction_type', 20);
                $table->timestamps();

                $table->unique(['post_id', 'user_id']);
                $table->index(['post_id', 'reaction_type']);
            });
        }

        if (!Schema::hasTable('post_comments')) {
            Schema::create('post_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('content');
                $table->timestamps();

                $table->index('post_id');
            });
        }

        if (Schema::hasTable('posts') && !Schema::hasColumn('posts', 'shared_from')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->unsignedBigInteger('shared_from')->nullable()->after('media_type');
                $table->foreign('shared_from')->references('id')->on('posts')->nullOnDelete();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('post_comments')) {
            Schema::dropIfExists('post_comments');
        }

        if (Schema::hasTable('post_reactions')) {
            Schema::dropIfExists('post_reactions');
        }

        if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'shared_from')) {
            Schema::table('posts', function (Blueprint $table) {
                try {
                    $table->dropForeign(['shared_from']);
                } catch (\Throwable $exception) {
                    // Ignore missing FK metadata.
                }
                $table->dropColumn('shared_from');
            });
        }
    }
}