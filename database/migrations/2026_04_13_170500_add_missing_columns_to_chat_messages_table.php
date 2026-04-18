<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToChatMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_messages', 'conversation_id')) {
                $table->foreignId('conversation_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('conversations')
                    ->onDelete('cascade');
            }

            if (!Schema::hasColumn('chat_messages', 'sender_id')) {
                $table->foreignId('sender_id')
                    ->nullable()
                    ->after('conversation_id')
                    ->constrained('users')
                    ->onDelete('cascade');
            }

            if (!Schema::hasColumn('chat_messages', 'body')) {
                $table->text('body')->nullable()->after('sender_id');
            }

            if (!Schema::hasColumn('chat_messages', 'type')) {
                $table->string('type')->default('text')->after('body');
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
        // Intentionally left empty to avoid destructive rollback on active chat data.
    }
}
