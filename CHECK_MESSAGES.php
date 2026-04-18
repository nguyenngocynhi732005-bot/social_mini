<?php
// Quick debug script to check message storage

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use App\Models\ChatMessage;

// Get all messages
$messages = ChatMessage::with('sender')
    ->orderBy('created_at', 'desc')
    ->limit(20)
    ->get();

echo "=== Last 20 Messages ===\n";
foreach ($messages as $msg) {
    $senderName = ($msg->sender ? $msg->sender->name : 'N/A');
    echo sprintf(
        "ID: %d | Sender: %d (%s) | Conversation: %d | Body: %s | Time: %s\n",
        $msg->id,
        $msg->sender_id,
        $senderName,
        $msg->conversation_id,
        substr($msg->body, 0, 30),
        $msg->created_at->format('H:i:s')
    );
}

echo "\n=== Conversation Participants ===\n";
$participants = \App\Models\ConversationParticipant::select('conversation_id', 'user_id')
    ->orderBy('conversation_id')
    ->get();

foreach ($participants as $p) {
    echo sprintf("Conversation %d - User %d\n", $p->conversation_id, $p->user_id);
}
