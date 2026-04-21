<?php

namespace App\Http\Controllers\ChatRealtime;

use App\Events\ChatRealtime\ChatBackgroundChanged;
use App\Events\ChatRealtime\CallSignal;
use App\Events\ChatRealtime\MessageDeleted;
use App\Events\ChatRealtime\TypingStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatRealtime\ChatService; // Service xử lý logic 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    private function resolveChatUserId(?int $identifier): ?int
    {
        $identifier = (int) $identifier;

        if ($identifier <= 0) {
            return null;
        }

        $user = DB::table('users')
            ->select('ID')
            ->where('ID', $identifier)
            ->orWhere('unique_id', $identifier)
            ->orWhere('user_id', $identifier)
            ->first();

        return $user ? (int) $user->ID : null;
    }

    private function callSignalCacheKey(int $conversationId): string
    {
        return 'chat-call-signal:' . $conversationId;
    }

    private function storeCallSignalState(int $conversationId, array $payload): void
    {
        Cache::put($this->callSignalCacheKey($conversationId), $payload, now()->addMinutes(10));
    }

    private function typingStatusCacheKey(int $conversationId): string
    {
        return 'chat-typing-status:' . $conversationId;
    }

    private function storeTypingStatusState(int $conversationId, array $payload): void
    {
        Cache::put($this->typingStatusCacheKey($conversationId), $payload, now()->addSeconds(20));
    }

    /**
     * Lấy tất cả hội thoại mà user đang đăng nhập đang tham gia.
     * Trả về kèm thông tin người còn lại trong cuộc trò chuyện.
     */
    public function authenticatedConversations()
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            return response()->json(['message' => 'Bạn cần đăng nhập để xem hội thoại.'], 401);
        }

        $conversations = $user->conversations()
            ->with(['users' => function ($query) {
                $query->select('users.ID', 'users.First_name', 'users.Last_name', 'users.Email');
            }])
            ->orderByDesc('conversations.id')
            ->get()
            ->map(function ($conversation) use ($user) {
                $lastMessage = $conversation->messages()->latest('created_at')->first();

                $otherParticipants = $conversation->users
                    ->reject(function ($participant) use ($user) {
                        return (int) $participant->ID === (int) $user->ID;
                    })
                    ->values()
                    ->map(function ($participant) {
                        return [
                            'id' => $participant->ID,
                            'first_name' => (string) ($participant->First_name ?? ''),
                            'last_name' => (string) ($participant->Last_name ?? ''),
                            'email' => (string) ($participant->Email ?? ''),
                            'full_name' => trim((string) ($participant->First_name ?? '') . ' ' . (string) ($participant->Last_name ?? '')),
                        ];
                    })
                    ->all();

                return [
                    'id' => $conversation->id,
                    'type' => $conversation->type ?? null,
                    'label' => $conversation->label ?? null,
                    'participants' => $otherParticipants,
                    'last_message' => optional($lastMessage)->body,
                    'last_message_at' => optional(optional($lastMessage)->created_at)->toIso8601String(),
                ];
            });

        return response()->json($conversations);
    }

    /**
     * Lấy danh sách hội thoại của user hiện tại để hiển thị sidebar.
     */
    public function conversations(Request $request)
    {
        $hasChatBackgroundColumn = Schema::hasColumn('conversations', 'chat_background_path');

        $requestedSenderId = (int) $request->query('sender_id', 0);
        $isLocalTestMode = app()->environment('local') && $request->boolean('test_mode');
        $senderId = $this->resolveChatUserId(
            ($isLocalTestMode && $requestedSenderId > 0)
                ? $requestedSenderId
                : (Auth::id() ?: $requestedSenderId)
        );

        if (!$senderId) {
            return response()->json(['message' => 'Thiếu sender_id để lấy danh sách hội thoại khi test.'], 422);
        }

        $this->touchUserOnlineHeartbeat((int) $senderId);

        $friendIds = $isLocalTestMode ? null : $this->resolveAcceptedFriendIds((int) $senderId);

        if ($friendIds === null) {
            $users = DB::table('users')
                ->where('ID', '!=', (int) $senderId)
                ->get();
        } elseif (empty($friendIds)) {
            return response()->json(collect());
        } else {
            $users = DB::table('users')
                ->whereIn('ID', $friendIds)
                ->get();
        }

        $profiles = $users->map(function ($user) use ($senderId) {
            $raw = (array) $user;
            $normalized = [];

            foreach ($raw as $key => $value) {
                $normalized[strtolower((string) $key)] = $value;
            }

            $peerId = (int) ($normalized['id'] ?? $normalized['user_id'] ?? $normalized['unique_id'] ?? 0);
            if ($peerId <= 0 || $peerId === (int) $senderId) {
                return null;
            }

            return [
                'peer_id' => $peerId,
                'normalized' => $normalized,
            ];
        })->filter()->values();

        if ($profiles->isEmpty()) {
            return response()->json(collect());
        }

        $conversationByPeerId = [];
        $conversationIds = [];

        foreach ($profiles as $profile) {
            $peerId = (int) $profile['peer_id'];
            $conversation = $this->chatService->getOrCreatePrivateConversation((int) $senderId, $peerId);
            $conversationByPeerId[$peerId] = $conversation;
            $conversationIds[] = (int) $conversation->id;
        }

        $conversationIds = array_values(array_unique(array_filter($conversationIds)));

        $latestMessageIds = ChatMessage::query()
            ->selectRaw('MAX(id) as latest_id, conversation_id')
            ->whereIn('conversation_id', $conversationIds)
            ->groupBy('conversation_id')
            ->pluck('latest_id', 'conversation_id');

        $lastMessages = ChatMessage::query()
            ->with('attachments')
            ->whereIn('id', $latestMessageIds->filter()->values())
            ->get()
            ->keyBy('conversation_id');

        $unreadCounts = DB::table('chat_messages as cm')
            ->join('conversation_participants as cp', function ($join) use ($senderId) {
                $join->on('cp.conversation_id', '=', 'cm.conversation_id')
                    ->where('cp.user_id', '=', (int) $senderId);
            })
            ->whereIn('cm.conversation_id', $conversationIds)
            ->where('cm.sender_id', '!=', (int) $senderId)
            ->where(function ($query) {
                $query->whereNull('cp.last_read_at')
                    ->orWhereColumn('cm.created_at', '>', 'cp.last_read_at');
            })
            ->groupBy('cm.conversation_id')
            ->pluck(DB::raw('COUNT(*)'), 'cm.conversation_id');

        $conversations = $profiles->map(function (array $profile) use ($conversationByPeerId, $lastMessages, $unreadCounts, $hasChatBackgroundColumn) {
            $peerId = (int) $profile['peer_id'];
            $normalized = (array) $profile['normalized'];
            $conversation = $conversationByPeerId[$peerId] ?? null;

            if (!$conversation) {
                return null;
            }

            $conversationId = (int) $conversation->id;
            $lastMessage = $lastMessages->get($conversationId);
            $lastMessageAt = optional(optional($lastMessage)->created_at)->toIso8601String();
            $unreadCount = (int) ($unreadCounts->get($conversationId) ?? 0);

            $name = trim((string) ($normalized['name'] ?? ''));
            if ($name === '') {
                $first = trim((string) ($normalized['first_name'] ?? ''));
                $last = trim((string) ($normalized['last_name'] ?? ''));
                $name = trim($first . ' ' . $last);
            }

            $email = trim((string) ($normalized['email'] ?? ''));
            if ($email === '') {
                $email = trim((string) ($normalized['mail'] ?? ''));
            }

            if ($name === '') {
                $name = (string) ($email ?: ('User #' . $peerId));
            }

            $avatar = $this->resolveAvatarUrl($normalized, $name, $email);
            $recentActivityAt = $this->resolveRecentActivityAt($normalized, $lastMessageAt);
            $isRecentlyActive = $this->resolveIsOnline($normalized);

            return [
                'id' => $conversationId,
                'peer_id' => $peerId,
                'name' => $name,
                'email' => $email,
                'avatar' => $avatar,
                'chat_background_url' => ($hasChatBackgroundColumn && $conversation->chat_background_path)
                    ? Storage::url($conversation->chat_background_path)
                    : null,
                'is_recently_active' => $isRecentlyActive,
                'recent_activity_at' => $recentActivityAt,
                'last_message' => $this->formatPreviewMessage($lastMessage),
                'last_message_sender_id' => optional($lastMessage)->sender_id ? (int) $lastMessage->sender_id : null,
                'last_message_at' => $lastMessageAt,
                'has_unread' => $unreadCount > 0,
                'unread_count' => $unreadCount,
            ];
        })
            ->filter()
            ->sort(function (array $a, array $b) {
                $activeA = $a['is_recently_active'] ? 1 : 0;
                $activeB = $b['is_recently_active'] ? 1 : 0;
                if ($activeA !== $activeB) {
                    return $activeB <=> $activeA;
                }

                $lastA = strtotime((string) ($a['last_message_at'] ?? '')) ?: 0;
                $lastB = strtotime((string) ($b['last_message_at'] ?? '')) ?: 0;
                if ($lastA !== $lastB) {
                    return $lastB <=> $lastA;
                }

                return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
            })
            ->values();

        return response()->json($conversations);
    }

    /**
     * Lấy danh sách tin nhắn trong một hội thoại [cite: 16]
     */
    public function index(Request $request, Conversation $conversation)
    {
        $requestedSenderId = (int) $request->query('sender_id', 0);
        $isLocalTestMode = app()->environment('local') && $request->boolean('test_mode');
        $senderId = $this->resolveChatUserId(
            ($isLocalTestMode && $requestedSenderId > 0)
                ? $requestedSenderId
                : (Auth::id() ?: $requestedSenderId)
        );

        if (!$senderId) {
            return response()->json(['message' => 'Thiếu sender_id để lấy tin nhắn khi test.'], 422);
        }

        // 1. Kiểm tra quyền truy cập (User phải thuộc hội thoại này) [cite: 56]
        if (!$isLocalTestMode && !$this->chatService->isUserInConversation((int) $senderId, $conversation->id)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $limit = 20;
        $beforeId = (int) $request->query('before_id', 0);

        // Trả về block tin nhắn mới nhất thay vì toàn bộ lịch sử để giảm payload và thời gian phản hồi.
        $query = $conversation->messages()
            ->with(['sender', 'attachments'])
            ->orderByDesc('id');

        if ($beforeId > 0) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query->limit($limit)->get()->reverse()->values();

        $payload = $messages->map(function ($message) {
            return $this->formatMessagePayload($message);
        });

        $oldestMessageId = (int) optional($messages->first())->id;
        $hasMore = false;
        if ($oldestMessageId > 0) {
            $hasMore = $conversation->messages()->where('id', '<', $oldestMessageId)->exists();
        }

        return response()->json([
            'data' => $payload,
            'meta' => [
                'limit' => $limit,
                'before_id' => $beforeId > 0 ? $beforeId : null,
                'next_before_id' => $oldestMessageId > 0 ? $oldestMessageId : null,
                'has_more' => $hasMore,
            ],
        ]);
    }

    /**
     * Gửi tin nhắn mới [cite: 16]
     */
    public function store(Request $request, Conversation $conversation)
    {
        $isLocalTestMode = app()->environment('local') && $request->boolean('test_mode');

        $validated = $request->validate([
            'body' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'in:text,image,audio,file'],
            'sender_id' => ['nullable', 'integer', 'exists:users,ID'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'audio' => ['nullable', 'file', 'mimes:webm,ogg,mp3,wav,wave,m4a,aac,mp4', 'max:10240'],
        ]);

        $requestedSenderId = (int) ($validated['sender_id'] ?? 0);
        $senderId = $this->resolveChatUserId(
            ($isLocalTestMode && $requestedSenderId > 0)
                ? $requestedSenderId
                : (Auth::id() ?: $requestedSenderId)
        );

        if (!$request->filled('body') && !$request->hasFile('image') && !$request->hasFile('audio')) {
            return response()->json(['message' => 'Tin nhắn cần nội dung, hình ảnh hoặc âm thanh.'], 422);
        }

        if (!$senderId) {
            return response()->json([
                'message' => 'Thiếu người gửi. Vui lòng đăng nhập hoặc truyền sender_id hợp lệ khi test.',
            ], 422);
        }

        if ($request->hasFile('audio')) {
            $validated['type'] = 'audio';
            $validated['body'] = null;
        }

        $uploadedAttachment = $request->file('image') ?: $request->file('audio');

        // Gọi Service xử lý lưu tin nhắn và phát sự kiện Realtime [cite: 56]
        $message = $this->chatService->sendMessage(
            $senderId,
            $conversation->id,
            $validated, // Body, type... [cite: 38]
            $uploadedAttachment,
            $isLocalTestMode
        );

        return response()->json($this->formatMessagePayload($message), 201);
    }

    /**
     * Thu hồi tin nhắn đã gửi.
     */
    public function destroy(Request $request, Conversation $conversation, ChatMessage $message)
    {
        $requestedSenderId = (int) $request->query('sender_id', 0);
        $isLocalTestMode = app()->environment('local') && $request->boolean('test_mode');
        $senderId = $this->resolveChatUserId(
            ($isLocalTestMode && $requestedSenderId > 0)
                ? $requestedSenderId
                : (Auth::id() ?: $requestedSenderId)
        );

        if (!$senderId) {
            return response()->json(['message' => 'Thiếu sender_id để thu hồi tin nhắn khi test.'], 422);
        }

        if (!$isLocalTestMode && !$this->chatService->isUserInConversation((int) $senderId, $conversation->id)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        if ((int) $message->conversation_id !== (int) $conversation->id) {
            return response()->json(['message' => 'Tin nhắn không thuộc cuộc trò chuyện này.'], 404);
        }

        if ((int) $message->sender_id !== (int) $senderId) {
            return response()->json(['message' => 'Chỉ người gửi mới được thu hồi tin nhắn này.'], 403);
        }

        $recalledMessage = $this->chatService->recallMessage($message);

        try {
            broadcast(new MessageDeleted($conversation->id, $message->id, $isLocalTestMode))->toOthers();
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json($this->formatMessagePayload($recalledMessage));
    }

    /**
     * Cập nhật ảnh nền theo conversation và broadcast realtime cho phía còn lại.
     */
    public function updateBackground(Request $request, Conversation $conversation)
    {
        if (!Schema::hasColumn('conversations', 'chat_background_path')) {
            return response()->json([
                'message' => 'Thiếu cột chat_background_path. Vui lòng chạy php artisan migrate.',
            ], 422);
        }

        $isLocalTestMode = app()->environment('local') && $request->boolean('test_mode');

        $validated = $request->validate([
            'sender_id' => ['nullable', 'integer', 'exists:users,ID'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'clear' => ['nullable', 'boolean'],
        ]);

        $requestedSenderId = (int) ($validated['sender_id'] ?? 0);
        $senderId = $this->resolveChatUserId(
            ($isLocalTestMode && $requestedSenderId > 0)
                ? $requestedSenderId
                : (Auth::id() ?: $requestedSenderId)
        );

        if (!$senderId) {
            return response()->json([
                'message' => 'Thiếu người gửi. Vui lòng đăng nhập hoặc truyền sender_id hợp lệ khi test.',
            ], 422);
        }

        if (!$isLocalTestMode && !$this->chatService->isUserInConversation((int) $senderId, $conversation->id)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $shouldClear = $request->boolean('clear');
        $uploadedImage = $request->file('image');

        if (!$shouldClear && !$uploadedImage) {
            return response()->json([
                'message' => 'Vui lòng chọn ảnh hoặc gửi clear=1 để xóa nền.',
            ], 422);
        }

        if ($uploadedImage && $conversation->chat_background_path) {
            Storage::disk('public')->delete($conversation->chat_background_path);
        }

        $newPath = null;
        if (!$shouldClear && $uploadedImage) {
            $newPath = $uploadedImage->store('chat-backgrounds', 'public');
        } elseif ($shouldClear && $conversation->chat_background_path) {
            Storage::disk('public')->delete($conversation->chat_background_path);
        }

        $conversation->update([
            'chat_background_path' => $newPath,
        ]);

        $backgroundUrl = $newPath ? Storage::url($newPath) : null;

        try {
            broadcast(new ChatBackgroundChanged($conversation->id, $backgroundUrl, (int) $senderId, $isLocalTestMode))->toOthers();
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'conversation_id' => $conversation->id,
            'chat_background_url' => $backgroundUrl,
            'cleared' => $backgroundUrl === null,
        ]);
    }

    /**
     * Phát tín hiệu cuộc gọi realtime để phía nhận hiển thị popup nghe/từ chối.
     */
    public function signalCall(Request $request, Conversation $conversation)
    {
        $isLocalTestMode = app()->environment('local') && $request->boolean('test_mode');

        $validated = $request->validate([
            'sender_id' => ['nullable', 'integer', 'exists:users,ID'],
            'target_user_id' => ['nullable', 'integer', 'exists:users,ID'],
            'action' => ['required', 'string', 'in:incoming,accepted,rejected,ended'],
            'call_type' => ['nullable', 'string', 'in:voice,video'],
            'room_id' => ['nullable', 'string', 'max:120'],
            'caller_name' => ['nullable', 'string', 'max:120'],
        ]);

        $requestedSenderId = (int) ($validated['sender_id'] ?? 0);
        $senderId = $this->resolveChatUserId(
            ($isLocalTestMode && $requestedSenderId > 0)
                ? $requestedSenderId
                : (Auth::id() ?: $requestedSenderId)
        );

        if (!$senderId) {
            return response()->json([
                'message' => 'Thiếu người gửi tín hiệu cuộc gọi.',
            ], 422);
        }

        if (!$isLocalTestMode && !$this->chatService->isUserInConversation((int) $senderId, $conversation->id)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $targetUserId = $this->resolveChatUserId((int) ($validated['target_user_id'] ?? 0));
        $action = (string) ($validated['action'] ?? 'incoming');
        $callType = (string) ($validated['call_type'] ?? 'video');
        $roomId = trim((string) ($validated['room_id'] ?? ('room_' . $conversation->id)));
        $callerName = trim((string) ($validated['caller_name'] ?? (optional(Auth::user())->name ?? ('User ' . $senderId))));

        $payload = [
            'conversation_id' => (int) $conversation->id,
            'sender_id' => (int) $senderId,
            'target_user_id' => $targetUserId ?: null,
            'action' => $action,
            'call_type' => $callType,
            'room_id' => $roomId,
            'caller_name' => $callerName,
            'test_mode' => $isLocalTestMode,
            'created_at' => now()->toIso8601String(),
        ];

        $this->storeCallSignalState((int) $conversation->id, $payload);

        try {
            broadcast(new CallSignal(
                (int) $conversation->id,
                (int) $senderId,
                $targetUserId ?: null,
                $action,
                $callType,
                $roomId,
                $callerName,
                $isLocalTestMode
            ))->toOthers();
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'ok' => true,
            'signal' => $payload,
        ]);
    }

    public function latestCallSignal(Request $request, Conversation $conversation)
    {
        $requestedSenderId = (int) $request->query('sender_id', 0);
        $senderId = $this->resolveChatUserId((Auth::id() ?: $requestedSenderId));

        if (!$senderId) {
            return response()->json(['signal' => null]);
        }

        $signal = Cache::get($this->callSignalCacheKey((int) $conversation->id));

        return response()->json([
            'signal' => $signal,
        ]);
    }

    /**
     * Đánh dấu tin nhắn là đã đọc [cite: 16, 52]
     */
    public function markAsRead(Request $request, Conversation $conversation)
    {
        $requestedSenderId = (int) $request->input('sender_id', 0);
        $isLocalTestMode = app()->environment('local') && $request->boolean('test_mode');
        $senderId = $this->resolveChatUserId(
            ($isLocalTestMode && $requestedSenderId > 0)
                ? $requestedSenderId
                : (Auth::id() ?: $requestedSenderId)
        );

        if (!$senderId) {
            return response()->json(['status' => 'error', 'message' => 'Thiếu người dùng để đánh dấu đã đọc.'], 422);
        }

        if (!$isLocalTestMode && !$this->chatService->isUserInConversation((int) $senderId, $conversation->id)) {
            return response()->json(['status' => 'error', 'message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $this->chatService->markAsRead((int) $senderId, $conversation->id);
        return response()->json(['status' => 'success']);
    }

    /**
     * Cập nhật trạng thái đang soạn tin để hiển thị realtime cho phía còn lại.
     */
    public function typingStatus(Request $request, Conversation $conversation)
    {
        $isLocalTestMode = app()->environment('local') && $request->boolean('test_mode');

        $validated = $request->validate([
            'sender_id' => ['nullable', 'integer', 'exists:users,ID'],
            'is_typing' => ['required', 'boolean'],
        ]);

        $requestedSenderId = (int) ($validated['sender_id'] ?? 0);
        $senderId = $this->resolveChatUserId(
            ($isLocalTestMode && $requestedSenderId > 0)
                ? $requestedSenderId
                : (Auth::id() ?: $requestedSenderId)
        );

        if (!$senderId) {
            return response()->json([
                'message' => 'Thiếu người gửi để cập nhật trạng thái đang soạn tin.',
            ], 422);
        }

        if (!$isLocalTestMode && !$this->chatService->isUserInConversation((int) $senderId, $conversation->id)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $sender = User::query()->where('ID', (int) $senderId)->first();
        $senderName = trim((string) (optional($sender)->name ?? ('User ' . $senderId)));
        $isTyping = (bool) ($validated['is_typing'] ?? false);
        $typingPayload = [
            'conversation_id' => (int) $conversation->id,
            'sender_id' => (int) $senderId,
            'is_typing' => $isTyping,
            'sender_name' => $senderName,
            'test_mode' => $isLocalTestMode,
            'created_at' => now()->toIso8601String(),
        ];

        $this->storeTypingStatusState((int) $conversation->id, $typingPayload);

        try {
            broadcast(new TypingStatusChanged(
                (int) $conversation->id,
                (int) $senderId,
                $isTyping,
                $senderName,
                $isLocalTestMode
            ))->toOthers();
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'ok' => true,
            'typing' => $typingPayload,
        ]);
    }

    public function latestTypingStatus(Request $request, Conversation $conversation)
    {
        $requestedSenderId = (int) $request->query('sender_id', 0);
        $isLocalTestMode = app()->environment('local') && $request->boolean('test_mode');
        $senderId = $this->resolveChatUserId(
            ($isLocalTestMode && $requestedSenderId > 0)
                ? $requestedSenderId
                : (Auth::id() ?: $requestedSenderId)
        );

        if (!$senderId) {
            return response()->json(['typing' => null]);
        }

        if (!$isLocalTestMode && !$this->chatService->isUserInConversation((int) $senderId, $conversation->id)) {
            return response()->json(['typing' => null]);
        }

        $typing = Cache::get($this->typingStatusCacheKey((int) $conversation->id));
        if (!is_array($typing)) {
            return response()->json(['typing' => null]);
        }

        if ((int) ($typing['sender_id'] ?? 0) === (int) $senderId) {
            return response()->json(['typing' => null]);
        }

        $typingAt = strtotime((string) ($typing['created_at'] ?? ''));
        if ($typingAt !== false && $typingAt < now()->subSeconds(25)->timestamp) {
            return response()->json(['typing' => null]);
        }

        return response()->json([
            'typing' => $typing,
        ]);
    }

    private function formatMessagePayload($message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'body' => $message->body,
            'type' => $message->type,
            'is_recalled' => $message->type === 'recalled',
            'created_at' => optional($message->created_at)->toIso8601String(),
            'sender' => [
                'id' => optional($message->sender)->id,
                'name' => optional($message->sender)->name,
            ],
            'attachments' => $message->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_path' => $attachment->file_path,
                    'file_name' => $attachment->file_name,
                    'mime_type' => $attachment->mime_type,
                    'file_size' => $attachment->file_size,
                    'file_url' => Storage::url($attachment->file_path),
                ];
            })->values(),
        ];
    }

    private function formatPreviewMessage($message): string
    {
        if (!$message) {
            return '';
        }

        if ($message->type === 'recalled') {
            return 'Tin nhắn đã được thu hồi';
        }

        $body = trim((string) $message->body);
        if (preg_match('/^\[STICKER:[a-z0-9_-]+\]$/i', $body)) {
            return 'Đã gửi sticker';
        }

        if (preg_match('/^\[Sticker\s*-\s*.+\]$/i', $body)) {
            return 'Đã gửi sticker';
        }

        if ($body !== '') {
            return $body;
        }

        $attachmentMimeTypes = $message->relationLoaded('attachments')
            ? $message->attachments->pluck('mime_type')->filter()->values()->all()
            : ($message->attachments()->pluck('mime_type')->filter()->values()->all() ?: []);

        if (!empty($attachmentMimeTypes)) {
            foreach ($attachmentMimeTypes as $mimeType) {
                if (str_starts_with((string) $mimeType, 'audio/')) {
                    return 'Đã gửi tin nhắn thoại';
                }
            }

            return 'Đã gửi ảnh';
        }

        return '';
    }

    private function resolveAvatarUrl(array $normalized, string $name, string $email): string
    {
        $candidates = [
            $normalized['avatar_url'] ?? null,
            $normalized['avatar'] ?? null,
            $normalized['profile_photo_url'] ?? null,
            $normalized['profile_photo'] ?? null,
            $normalized['image_url'] ?? null,
            $normalized['image'] ?? null,
            $normalized['photo_url'] ?? null,
            $normalized['photo'] ?? null,
            $normalized['picture'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value === '') {
                continue;
            }

            if (preg_match('/^https?:\/\//i', $value) || str_starts_with($value, 'data:image/')) {
                return $value;
            }

            $relative = ltrim(str_replace('\\', '/', $value), '/');
            if ($relative !== '') {
                return Storage::url($relative);
            }
        }

        $seed = trim($name) !== '' ? $name : ($email ?: 'User');
        $encoded = rawurlencode($seed);
        return "https://ui-avatars.com/api/?name={$encoded}&background=eceef2&color=111827&size=96";
    }

    private function resolveRecentActivityAt(array $normalized, ?string $fallback): ?string
    {
        $candidates = [
            $normalized['last_active_at'] ?? null,
            $normalized['last_login_at'] ?? null,
            $normalized['last_seen_at'] ?? null,
            $normalized['updated_at'] ?? null,
            $fallback,
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === null) {
                continue;
            }

            $timestamp = strtotime((string) $candidate);
            if ($timestamp === false) {
                continue;
            }

            return date(DATE_ATOM, $timestamp);
        }

        return null;
    }

    private function resolveIsOnline(array $normalized): bool
    {
        $rawStatus = trim((string) ($normalized['online_status'] ?? ''));
        if ($rawStatus === '') {
            return false;
        }

        $status = mb_strtolower($rawStatus, 'UTF-8');
        if (in_array($status, ['offline', 'off', '0', 'false'], true)) {
            return false;
        }

        if (str_contains($status, 'offline') || str_contains($status, 'ngoai tuyen') || str_contains($status, 'ngoại tuyến')) {
            return false;
        }

        if (in_array($status, ['active now', 'online', 'on', '1', 'true', 'đang hoạt động', 'dang hoat dong'], true)) {
            return $this->isRecentOnlineHeartbeat($normalized);
        }

        $looksOnline = str_contains($status, 'active') || str_contains($status, 'online') || str_contains($status, 'hoat dong') || str_contains($status, 'hoạt động');
        if (!$looksOnline) {
            return false;
        }

        return $this->isRecentOnlineHeartbeat($normalized);
    }

    private function isRecentOnlineHeartbeat(array $normalized): bool
    {
        $candidates = [
            $normalized['updatedat'] ?? null,
            $normalized['last_active_at'] ?? null,
            $normalized['last_login_at'] ?? null,
            $normalized['last_seen_at'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value === '') {
                continue;
            }

            $timestamp = strtotime($value);
            if ($timestamp === false) {
                continue;
            }

            return $timestamp >= now()->subMinutes(5)->timestamp;
        }

        return false;
    }

    private function touchUserOnlineHeartbeat(int $userId): void
    {
        if ($userId <= 0 || !Schema::hasColumn('users', 'online_status')) {
            return;
        }

        $updates = [
            'online_status' => 'Active now',
        ];

        if (Schema::hasColumn('users', 'UpdatedAt')) {
            $updates['UpdatedAt'] = now();
        }

        DB::table('users')
            ->where('ID', $userId)
            ->update($updates);
    }

    private function resolveAcceptedFriendIds(int $senderId): ?array
    {
        if ($senderId <= 0) {
            return [];
        }

        $friendshipTable = $this->detectFriendshipTable();

        if ($friendshipTable === null) {
            return null;
        }

        $rows = DB::table($friendshipTable)
            ->select('user_one_id', 'user_two_id')
            ->where('status', 1)
            ->where(function ($query) use ($senderId) {
                $query->where('user_one_id', $senderId)
                    ->orWhere('user_two_id', $senderId);
            })
            ->get();

        return $rows
            ->map(function ($row) use ($senderId) {
                $one = (int) ($row->user_one_id ?? 0);
                $two = (int) ($row->user_two_id ?? 0);

                if ($one === $senderId) {
                    return $two;
                }

                if ($two === $senderId) {
                    return $one;
                }

                return 0;
            })
            ->filter(fn($id) => $id > 0 && $id !== $senderId)
            ->unique()
            ->values()
            ->all();
    }

    private function detectFriendshipTable(): ?string
    {
        $priorityCandidates = ['friendship', 'friendships'];
        foreach ($priorityCandidates as $candidate) {
            if (Schema::hasTable($candidate)) {
                return $candidate;
            }
        }

        $databaseName = DB::getDatabaseName();
        if (!$databaseName) {
            return null;
        }

        $tables = DB::table('information_schema.columns')
            ->select('table_name')
            ->where('table_schema', $databaseName)
            ->whereIn('column_name', ['user_one_id', 'user_two_id', 'status'])
            ->groupBy('table_name')
            ->havingRaw('COUNT(DISTINCT column_name) = 3')
            ->pluck('table_name')
            ->all();

        if (empty($tables)) {
            return null;
        }

        usort($tables, static function (string $a, string $b): int {
            $aHasFriend = stripos($a, 'friend') !== false ? 1 : 0;
            $bHasFriend = stripos($b, 'friend') !== false ? 1 : 0;

            if ($aHasFriend !== $bHasFriend) {
                return $bHasFriend <=> $aHasFriend;
            }

            return strcmp($a, $b);
        });

        return (string) $tables[0];
    }
}
