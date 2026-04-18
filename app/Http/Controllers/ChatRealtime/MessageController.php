<?php

namespace App\Http\Controllers\ChatRealtime;

use App\Events\ChatRealtime\ChatBackgroundChanged;
use App\Events\ChatRealtime\MessageDeleted;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatRealtime\ChatService; // Service xử lý logic 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $conversations = $users->map(function ($user) use ($senderId, $hasChatBackgroundColumn) {
            $raw = (array) $user;
            $normalized = [];
            foreach ($raw as $key => $value) {
                $normalized[strtolower((string) $key)] = $value;
            }

            $peerId = $this->resolveChatUserId((int) ($normalized['id'] ?? $normalized['ID'] ?? $normalized['user_id'] ?? $normalized['unique_id'] ?? 0));
            if ($peerId <= 0 || $peerId === (int) $senderId) {
                return null;
            }

            $conversation = $this->chatService->getOrCreatePrivateConversation((int) $senderId, $peerId);
            $lastMessage = $conversation->messages()->latest()->first();
            $lastMessageAt = optional(optional($lastMessage)->created_at)->toIso8601String();

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
            $isRecentlyActive = false;
            if ($recentActivityAt) {
                $activeAt = strtotime($recentActivityAt);
                $isRecentlyActive = $activeAt !== false && $activeAt >= now()->subDays(7)->timestamp;
            }

            return [
                'id' => $conversation->id,
                'name' => $name,
                'email' => $email,
                'avatar' => $avatar,
                'chat_background_url' => ($hasChatBackgroundColumn && $conversation->chat_background_path)
                    ? Storage::url($conversation->chat_background_path)
                    : null,
                'is_recently_active' => $isRecentlyActive,
                'recent_activity_at' => $recentActivityAt,
                'last_message' => $this->formatPreviewMessage($lastMessage),
                'last_message_at' => $lastMessageAt,
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

        // 2. Lấy tin nhắn kèm thông tin người gửi [cite: 16]
        $messages = $conversation->messages()
            ->with(['sender', 'attachments']) // Load thêm cả file đính kèm nếu có [cite: 31]
            ->orderBy('created_at', 'asc')
            ->get();

        $payload = $messages->map(function ($message) {
            return $this->formatMessagePayload($message);
        });

        return response()->json($payload);
    }

    /**
     * Gửi tin nhắn mới [cite: 16]
     */
    public function store(Request $request, Conversation $conversation)
    {
        $isLocalTestMode = app()->environment('local') && ($request->boolean('test_mode') || $request->filled('sender_id'));

        $validated = $request->validate([
            'body' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'in:text,image,audio,file'],
            'sender_id' => ['nullable', 'integer', 'exists:users,id'],
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

        $isLocalTestMode = app()->environment('local') && ($request->boolean('test_mode') || $request->filled('sender_id'));

        $validated = $request->validate([
            'sender_id' => ['nullable', 'integer', 'exists:users,id'],
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
     * Đánh dấu tin nhắn là đã đọc [cite: 16, 52]
     */
    public function markAsRead(Conversation $conversation)
    {
        $this->chatService->markAsRead($this->resolveChatUserId(Auth::id()), $conversation->id);
        return response()->json(['status' => 'success']);
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
