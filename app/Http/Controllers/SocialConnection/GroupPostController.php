<?php

namespace App\Http\Controllers\SocialConnection;

use App\Http\Controllers\Controller;
use App\Models\GroupMember;
use App\Models\GroupPost;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class GroupPostController extends Controller
{
    private function groupMemberGroupColumn(): string
    {
        return Schema::hasColumn('group_members', 'social_group_id') ? 'social_group_id' : 'group_id';
    }

    private function groupPostGroupColumn(): string
    {
        return Schema::hasColumn('group_posts', 'social_group_id') ? 'social_group_id' : 'group_id';
    }

    public function store(Request $request, $groupId)
    {
        if (!Schema::hasTable('group_members') || !Schema::hasTable('group_posts')) {
            return response()->json([
                'success' => false,
                'message' => 'Chua khoi tao day du bang group_members/group_posts.',
            ], 503);
        }

        $userId = (int) auth()->id();
        if ($userId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để đăng bài.',
            ], 401);
        }

        $memberGroupColumn = $this->groupMemberGroupColumn();
        $postGroupColumn = $this->groupPostGroupColumn();

        if (!Schema::hasColumn('group_members', $memberGroupColumn) || !Schema::hasColumn('group_posts', $postGroupColumn)) {
            return response()->json([
                'success' => false,
                'message' => 'Cau truc cot nhom hien tai khong hop le.',
            ], 503);
        }

        $isJoined = GroupMember::query()
            ->where($memberGroupColumn, (int) $groupId)
            ->where('user_id', $userId)
            ->exists();

        if (!$isJoined) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa tham gia nhóm này.',
            ], 403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $post = new GroupPost();
        $post->{$postGroupColumn} = (int) $groupId;
        $post->user_id = $userId;
        $post->content = $validated['content'];

        $post->save();
        $post->load('user');

        $recipientIds = GroupMember::query()
            ->where($memberGroupColumn, (int) $groupId)
            ->where('user_id', '!=', $userId)
            ->pluck('user_id');

        $rawName = trim((string) (
            $post->user->name
            ?? $post->user->Name
            ?? $post->user->username
            ?? $post->user->first_name
            ?? $post->user->email
            ?? ''
        ));
        $displayName = $rawName !== '' ? $rawName : 'Thành viên';
        $avatarSeed = $rawName !== '' ? $rawName : 'U';

        if (Schema::hasTable('notifications')) {
            foreach ($recipientIds as $recipientId) {
                Notification::query()->create([
                    'receiver_id' => (int) $recipientId,
                    'sender_id' => $userId,
                    'type' => 'new_post',
                    'is_read' => false,
                    'post_id' => (int) $post->id,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Đăng bài thành công.',
            'post' => [
                'id' => $post->id,
                'content'   => $post->content,
                'user_name' => $displayName,
                'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($avatarSeed) . '&background=random',
                'created_at' => 'Vừa xong'
            ]
        ]);
    }
}