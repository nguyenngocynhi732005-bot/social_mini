<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) ($user->ID ?? $user->id ?? 0) === (int) $id;
});

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    // Chỉ cho phép nếu user là thành viên của cuộc hội thoại [cite: 56, 99]
    $userId = (int) ($user->ID ?? $user->id ?? 0);

    return \App\Models\ConversationParticipant::where('user_id', $userId)
        ->where('conversation_id', $conversationId)
        ->exists();
});
