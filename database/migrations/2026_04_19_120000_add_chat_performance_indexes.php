<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChatPerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index(['conversation_id', 'id'], 'chat_messages_conversation_id_id_index');
            $table->index(['conversation_id', 'created_at'], 'chat_messages_conversation_created_at_index');
            $table->index(['conversation_id', 'sender_id', 'created_at'], 'chat_messages_conversation_sender_created_at_index');
        });

        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->index(['user_id', 'conversation_id'], 'conversation_participants_user_conversation_index');
            $table->index(['conversation_id', 'user_id', 'last_read_at'], 'conversation_participants_conversation_user_read_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('chat_messages_conversation_id_id_index');
            $table->dropIndex('chat_messages_conversation_created_at_index');
            $table->dropIndex('chat_messages_conversation_sender_created_at_index');
        });

        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->dropIndex('conversation_participants_user_conversation_index');
            $table->dropIndex('conversation_participants_conversation_user_read_index');
        });
    }
}
