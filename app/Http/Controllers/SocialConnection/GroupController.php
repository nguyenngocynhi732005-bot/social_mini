<?php

namespace App\Http\Controllers\SocialConnection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GroupMember;
use App\Models\SocialGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class GroupController extends Controller
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

    public function index(Request $request)
    {
        $userId = $this->getCurrentUserId();
        $groupColumn = $this->groupMemberGroupColumn();

        $joinedGroupIds = GroupMember::query()
            ->where('user_id', $userId)
            ->pluck($groupColumn)
            ->unique()
            ->values();

        $systemGroups = SocialGroup::query()
            ->withCount('members')
            ->orderByDesc('created_at')
            ->get();

        $joinedGroups = SocialGroup::query()
            ->whereIn('id', $joinedGroupIds)
            ->withCount('members')
            ->orderByDesc('created_at')
            ->get();

        return view('social-connection.groups.index', compact('systemGroups', 'joinedGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'privacy' => ['nullable', Rule::in(['public', 'private'])],
            'is_hidden' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'avatar_image' => ['nullable', 'string', 'max:2048'],
        ]);

        $userId = $this->getCurrentUserId();
        $groupColumn = $this->groupMemberGroupColumn();

        $group = SocialGroup::query()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'privacy' => $validated['privacy'] ?? 'public',
            'is_hidden' => (bool) ($validated['is_hidden'] ?? false),
            'cover_image' => $validated['cover_image'] ?? null,
            'avatar_image' => $validated['avatar_image'] ?? null,
            'created_by' => $userId,
        ]);

        GroupMember::query()->firstOrCreate(
            [
                $groupColumn => $group->id,
                'user_id' => $userId,
            ],
            [
                'role' => 'admin',
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tạo nhóm thành công.',
                'group' => $group,
            ]);
        }

        return redirect()->route('social.groups.show', $group->id)
            ->with('success', 'Tạo nhóm thành công.');
    }

    public function show(Request $request, $group)
    {
        $groupModel = $this->getGroupOrFail($group);
        $userId = $this->getCurrentUserId();
        $groupColumn = $this->groupMemberGroupColumn();

        $members = GroupMember::query()
            ->with('user')
            ->where($groupColumn, $groupModel->id)
            ->orderBy('role')
            ->orderBy('id')
            ->get();

        $isJoined = GroupMember::query()
            ->where($groupColumn, $groupModel->id)
            ->where('user_id', $userId)
            ->exists();

        return view('social-connection.groups.show', [
            'group' => $groupModel,
            'members' => $members,
            'isJoined' => $isJoined,
        ]);
    }

    public function join(Request $request, $group)
    {
        $groupModel = $this->getGroupOrFail($group);
        $userId = $this->getCurrentUserId();
        $groupColumn = $this->groupMemberGroupColumn();

        $alreadyMember = GroupMember::query()
            ->where($groupColumn, $groupModel->id)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyMember) {
            return response()->json(['message' => 'Bạn đã ở trong nhóm này.']);
        }

        if ($groupModel->privacy === 'private') {
            if (!Schema::hasTable('group_join_requests')) {
                return response()->json([
                    'message' => 'Thiếu bảng group_join_requests để lưu yêu cầu tham gia.',
                ], 500);
            }

            $payload = [];

            if (Schema::hasColumn('group_join_requests', 'social_group_id')) {
                $payload['social_group_id'] = $groupModel->id;
            } elseif (Schema::hasColumn('group_join_requests', 'group_id')) {
                $payload['group_id'] = $groupModel->id;
            }

            if (Schema::hasColumn('group_join_requests', 'user_id')) {
                $payload['user_id'] = $userId;
            } elseif (Schema::hasColumn('group_join_requests', 'requester_id')) {
                $payload['requester_id'] = $userId;
            }

            if (empty($payload)) {
                return response()->json([
                    'message' => 'Cấu trúc bảng group_join_requests không hợp lệ.',
                ], 500);
            }

            if (Schema::hasColumn('group_join_requests', 'status')) {
                $payload['status'] = 'pending';
            }
            if (Schema::hasColumn('group_join_requests', 'created_at')) {
                $payload['created_at'] = now();
            }
            if (Schema::hasColumn('group_join_requests', 'updated_at')) {
                $payload['updated_at'] = now();
            }

            DB::table('group_join_requests')->updateOrInsert(
                array_filter($payload, function ($key) {
                    return in_array($key, ['social_group_id', 'group_id', 'user_id', 'requester_id'], true);
                }, ARRAY_FILTER_USE_KEY),
                $payload
            );

            return response()->json(['message' => 'Đã gửi yêu cầu tham gia nhóm.']);
        }

        GroupMember::query()->create([
            $groupColumn => $groupModel->id,
            'user_id' => $userId,
            'role' => 'member',
        ]);

        return response()->json(['message' => 'Tham gia nhóm thành công.']);
    }

    public function leave(Request $request, $group)
    {
        $groupModel = $this->getGroupOrFail($group);
        $userId = $this->getCurrentUserId();
        $groupColumn = $this->groupMemberGroupColumn();

        $deleted = GroupMember::query()
            ->where($groupColumn, $groupModel->id)
            ->where('user_id', $userId)
            ->delete();

        if ($deleted === 0) {
            return response()->json(['message' => 'Bạn chưa tham gia nhóm này.'], 404);
        }

        return response()->json(['message' => 'Rời nhóm thành công.']);
    }

    public function update(Request $request, $group)
    {
        $groupModel = $this->getGroupOrFail($group);
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'privacy' => ['nullable', Rule::in(['public', 'private'])],
            'is_hidden' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'avatar_image' => ['nullable', 'string', 'max:2048'],
        ]);

        $groupModel->fill($validated);
        $groupModel->save();

        return response()->json([
            'message' => 'Cập nhật nhóm thành công.',
            'group' => $groupModel,
        ]);
    }

    public function destroy(Request $request, $group)
    {
        $groupModel = $this->getGroupOrFail($group);
        $groupModel->delete();

        return response()->json(['message' => 'Xóa nhóm thành công.']);
    }
}
