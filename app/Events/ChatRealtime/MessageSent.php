<?php

namespace App\Events\ChatRealtime;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public $testMode;

    public function __construct(ChatMessage $message, bool $testMode = false)
    {
        // Load thông tin người gửi để hiển thị avatar/tên ở phía frontend [cite: 47]
        $this->message = $message->load('sender');
        $this->testMode = $testMode;
    }

    /**
     * Get the data to broadcast
     * Ensures sender information is properly serialized
     */
    public function broadcastWith()
    {
        $sender = optional($this->message->sender);

        return [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
                'sender_id' => $this->message->sender_id,
                'body' => $this->message->body,
                'type' => $this->message->type,
                'is_recalled' => $this->message->type === 'recalled',
                'created_at' => optional($this->message->created_at)->toIso8601String(),
                'sender' => [
                    'id' => $sender->id ?? null,
                    'name' => $sender->name ?? 'Unknown',
                ],
                'attachments' => $this->message->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'file_path' => $attachment->file_path,
                        'file_name' => $attachment->file_name,
                        'mime_type' => $attachment->mime_type,
                        'file_size' => $attachment->file_size,
                        'file_url' => \Illuminate\Support\Facades\Storage::url($attachment->file_path),
                    ];
                })->values()->all(),
            ]
        ];
    }
    public function broadcastOn()
    {
        if ($this->testMode) {
            return new Channel('chat.' . $this->message->conversation_id);
        }

        return new PrivateChannel('chat.' . $this->message->conversation_id);
    }

    /**
     * Tên sự kiện (Tùy chọn, mặc định là tên Class)
     */
    public function broadcastAs()
    {
        return 'MessageSent';
    }
}
