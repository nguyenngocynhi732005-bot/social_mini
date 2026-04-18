<?php

namespace App\Events\ChatRealtime;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatBackgroundChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;

    public $backgroundUrl;

    public $senderId;

    public $testMode;

    public function __construct(int $conversationId, ?string $backgroundUrl, int $senderId, bool $testMode = false)
    {
        $this->conversationId = $conversationId;
        $this->backgroundUrl = $backgroundUrl;
        $this->senderId = $senderId;
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
        return 'ChatBackgroundChanged';
    }

    public function broadcastWith()
    {
        return [
            'conversationId' => $this->conversationId,
            'backgroundUrl' => $this->backgroundUrl,
            'senderId' => $this->senderId,
            'cleared' => $this->backgroundUrl === null,
        ];
    }
}
