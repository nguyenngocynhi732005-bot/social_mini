<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('post_comments')) {
            Schema::table('post_comments', function (Blueprint $table) {
                if (!Schema::hasColumn('post_comments', 'parent_id')) {
                    $table->unsignedBigInteger('parent_id')->nullable()->after('post_id');
                    $table->foreign('parent_id')->references('id')->on('post_comments')->onDelete('cascade');
                }
                if (!Schema::hasColumn('post_comments', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('post_comments')) {
            Schema::table('post_comments', function (Blueprint $table) {
                if (Schema::hasColumn('post_comments', 'parent_id')) {
                    $table->dropForeign(['parent_id']);
                    $table->dropColumn('parent_id');
                }
                if (Schema::hasColumn('post_comments', 'updated_at')) {
                    $table->dropColumn('updated_at');
                }
            });
        }
    }
};
