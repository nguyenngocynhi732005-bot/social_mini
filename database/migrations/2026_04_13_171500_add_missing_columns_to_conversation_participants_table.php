<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToConversationParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversation_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('conversation_participants', 'conversation_id')) {
                $table->unsignedBigInteger('conversation_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('conversation_participants', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('conversation_id');
            }

            if (!Schema::hasColumn('conversation_participants', 'role')) {
                $table->string('role')->default('member')->after('user_id');
            }

            if (!Schema::hasColumn('conversation_participants', 'last_read_at')) {
                $table->timestamp('last_read_at')->nullable()->after('role');
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
        // Intentionally non-destructive.
    }
}
