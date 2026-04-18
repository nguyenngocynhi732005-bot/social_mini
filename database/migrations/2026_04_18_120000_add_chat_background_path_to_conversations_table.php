<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChatBackgroundPathToConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'chat_background_path')) {
                if (Schema::hasColumn('conversations', 'type')) {
                    $table->string('chat_background_path')->nullable()->after('type');
                } elseif (Schema::hasColumn('conversations', 'label')) {
                    $table->string('chat_background_path')->nullable()->after('label');
                } else {
                    $table->string('chat_background_path')->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'chat_background_path')) {
                $table->dropColumn('chat_background_path');
            }
        });
    }
}
