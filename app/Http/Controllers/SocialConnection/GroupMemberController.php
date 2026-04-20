<?php

namespace App\Http\Controllers\SocialConnection;

use App\Http\Controllers\Controller;
use App\Models\GroupMember;
use App\Models\SocialGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class GroupMemberController extends Controller
{
    private function getCurrentUserId(): int
    {
        if (auth()->check()) {
            return (int) auth()->id();
        }

        return (int) (User::query()->min((new User())->getKeyName()) ?? 0);
    }

    private function groupMemberGroupColumn(): string
    {
        return Schema::hasColumn('group_members', 'social_group_id') ? 'social_group_id' : 'group_id';
    }

    private function getGroupOrFail($group): SocialGroup
    {
        if ($group instanceof SocialGroup) {
            return $group;
        }

        return SocialGroup::query()->findOrFail((int) $group);
    }

    private function isAdmin(int $groupId, int $userId): bool
    {
        $groupColumn = $this->groupMemberGroupColumn();

        return GroupMember::query()
            ->where($groupColumn, $groupId)
            ->where('user_id', $userId)
            ->where('role', 'admin')
            ->exists();
    }

    private function normalizeRole(string $role): string
    {
        return $role === 'moderator' ? 'mod' : $role;
    }

    public function add(Request $request, $group)
    {
        $groupModel = $this->getGroupOrFail($group);
        $groupColumn = $this->groupMemberGroupColumn();

        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
            'role' => ['nullable', Rule::in(['admin', 'moderator', 'member'])],
        ]);

        $member = GroupMember::query()->firstOrCreate(
            [
                $groupColumn => $groupModel->id,
                'user_id' => (int) $validated['user_id'],
            ],
            [
                'role' => $this->normalizeRole($validated['role'] ?? 'member'),
            ]
        );

        return response()->json([
            'message' => 'Thêm thành viên thành công.',
            'member' => $member,
        ]);
    }

    public function updateRole(Request $request, $group, $user)
    {
        $groupModel = $this->getGroupOrFail($group);
        $userId = (int) $user;
        $actorId = $this->getCurrentUserId();
        $groupColumn = $this->groupMemberGroupColumn();

        if (!$this->isAdmin($groupModel->id, $actorId)) {
            return response()->json(['message' => 'Chỉ admin mới có quyền cập nhật vai trò.'], 403);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'moderator', 'member'])],
        ]);

        $member = GroupMember::query()
            ->where($groupColumn, $groupModel->id)
            ->where('user_id', $userId)
            ->first();

        if (!$member) {
            return response()->json(['message' => 'Không tìm thấy thành viên trong nhóm.'], 404);
        }

        $member->role = $this->normalizeRole($validated['role']);
        $member->save();

        return response()->json([
            'message' => 'Cập nhật vai trò thành công.',
            'member' => $member,
        ]);
    }

    public function remove(Request $request, $group, $user)
    {
        $groupModel = $this->getGroupOrFail($group);
        $userId = (int) $user;
        $actorId = $this->getCurrentUserId();
        $groupColumn = $this->groupMemberGroupColumn();

        if (!$this->isAdmin($groupModel->id, $actorId)) {
            return response()->json(['message' => 'Chỉ admin mới có quyền xóa thành viên.'], 403);
        }

        $deleted = GroupMember::query()
            ->where($groupColumn, $groupModel->id)
            ->where('user_id', $userId)
            ->delete();

        if ($deleted === 0) {
            return response()->json(['message' => 'Không tìm thấy thành viên trong nhóm.'], 404);
        }

        return response()->json(['message' => 'Đã xóa thành viên khỏi nhóm.']);
    }
}
