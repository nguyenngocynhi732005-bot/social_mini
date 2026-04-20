<?php

namespace App\Events\ChatRealtime;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversationId;

    public int $senderId;

    public bool $isTyping;

    public string $senderName;

    public bool $testMode;

    public function __construct(
        int $conversationId,
        int $senderId,
        bool $isTyping,
        string $senderName,
        bool $testMode = false
    ) {
        $this->conversationId = $conversationId;
        $this->senderId = $senderId;
        $this->isTyping = $isTyping;
        $this->senderName = $senderName;
        $this->testMode = $testMode;
    }

    public function broadcastOn()
    {
        if ($this->testMode) {
            return new Channel('chat.' . $this->conversationId);
        }

        return new PrivateChannel('chat.' . $this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'TypingStatusChanged';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'sender_id' => $this->senderId,
            'is_typing' => $this->isTyping,
            'sender_name' => $this->senderName,
            'test_mode' => $this->testMode,
            'created_at' => now()->toIso8601String(),
        ];
    }
}
