<?php

namespace App\Events\ChatRealtime;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversationId;

    public int $senderId;

    public ?int $targetUserId;

    public string $action;

    public string $callType;

    public string $roomId;

    public string $callerName;

    public bool $testMode;

    public function __construct(
        int $conversationId,
        int $senderId,
        ?int $targetUserId,
        string $action,
        string $callType,
        string $roomId,
        string $callerName,
        bool $testMode = false
    ) {
        $this->conversationId = $conversationId;
        $this->senderId = $senderId;
        $this->targetUserId = $targetUserId;
        $this->action = $action;
        $this->callType = $callType;
        $this->roomId = $roomId;
        $this->callerName = $callerName;
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
        return 'CallSignal';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'sender_id' => $this->senderId,
            'target_user_id' => $this->targetUserId,
            'action' => $this->action,
            'call_type' => $this->callType,
            'room_id' => $this->roomId,
            'caller_name' => $this->callerName,
            'test_mode' => $this->testMode,
        ];
    }
}
