<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToChatAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_attachments', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_attachments', 'chat_message_id')) {
                $table->unsignedBigInteger('chat_message_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('chat_attachments', 'file_path')) {
                $table->string('file_path')->nullable()->after('chat_message_id');
            }

            if (!Schema::hasColumn('chat_attachments', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }

            if (!Schema::hasColumn('chat_attachments', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('file_name');
            }

            if (!Schema::hasColumn('chat_attachments', 'file_size')) {
                $table->unsignedInteger('file_size')->nullable()->after('mime_type');
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
