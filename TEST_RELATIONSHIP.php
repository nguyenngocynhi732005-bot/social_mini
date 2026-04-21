<?php
require 'bootstrap/app.php';

$msg = \App\Models\ChatMessage::find(70);
echo "Message ID: " . $msg->id . "\n";
echo "Sender ID FK: " . $msg->sender_id . "\n";

// Try direct raw query to see what's happening
$result = \DB::select(\DB::raw('SELECT cm.id, cm.sender_id, u.ID, u.First_name FROM chat_messages cm LEFT JOIN users u ON u.ID = cm.sender_id WHERE cm.id = 70'));
echo "\nDirect Query Result:\n";
var_dump($result);

// Now try through Eloquent
$msg = \App\Models\ChatMessage::with('sender')->find(70);
echo "\nEloquent Load Result:\n";
var_dump($msg->sender);
