<?php

namespace App\Events\ChatRealtime;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;

    public $messageId;

    public $testMode;

    public function __construct($conversationId, $messageId, bool $testMode = false)
    {
        $this->conversationId = (int) $conversationId;
        $this->messageId = (int) $messageId;
        $this->testMode = $testMode;
    }

    public function broadcastOn()
    {
        if ($this->testMode) {
            return new Channel('chat.' . $this->conversationId);
        }

        return new PrivateChannel('chat.' . $this->conversationId);
    }

    public function broadcastAs()
    {
        return 'MessageDeleted';
    }
}
