<?php

namespace App\Http\Controllers\SocialConnection;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    private function getCurrentUserId(): int
    {
        if (auth()->check()) {
            return (int) auth()->id();
        }

        return (int) (User::query()->min((new User())->getKeyName()) ?? 0);
    }

    public function index(Request $request)
    {
        $userId = $this->getCurrentUserId();
        $limit = (int) $request->input('limit', 8);
        if ($limit <= 0) {
            $limit = 8;
        }
        $limit = min($limit, 20);
        
        // ĐỔI user_id THÀNH receiver_id
        $notifications = Notification::with('sender')
            ->where('receiver_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();

        $unreadCount = Notification::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        Log::info('Notifications sender relation debug', [
            'viewer_user_id' => $userId,
            'items' => $notifications->map(function ($noti) {
                return [
                    'notification_id' => $noti->id,
                    'sender_id' => $noti->sender_id,
                    'sender_is_null' => $noti->sender === null,
                    'sender_primary_key' => optional($noti->sender)->ID,
                    'sender_name' => optional($noti->sender)->name,
                    'type' => $noti->type,
                ];
            })->values()->all(),
        ]);

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications->map(function ($noti) {
                $sender = $noti->sender;
                $firstName = trim((string) (optional($sender)->First_name ?? optional($sender)->first_name ?? ''));
                $lastName = trim((string) (optional($sender)->Last_name ?? optional($sender)->last_name ?? ''));
                $fullName = trim($firstName . ' ' . $lastName);

                $senderName = trim((string) (
                    ($fullName !== '' ? $fullName : null)
                    ?? optional($sender)->Name
                    ?? optional($sender)->name
                    ?? optional($sender)->username
                    ?? optional($sender)->Email
                    ?? optional($sender)->email
                    ?? optional($sender)->Phone
                    ?? ''
                ));

                if ($senderName === '') {
                    $senderName = 'User #' . (int) $noti->sender_id;
                }

                // Tự động tạo câu thông báo dựa vào cột 'type'
                $message = '';
                $link = '#';

                if ($noti->type === 'friend_request') {
                    $message = $senderName . ' đã gửi cho bạn một lời mời kết bạn.';
                    if ($link === '#') {
                        $link = '/friends';
                    }
                } elseif ($noti->type === 'friend_accept') {
                    $message = $senderName . ' đã chấp nhận lời mời kết bạn.';
                    if ($link === '#') {
                        $link = '/friends';
                    }
                } elseif ($noti->type === 'like_post') {
                    $message = $senderName . ' đã thích bài viết của bạn.';
                    if ($link === '#') {
                        $link = '/newsfeed';
                    }
                } elseif ($noti->type === 'comment_post') {
                    $message = $senderName . ' đã bình luận về bài viết của bạn.';
                    if ($link === '#') {
                        $link = '/newsfeed';
                    }
                } elseif ($noti->type === 'share_post') {
                    $message = $senderName . ' đã chia sẻ bài viết của bạn.';
                    if ($link === '#') {
                        $link = '/newsfeed';
                    }
                } elseif ($noti->type === 'new_post' || $noti->type === 'group_post') {
                    $message = $senderName . ' đã đăng một bài viết mới trong nhóm.';
                    if ($link === '#') {
                        $link = '/social/groups';
                    }
                }

                if ($message === '') {
                    $message = 'Bạn có thông báo mới.';
                }

                return [
                    'id' => $noti->id,
                    'type' => $noti->type,
                    'message' => $message,
                    'link' => $link,
                    'is_read' => (bool) $noti->is_read,
                    'sender_name' => $senderName,
                    'sender_avatar_url' => optional($sender)->avatar_url,
                    'time' => optional($noti->created_at)->diffForHumans() ?? 'Vừa xong',
                ];
            })->values(),
        ]);
    }

    public function markRead(Request $request, $id)
    {
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return response()->json(['message' => 'Bạn cần đăng nhập.'], 401);
        }

        $notification = Notification::query()
            ->where('id', (int) $id)
            ->where('receiver_id', $userId) 
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Không tìm thấy thông báo.'], 404);
        }

        $notification->is_read = true;
        $notification->save();

        return response()->json(['message' => 'Đã đánh dấu đã đọc.']);
    }

    public function markAllRead(Request $request)
    {
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return response()->json(['message' => 'Bạn cần đăng nhập.'], 401);
        }

        Notification::query()
            ->where('receiver_id', $userId) 
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Đã đánh dấu tất cả là đã đọc.']);
    }
}
