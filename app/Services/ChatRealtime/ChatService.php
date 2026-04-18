<?php

namespace App\Services\ChatRealtime;

use App\Models\ChatAttachment;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
// Sau này An sẽ thêm Event Realtime ở đây [cite: 45]
use App\Events\ChatRealtime\MessageSent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ChatService
{
    /**
     * Kiểm tra User có trong hội thoại không
     */
    public function isUserInConversation($userId, $conversationId)
    {
        return ConversationParticipant::where('user_id', $userId)
            ->where('conversation_id', $conversationId)
            ->exists();
    }

    /**
     * Lấy hoặc tạo cuộc trò chuyện private giữa 2 user.
     */
    public function getOrCreatePrivateConversation(int $firstUserId, int $secondUserId): Conversation
    {
        $userIds = [$firstUserId, $secondUserId];
        sort($userIds);

        $query = Conversation::query()
            ->select('conversations.id')
            ->join('conversation_participants as cp', 'cp.conversation_id', '=', 'conversations.id')
            ->whereIn('cp.user_id', $userIds)
            ->groupBy('conversations.id')
            ->havingRaw('COUNT(DISTINCT cp.user_id) = 2');

        if (Schema::hasColumn('conversations', 'type')) {
            $query->where('conversations.type', 'private');
        }

        $conversationId = $query->value('conversations.id');

        if ($conversationId) {
            return Conversation::query()->findOrFail($conversationId);
        }

        return DB::transaction(function () use ($userIds) {
            $conversationData = [];
            if (Schema::hasColumn('conversations', 'type')) {
                $conversationData['type'] = 'private';
            }
            if (Schema::hasColumn('conversations', 'label')) {
                $conversationData['label'] = null;
            }

            $conversation = Conversation::query()->create($conversationData);

            ConversationParticipant::query()->insert([
                [
                    'conversation_id' => $conversation->id,
                    'user_id' => $userIds[0],
                    'role' => 'member',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'conversation_id' => $conversation->id,
                    'user_id' => $userIds[1],
                    'role' => 'member',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            return $conversation;
        });
    }

    /**
     * Xử lý lưu tin nhắn
     */
    public function sendMessage($senderId, $conversationId, array $data, ?UploadedFile $attachment = null, bool $testMode = false)
    {
        $resolvedType = (string) ($data['type'] ?? 'text');
        if ($attachment) {
            $attachmentMimeType = (string) ($attachment->getMimeType() ?: '');
            if (str_starts_with($attachmentMimeType, 'image/')) {
                $resolvedType = 'image';
            } elseif (str_starts_with($attachmentMimeType, 'audio/')) {
                $resolvedType = 'audio';
            } else {
                $resolvedType = 'file';
            }
        }

        // 1. Lưu tin nhắn vào Database 
        $message = ChatMessage::create([
            'sender_id' => $senderId,
            'conversation_id' => $conversationId,
            'body' => $data['body'] ?? null,
            'type' => $resolvedType,
        ]);

        if ($attachment) {
            $path = $attachment->store('chat-attachments', 'public');

            ChatAttachment::create([
                'chat_message_id' => $message->id,
                'file_path' => $path,
                'file_name' => $attachment->getClientOriginalName(),
                'mime_type' => $attachment->getMimeType() ?: 'application/octet-stream',
                'file_size' => $attachment->getSize() ?: 0,
            ]);
        }

        // 2. Load thông tin người gửi để Frontend có dữ liệu hiển thị (tên, avatar) 
        $messageData = $message->load(['sender', 'attachments']);

        // 3. Kích hoạt phát tín hiệu Realtime đến những người khác trong phòng 
        try {
            broadcast(new MessageSent($messageData, $testMode))->toOthers();
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $messageData;
    }

    /**
     * Thu hồi tin nhắn: xóa file đính kèm và đổi nội dung sang trạng thái recalled.
     */
    public function recallMessage(ChatMessage $message): ChatMessage
    {
        $message->loadMissing('attachments');

        foreach ($message->attachments as $attachment) {
            if ($attachment->file_path) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        }

        $message->attachments()->delete();

        $message->update([
            'body' => 'Tin nhắn đã được thu hồi',
            'type' => 'recalled',
        ]);

        return $message->fresh(['sender', 'attachments']);
    }

    /**
     * Đánh dấu cuộc hội thoại đã được đọc
     */
    public function markAsRead($userId, $conversationId)
    {
        return ConversationParticipant::where('user_id', $userId)
            ->where('conversation_id', $conversationId)
            ->update(['last_read_at' => now()]);
    }
}
