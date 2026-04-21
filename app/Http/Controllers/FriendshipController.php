<?php

namespace App\Http\Controllers\SocialConnection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friendship;
use App\Models\UserBlock;
use Illuminate\Support\Facades\Log;
use App\Models\Notification; 

class FriendshipController extends Controller
{
    // Lấy ID người dùng hiện tại (Tạm để ID 1 nếu Thành viên 2 chưa làm xong Đăng nhập)
    private function getCurrentUserId()
    {
        if (auth()->check()) {
            return (int) auth()->id();
        }

        // Fallback về user có ID nhỏ nhất đang tồn tại để tránh lỗi FK khi DB không có ID=1
        return (int) (User::query()->min((new User())->getKeyName()) ?? 0);
    }

    // Hiển thị trang bạn bè và 1 người được chọn từ thanh tìm kiếm
    public function index(Request $request)
    {
        $myId = $this->getCurrentUserId();
        $targetId = (int) $request->query('target_id');

        $relations = Friendship::query()
            ->whereIn('status', ['pending', 'accepted', 'blocked'])
            ->where(function ($query) use ($myId) {
                $query->where('user_one_id', $myId)
                    ->orWhere('user_two_id', $myId);
            })
            ->get(['user_one_id', 'user_two_id', 'status']);

        $relationStatusByUser = [];
        foreach ($relations as $relation) {
            $otherId = (int) ($relation->user_one_id == $myId ? $relation->user_two_id : $relation->user_one_id);
            $relationStatusByUser[$otherId] = $relation->status;
        }

        $acceptedRelations = Friendship::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($myId) {
                $query->where('user_one_id', $myId)
                    ->orWhere('user_two_id', $myId);
            })
            ->get(['user_one_id', 'user_two_id']);

        $friendIds = $acceptedRelations
            ->map(function ($relation) use ($myId) {
                return (int) ($relation->user_one_id == $myId ? $relation->user_two_id : $relation->user_one_id);
            })
            ->unique()
            ->values();

        $friends = User::query()
            ->whereIn('ID', $friendIds)
            ->orderBy('Name')
            ->get();

        $highlightUser = null;

        if ($targetId > 0) {
            $highlightUser = User::where('id', $targetId)
                ->where('ID', '!=', $myId)
                ->first();
        }

        $blockedOrFriendIds = Friendship::query()
            ->whereIn('status', ['accepted', 'blocked'])
            ->where(function ($query) use ($myId) {
                $query->where('user_one_id', $myId)
                    ->orWhere('user_two_id', $myId);
            })
            ->get(['user_one_id', 'user_two_id'])
            ->map(function ($relation) use ($myId) {
                return (int) ($relation->user_one_id == $myId ? $relation->user_two_id : $relation->user_one_id);
            })
            ->unique();

        $suggestedUsers = User::query()
            ->where('ID', '!=', $myId)
            ->whereNotIn('id', $blockedOrFriendIds)
            ->when($targetId, function ($query) use ($targetId) {
                $query->where('ID', '!=', $targetId);
            })
            ->orderBy('Name')
            ->limit(8)
            ->get();

        $pendingRequesterIds = Friendship::query()
            ->where('user_two_id', $myId)
            ->where('status', 'pending')
            ->pluck('user_one_id');

        $pendingRequests = User::query()
            ->whereIn('ID', $pendingRequesterIds)
            ->get();

        // TÌM DANH SÁCH BỊ CHẶN (Phải đặt TRƯỚC lệnh return)
        $blockedUsers = \App\Models\User::whereIn('id', function($query) use ($myId) {
            $query->select('blocked_id')
                  ->from('user_blocks') 
                  ->where('blocker_id', $myId); // Dùng luôn $myId cho đồng bộ
        })->get();

        // GỘP TẤT CẢ VÀO 1 LỆNH RETURN DUY NHẤT Ở CUỐI CÙNG
        return view('pages.friends', compact('highlightUser', 'suggestedUsers', 'friends', 'relationStatusByUser', 'pendingRequests', 'blockedUsers'));
    }

    // 1. GỬI LỜI MỜI KẾT BẠN
    public function sendRequest(Request $request)
    {
        try {
            $myId = $this->getCurrentUserId();
            $targetId = (int) $request->input('target_id');

            if ($myId <= 0 || $targetId <= 0) {
                return response()->json(['message' => 'Không tìm thấy tài khoản hợp lệ để gửi lời mời'], 422);
            }

            if (!User::query()->whereKey($targetId)->exists()) {
                return response()->json(['message' => 'Người dùng đích không tồn tại'], 404);
            }

            if ($myId == $targetId) {
                return response()->json(['message' => 'Không thể tự kết bạn với chính mình'], 400);
            }

            // Kiểm tra xem đã có quan hệ gì chưa (kể cả người kia gửi cho mình)
            $existingFriendship = Friendship::where(function ($query) use ($myId, $targetId) {
                $query->where('user_one_id', $myId)->where('user_two_id', $targetId);
            })->orWhere(function ($query) use ($myId, $targetId) {
                $query->where('user_one_id', $targetId)->where('user_two_id', $myId);
            })->first();

            if ($existingFriendship && $existingFriendship->status !== 'cancelled') {
                return response()->json(['message' => 'Đã tồn tại trạng thái bạn bè hoặc lời mời'], 400);
            }

            if ($existingFriendship && $existingFriendship->status === 'cancelled') {
                $existingFriendship->update([
                    'user_one_id' => $myId,
                    'user_two_id' => $targetId,
                    'status' => 'pending',
                ]);

                return response()->json(['message' => 'Đã gửi lại lời mời kết bạn!']);
            }

            // Tạo lời mời mới
            Friendship::create([
                'user_one_id' => $myId,
                'user_two_id' => $targetId,
                'status' => 'pending'
            ]);

            // --- ĐÃ SỬA: KHỚP VỚI DATABASE THỰC TẾ ---
            Notification::create([
                'receiver_id' => $targetId, // Đổi từ user_id -> receiver_id
                'sender_id'   => $myId,
                'type'        => 'friend_request',
                'is_read'     => 0
            ]);
            // ------------------------------------------

            return response()->json(['message' => 'Đã gửi lời mời kết bạn thành công!']);
        } catch (\Throwable $exception) {
            Log::error('sendRequest failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Loi he thong khi gui loi moi ket ban'], 500);
        }
    }

    // 1.1 HỦY LỜI MỜI KẾT BẠN ĐÃ GỬI
    public function cancelRequest(Request $request)
    {
        $myId = $this->getCurrentUserId();
        $targetId = (int) $request->input('target_id');

        if ($targetId <= 0 || $myId === $targetId) {
            return response()->json(['message' => 'Yêu cầu không hợp lệ'], 422);
        }

        $affected = Friendship::query()
            ->where('status', 'pending')
            ->where(function ($query) use ($myId, $targetId) {
                $query->where(function ($pairQuery) use ($myId, $targetId) {
                    $pairQuery->where('user_one_id', $myId)
                        ->where('user_two_id', $targetId);
                })->orWhere(function ($pairQuery) use ($myId, $targetId) {
                    $pairQuery->where('user_one_id', $targetId)
                        ->where('user_two_id', $myId);
                });
            })
            ->delete();

        if ($affected === 0) {
            return response()->json(['message' => 'Không tìm thấy lời mời để hủy'], 404);
        }

        return response()->json(['message' => 'Đã hủy lời mời kết bạn']);
    }

    // 2. PHẢN HỒI LỜI MỜI (Chấp nhận hoặc Từ chối)
    public function respond(Request $request)
    {
        $myId = $this->getCurrentUserId();
        $requesterId = $request->input('requester_id');
        $action = $request->input('action'); // 'accept' hoặc 'decline'

        // Tìm lời mời người kia gửi cho mình
        $friendship = Friendship::where('user_one_id', $requesterId)
                                ->where('user_two_id', $myId)
                                ->where('status', 'pending')
                                ->first();

        if (!$friendship) {
            return response()->json(['message' => 'Không tìm thấy lời mời này'], 404);
        }

        if ($action === 'accept') {
            $friendship->update(['status' => 'accepted']);

            // --- ĐÃ SỬA: BẮN THÔNG BÁO BÁO TIN MỪNG ---
            Notification::create([
                'receiver_id' => $requesterId, // Đổi từ user_id -> receiver_id
                'sender_id'   => $myId,
                'type'        => 'friend_accept', // Sửa cho khớp loại trong DB
                'is_read'     => 0
            ]);

            return response()->json(['message' => 'Đã chấp nhận kết bạn!']);
        } else {
            // Xử lý khi bấm Từ chối (hủy lời mời)
            $friendship->update(['status' => 'cancelled']);
            return response()->json(['message' => 'Đã xóa lời mời.']);
        }
    }

    // 3. HỦY KẾT BẠN (Hoặc Thu hồi lời mời đã gửi)
    public function unfriend($targetId)
    {
        $myId = $this->getCurrentUserId();

        Friendship::where(function ($query) use ($myId, $targetId) {
            $query->where('user_one_id', $myId)->where('user_two_id', $targetId);
        })->orWhere(function ($query) use ($myId, $targetId) {
            $query->where('user_one_id', $targetId)->where('user_two_id', $myId);
        })->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Đã hủy kết bạn/thu hồi lời mời.']);
    }

    // 4. CHẶN NGƯỜI DÙNG
    public function block(Request $request)
    {
        $myId = $this->getCurrentUserId();
        $targetId = $request->input('target_id');

        // Thêm vào bảng chặn
        UserBlock::firstOrCreate([
            'blocker_id' => $myId,
            'blocked_id' => $targetId
        ]);

        Friendship::query()
            ->where(function ($query) use ($myId, $targetId) {
                $query->where('user_one_id', $myId)->where('user_two_id', $targetId);
            })->orWhere(function ($query) use ($myId, $targetId) {
                $query->where('user_one_id', $targetId)->where('user_two_id', $myId);
            })
            ->update(['status' => 'blocked']);

        return response()->json(['message' => 'Đã chặn người dùng này.']);
    }

    // 5. BỎ CHẶN
    public function unblock(Request $request, $user = null)
    {
        $myId = $this->getCurrentUserId();
        $targetId = (int) ($user ?? $request->input('target_id')); // Ưu tiên route param

        if ($targetId <= 0) {
            return response()->json(['message' => 'Nguoi dung can bo chan khong hop le.'], 422);
        }

        // 1. Xóa khỏi bảng sổ đen
        UserBlock::where('blocker_id', $myId)
                 ->where('blocked_id', $targetId)
                 ->delete();

        // 2. Mở khóa trạng thái trong bảng tình bạn (trả về cancelled để có thể kết bạn lại)
        Friendship::where(function ($query) use ($myId, $targetId) {
            $query->where('user_one_id', $myId)->where('user_two_id', $targetId);
        })->orWhere(function ($query) use ($myId, $targetId) {
            $query->where('user_one_id', $targetId)->where('user_two_id', $myId);
        })->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Đã bỏ chặn thành công.']);
    }
}