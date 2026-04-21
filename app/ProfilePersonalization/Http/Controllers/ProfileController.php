<?php

namespace App\ProfilePersonalization\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Song;
use App\Models\Story;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request, int $id)
    {
        return $this->renderProfilePage($request, $id);
    }

    public function index(Request $request)
    {
        $requestedId = $request->query('id');
        $user = Auth::user();
        $profileId = $requestedId ?: ($user ? $user->id : null);

        return $this->renderProfilePage($request, $profileId ? (int) $profileId : null);
    }

    private function renderProfilePage(Request $request, ?int $profileId = null)
    {
        $targetUser = $this->resolveProfileUser($profileId);

        if (!$targetUser) {
            abort(404, 'Khong tim thay nguoi dung.');
        }

        $targetUserId = (int) $targetUser->id;

        $posts = Post::query()
            ->with(['user', 'comments.user', 'comments.replies.user', 'sharedFromPost.user'])
            ->withCount([
                'comments',
                'sharedPosts as shares_count',
                'reactions as like_count' => function ($query) { $query->where('reaction_type', 'like'); },
                'reactions as love_count' => function ($query) { $query->where('reaction_type', 'love'); },
                'reactions as haha_count' => function ($query) { $query->where('reaction_type', 'haha'); },
                'reactions as wow_count' => function ($query) { $query->where('reaction_type', 'wow'); },
                'reactions as sad_count' => function ($query) { $query->where('reaction_type', 'sad'); },
                'reactions as angry_count' => function ($query) { $query->where('reaction_type', 'angry'); },
            ])
            ->where('user_id', $targetUserId)
            ->latest()
            ->get();

        $hotSongsQuery = Song::query()
            ->where('is_hot', true)
            ->where('is_active', true)
            ->orderBy('hot_rank');

        if (Schema::hasColumn('songs', 'file_path')) {
            $hotSongsQuery->whereNotNull('file_path');
        }

        $hotSongs = $hotSongsQuery
            ->get()
            ->unique('title')
            ->take(10)
            ->values();

        $profileStories = collect();
        if (Schema::hasTable('stories') && Schema::hasColumn('stories', 'user_id') && Schema::hasColumn('stories', 'created_at')) {
            $profileStories = Story::query()
                ->where('user_id', $targetUserId)
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->orderByDesc('created_at')
                ->get();
        }

        $friendsData = $this->getFriends($targetUserId);
        $photosData = $this->getPhotos($targetUserId);
        $videosData = $this->getVideos($targetUserId);

        $friendCount = (int) $friendsData->count();
        $isOwnProfile = Auth::check() && (int) Auth::id() === $targetUserId;

        $fullName = trim(
            (string) ($targetUser->first_name ?? $targetUser->First_name ?? '')
            . ' '
            . (string) ($targetUser->last_name ?? $targetUser->Last_name ?? '')
        );
        $profileDisplayName = $fullName !== ''
            ? $fullName
            : ((string) ($targetUser->name ?? $targetUser->Name ?? $targetUser->email ?? $targetUser->Email ?? 'Nguoi dung'));

        $postCount = (int) $posts->count();

        return view('profilepersonalization.profile', [
            'profileId' => $targetUserId,
            'posts' => $posts,
            'currentUser' => $targetUser,
            'currentAvatarUrl' => $targetUser->avatar_url,
            'currentCoverUrl' => $targetUser->cover_url,
            'hotSongs' => $hotSongs,
            'friendSuggestions' => $friendsData,
            'friendCount' => $friendCount,
            'postCount' => $postCount,
            'photoPosts' => $photosData,
            'friendsData' => $friendsData,
            'photosData' => $photosData,
            'videosData' => $videosData,
            'profileDisplayName' => $profileDisplayName,
            'profileStories' => $profileStories,
            'hasActiveStory' => $profileStories->isNotEmpty(),
            'isOwnProfile' => $isOwnProfile,
        ]);
    }

    private function resolveProfileUser(?int $profileId = null): ?User
    {
        if ($profileId !== null) {
            $byId = User::query()->find($profileId);
            if ($byId) {
                return $byId;
            }
        }

        $authUser = Auth::user();
        if ($authUser) {
            return $authUser;
        }

        $keyName = (new User())->getKeyName();
        return User::query()->orderBy($keyName)->first();
    }

    private function resolveEditableUser(Request $request): ?User
    {
        $authUser = Auth::user();
        if ($authUser && $authUser->id !== null) {
            return $authUser;
        }

        $rawProfileId = $request->input('profile_id');
        if ($rawProfileId !== null && $rawProfileId !== '' && is_numeric($rawProfileId)) {
            $profileId = (int) $rawProfileId;
            $requested = User::query()->whereKey($profileId)->first();
            if ($requested && $requested->id !== null) {
                return $requested;
            }
        }

        $keyName = (new User())->getKeyName();
        return User::query()->whereNotNull($keyName)->orderBy($keyName)->first();
    }

    private function assertEditableUser(Request $request): ?User
    {
        $user = $this->resolveEditableUser($request);
        if (!$user || $user->id === null) {
            return null;
        }

        return $user;
    }

    public function getFriends(int $userId): Collection
    {
        if (!Schema::hasTable('friendships')) {
            return collect();
        }

        $friendships = DB::table('friendships')
            ->where('status', 'accepted')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('friend_id', $userId);
            });

        $friendIds = $friendships
            ->get(['user_id', 'friend_id'])
            ->map(function ($row) use ($userId) {
                return (int) ($row->user_id == $userId ? $row->friend_id : $row->user_id);
            })
            ->filter(function ($id) use ($userId) {
                return $id > 0 && $id !== (int) $userId;
            })
            ->unique()
            ->values();

        if ($friendIds->isEmpty()) {
            return collect();
        }

        $keyName = (new User())->getKeyName();
        return User::query()->whereIn($keyName, $friendIds)->get()->values();
    }

    public function getPhotos(int $userId): Collection
    {
        if (!Schema::hasTable('posts') || !Schema::hasColumn('posts', 'user_id')) {
            return collect();
        }

        $query = Post::query()->where('user_id', $userId);

        $hasPostType = Schema::hasColumn('posts', 'post_type');
        $hasMediaType = Schema::hasColumn('posts', 'media_type');
        if ($hasPostType || $hasMediaType) {
            $query->where(function ($builder) use ($hasPostType, $hasMediaType) {
                if ($hasPostType) {
                    $builder->where('post_type', 'image');
                }
                if ($hasMediaType) {
                    if ($hasPostType) {
                        $builder->orWhere('media_type', 'image');
                    } else {
                        $builder->where('media_type', 'image');
                    }
                }
            });
        }

        $hasImageUrl = Schema::hasColumn('posts', 'image_url');
        $hasMediaPath = Schema::hasColumn('posts', 'media_path');
        if ($hasImageUrl || $hasMediaPath) {
            $query->where(function ($builder) use ($hasImageUrl, $hasMediaPath) {
                if ($hasImageUrl) {
                    $builder->whereNotNull('image_url');
                }
                if ($hasMediaPath) {
                    if ($hasImageUrl) {
                        $builder->orWhereNotNull('media_path');
                    } else {
                        $builder->whereNotNull('media_path');
                    }
                }
            });
        }

        return $query->latest()->get();
    }

    public function getVideos(int $userId): Collection
    {
        if (!Schema::hasTable('posts') || !Schema::hasColumn('posts', 'user_id')) {
            return collect();
        }

        $query = Post::query()->where('user_id', $userId);

        $hasPostType = Schema::hasColumn('posts', 'post_type');
        $hasMediaType = Schema::hasColumn('posts', 'media_type');
        if ($hasPostType || $hasMediaType) {
            $query->where(function ($builder) use ($hasPostType, $hasMediaType) {
                if ($hasPostType) {
                    $builder->where('post_type', 'video');
                }
                if ($hasMediaType) {
                    if ($hasPostType) {
                        $builder->orWhere('media_type', 'video');
                    } else {
                        $builder->where('media_type', 'video');
                    }
                }
            });
        }

        if (Schema::hasColumn('posts', 'media_path')) {
            $query->whereNotNull('media_path');
        }

        return $query->latest()->get();
    }

    public function updateDetails(Request $request)
    {
        return $this->updateIntro($request);
    }

    public function updateAccount(Request $request)
    {
        $user = $this->assertEditableUser($request);
        if (!$user) {
            return response()->json([
                'message' => 'Khong tim thay nguoi dung de cap nhat tai khoan.',
            ], 404);
        }

        $emailColumn = $this->resolveExistingUsersColumn(['email', 'Email']);
        if ($emailColumn === null) {
            return response()->json([
                'message' => 'Bang users chua co cot email tuong thich.',
            ], 422);
        }

        $keyName = (new User())->getKeyName();

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', $emailColumn)->ignore($user->id, $keyName)],
            'phone' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other,nam,nu,khac'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'confirmed', 'string', 'min:6'],
        ]);

        $passwordColumn = $this->resolveExistingUsersColumn(['password', 'Password']);
        $storedPassword = (string) ($passwordColumn ? ($user->{$passwordColumn} ?? '') : '');

        if (!empty($validated['new_password'])) {
            if ($passwordColumn === null) {
                return response()->json([
                    'message' => 'Bang users chua co cot mat khau tuong thich.',
                ], 422);
            }

            $currentPasswordInput = (string) ($validated['current_password'] ?? '');
            $isCorrectCurrentPassword = false;

            if ($storedPassword !== '') {
                try {
                    $isCorrectCurrentPassword = Hash::check($currentPasswordInput, $storedPassword);
                } catch (\Throwable $exception) {
                    $isCorrectCurrentPassword = false;
                }

                // Legacy SQL may store plain text passwords.
                if (!$isCorrectCurrentPassword) {
                    $isCorrectCurrentPassword = hash_equals($storedPassword, $currentPasswordInput);
                }
            }

            if (!$isCorrectCurrentPassword) {
                return response()->json([
                    'message' => 'Mat khau cu khong chinh xac.',
                ], 422);
            }
        }

        $updates = [
            $emailColumn => $validated['email'],
        ];

        $firstName = trim((string) ($validated['first_name'] ?? ''));
        $lastName = trim((string) ($validated['last_name'] ?? ''));
        $displayName = trim($firstName . ' ' . $lastName);
        if ($displayName !== '') {
            $nameColumn = $this->resolveExistingUsersColumn(['name', 'Name']);
            if ($nameColumn !== null) {
                $updates[$nameColumn] = $displayName;
            }
        }

        $firstNameColumn = $this->resolveExistingUsersColumn(['first_name', 'First_name']);
        if ($firstNameColumn !== null) {
            $updates[$firstNameColumn] = $firstName !== '' ? $firstName : null;
        }
        $lastNameColumn = $this->resolveExistingUsersColumn(['last_name', 'Last_name']);
        if ($lastNameColumn !== null) {
            $updates[$lastNameColumn] = $lastName !== '' ? $lastName : null;
        }
        $phoneColumn = $this->resolveExistingUsersColumn(['phone', 'Phone']);
        if ($phoneColumn !== null) {
            $updates[$phoneColumn] = !empty($validated['phone']) ? $validated['phone'] : null;
        }
        $birthDateColumn = $this->resolveExistingUsersColumn(['birth_date', 'BirthDate']);
        if ($birthDateColumn !== null) {
            $updates[$birthDateColumn] = !empty($validated['birth_date']) ? $validated['birth_date'] : null;
        }
        $genderColumn = $this->resolveExistingUsersColumn(['gender', 'Gender']);
        if ($genderColumn !== null) {
            $updates[$genderColumn] = !empty($validated['gender'])
                ? $this->normalizeGenderValue((string) $validated['gender'], $genderColumn)
                : null;
        }
        if (!empty($validated['new_password']) && $passwordColumn !== null) {
            $updates[$passwordColumn] = Hash::make($validated['new_password']);
        }

        DB::table('users')->where($keyName, $user->id)->update($updates);

        $freshUser = User::query()->whereKey($user->id)->first();
        if ($freshUser instanceof User) {
            $user = $freshUser;
        }

        return response()->json([
            'ok' => true,
            'message' => 'Cap nhat tai khoan thanh cong.',
            'data' => [
                'name' => $this->firstUserValue($user, ['name', 'Name']),
                'email' => $this->firstUserValue($user, ['email', 'Email']),
                'first_name' => $this->firstUserValue($user, ['first_name', 'First_name']),
                'last_name' => $this->firstUserValue($user, ['last_name', 'Last_name']),
                'phone' => $this->firstUserValue($user, ['phone', 'Phone']),
                'birth_date' => $this->firstUserValue($user, ['birth_date', 'BirthDate']),
                'gender' => $this->firstUserValue($user, ['gender', 'Gender']),
            ],
        ]);
    }

    public function updateIntro(Request $request)
    {
        $user = $this->assertEditableUser($request);
        if (!$user) {
            return response()->json([
                'message' => 'Khong tim thay nguoi dung de cap nhat gioi thieu.',
            ], 404);
        }

        $rules = [];
        if (Schema::hasColumn('users', 'bio')) {
            $rules['bio'] = ['nullable', 'string', 'max:101'];
        }
        if (Schema::hasColumn('users', 'work')) {
            $rules['work'] = ['nullable', 'string', 'max:255'];
        }
        if (Schema::hasColumn('users', 'education')) {
            $rules['education'] = ['nullable', 'string', 'max:255'];
        }
        if (Schema::hasColumn('users', 'location')) {
            $rules['location'] = ['nullable', 'string', 'max:255'];
        }
        if (Schema::hasColumn('users', 'hometown')) {
            $rules['hometown'] = ['nullable', 'string', 'max:255'];
        }
        if (Schema::hasColumn('users', 'relationship')) {
            $rules['relationship'] = ['nullable', 'in:doc-than,hen-ho,da-ket-hon'];
        }

        $validated = empty($rules) ? [] : $request->validate($rules);

        $updates = [];
        foreach (['bio', 'work', 'education', 'location', 'hometown', 'relationship'] as $field) {
            if (array_key_exists($field, $validated)) {
                $updates[$field] = $validated[$field];
            }
        }

        if (!empty($updates)) {
            $user->update($updates);
        }

        $user->refresh();

        return response()->json([
            'ok' => true,
            'message' => 'Cap nhat gioi thieu thanh cong.',
            'data' => [
                'bio' => $user->bio ?? null,
                'work' => $user->work ?? null,
                'education' => $user->education ?? null,
                'location' => $user->location ?? null,
                'hometown' => $user->hometown ?? null,
                'relationship' => $user->relationship ?? null,
            ],
        ]);
    }

    public function updateImages(Request $request)
    {
        $request->validate([
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'cover_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        if (!$request->hasFile('avatar') && !$request->hasFile('cover_image')) {
            return response()->json([
                'ok' => false,
                'message' => 'Vui long chon it nhat mot anh avatar hoac cover_image.',
            ], 422);
        }

        $user = $this->assertEditableUser($request);
        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'Khong tim thay nguoi dung de cap nhat anh.',
            ], 404);
        }

        $avatarColumns = [];
        if (Schema::hasColumn('users', 'avatar')) {
            $avatarColumns[] = 'avatar';
        }
        if (Schema::hasColumn('users', 'avatar_path')) {
            $avatarColumns[] = 'avatar_path';
        }
        if (Schema::hasColumn('users', 'img')) {
            $avatarColumns[] = 'img';
        }
        if (Schema::hasColumn('users', 'AvatarURL')) {
            $avatarColumns[] = 'AvatarURL';
        }

        $coverColumns = [];
        if (Schema::hasColumn('users', 'cover_image')) {
            $coverColumns[] = 'cover_image';
        }
        if (Schema::hasColumn('users', 'cover_path')) {
            $coverColumns[] = 'cover_path';
        }

        $updates = [];
        $responseData = [];

        if ($request->hasFile('avatar')) {
            foreach ($avatarColumns as $column) {
                $this->deletePublicFileIfExists((string) ($user->{$column} ?? ''));
            }

            $avatarPath = $request->file('avatar')->store('uploads/profiles', 'public');
            foreach ($avatarColumns as $column) {
                if ($column === 'AvatarURL') {
                    $updates[$column] = asset('storage/' . ltrim($avatarPath, '/'));
                } else {
                    $updates[$column] = $avatarPath;
                }
            }

            $responseData['avatar_path'] = $avatarPath;
            $responseData['avatar_url'] = asset('storage/' . ltrim($avatarPath, '/'));
        }

        if ($request->hasFile('cover_image')) {
            foreach ($coverColumns as $column) {
                $this->deletePublicFileIfExists((string) ($user->{$column} ?? ''));
            }

            $coverPath = $request->file('cover_image')->store('uploads/profiles', 'public');
            foreach ($coverColumns as $column) {
                $updates[$column] = $coverPath;
            }

            $responseData['cover_path'] = $coverPath;
            $responseData['cover_url'] = asset('storage/' . ltrim($coverPath, '/'));
        }

        if (empty($updates)) {
            return response()->json([
                'ok' => false,
                'message' => 'Bang users chua co cot avatar/cover_image tuong thich.',
            ], 422);
        }

        $user->update($updates);

        return response()->json([
            'ok' => true,
            'message' => 'Cap nhat anh thanh cong.',
            'data' => $responseData,
        ]);
    }

    private function deletePublicFileIfExists(string $rawPath): void
    {
        $path = trim($rawPath);
        if ($path === '' || stripos($path, 'http://') === 0 || stripos($path, 'https://') === 0) {
            return;
        }

        $normalized = str_replace('\\', '/', $path);
        if (strpos($normalized, '/storage/') === 0) {
            $normalized = substr($normalized, 9);
        }
        if (strpos($normalized, 'storage/') === 0) {
            $normalized = substr($normalized, 8);
        }
        $normalized = ltrim($normalized, '/');

        if ($normalized !== '' && Storage::disk('public')->exists($normalized)) {
            Storage::disk('public')->delete($normalized);
        }
    }

    public function updateCover(Request $request)
    {
        $validated = $request->validate([
            'cover' => ['required', 'file', 'image', 'max:10240'],
        ]);

        $user = $this->assertEditableUser($request);

        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'Khong tim thay nguoi dung de cap nhat anh bia.',
            ], 404);
        }

        if (!empty($user->cover_path) && Storage::disk('public')->exists($user->cover_path)) {
            Storage::disk('public')->delete($user->cover_path);
        }

        $directory = 'covers/' . $user->id;
        Storage::disk('public')->makeDirectory($directory);

        $extension = $request->file('cover')->getClientOriginalExtension() ?: 'jpg';
        $fileName = 'cover_' . now()->format('Ymd_His') . '_' . Str::random(10) . '.' . strtolower($extension);
        $path = $request->file('cover')->storeAs($directory, $fileName, 'public');

        $user->cover_path = $path;
        $user->save();

        return response()->json([
            'ok' => true,
            'cover_url' => $user->cover_url,
            'cover_path' => $path,
            'message' => 'Cap nhat anh bia thanh cong.',
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'string'],
        ]);

        $user = $this->assertEditableUser($request);
        if (!$user) {
            return response()->json([
                'message' => 'Khong tim thay nguoi dung de cap nhat anh dai dien.',
            ], 404);
        }

        $dataUrl = $request->input('avatar');
        if (!preg_match('/^data:image\/(png|jpe?g|webp);base64,/', $dataUrl)) {
            return response()->json([
                'message' => 'Dinh dang anh khong hop le.',
            ], 422);
        }

        $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $decoded = base64_decode($base64, true);

        if ($decoded === false) {
            return response()->json([
                'message' => 'Khong the xu ly anh dai dien.',
            ], 422);
        }

        $extension = 'jpg';
        if (str_starts_with($dataUrl, 'data:image/png')) {
            $extension = 'png';
        } elseif (str_starts_with($dataUrl, 'data:image/webp')) {
            $extension = 'webp';
        }

        $directory = 'avatars/' . $user->id;
        $fileName = 'avatar_' . now()->format('Ymd_His') . '_' . Str::random(10) . '.' . $extension;
        $path = $directory . '/' . $fileName;

        Storage::disk('public')->makeDirectory($directory);

        if (!empty($user->avatar_path) && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        Storage::disk('public')->put($path, $decoded);

        $user->avatar_path = $path;
        $user->save();

        return response()->json([
            'ok' => true,
            'avatar_url' => $user->avatar_url,
            'avatar_path' => $path,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function activityLog(Request $request)
    {
        $requestedId = $request->query('id');
        $profileId = is_numeric($requestedId) ? (int) $requestedId : null;
        $targetUser = $this->resolveProfileUser($profileId);

        if (!$targetUser || $targetUser->id === null) {
            return view('profilepersonalization.activity-log', [
                'activityGroups' => collect(),
                'selectedYear' => (int) Carbon::now()->year,
                'selectedType' => 'all',
                'years' => collect([(int) Carbon::now()->year]),
                'profileId' => $profileId,
                'typeOptions' => [
                    'all' => 'Tat ca',
                    'like' => 'Like',
                    'comment' => 'Comment',
                    'post' => 'Post',
                    'story' => 'Story',
                ],
            ]);
        }

        $targetUserId = (int) $targetUser->id;
        $allActivities = collect();

        if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'user_id') && Schema::hasColumn('posts', 'created_at')) {
            $postColumns = ['id', 'created_at'];
            if (Schema::hasColumn('posts', 'content')) {
                $postColumns[] = 'content';
            }
            if (Schema::hasColumn('posts', 'post_type')) {
                $postColumns[] = 'post_type';
            }

            $postActivities = DB::table('posts')
                ->where('user_id', $targetUserId)
                ->orderByDesc('created_at')
                ->limit(300)
                ->get($postColumns)
                ->map(function ($postRow) {
                    $summary = $this->summarizePostText(
                        isset($postRow->content) ? (string) $postRow->content : '',
                        isset($postRow->post_type) ? (string) $postRow->post_type : null
                    );

                    return [
                        'type' => 'post',
                        'description' => 'Ban da dang bai viet moi "' . $summary . '".',
                        'at' => Carbon::parse((string) $postRow->created_at),
                    ];
                });

            $allActivities = $allActivities->concat($postActivities);
        }

        if (Schema::hasTable('post_comments') && Schema::hasColumn('post_comments', 'user_id') && Schema::hasColumn('post_comments', 'created_at')) {
            $commentQuery = DB::table('post_comments')->where('post_comments.user_id', $targetUserId);

            $commentColumns = ['post_comments.created_at'];
            if (Schema::hasColumn('post_comments', 'content')) {
                $commentColumns[] = 'post_comments.content';
            }

            $canJoinPosts = Schema::hasTable('posts') && Schema::hasColumn('post_comments', 'post_id') && Schema::hasColumn('posts', 'id');
            if ($canJoinPosts) {
                $commentQuery->leftJoin('posts', 'posts.id', '=', 'post_comments.post_id');
                if (Schema::hasColumn('posts', 'content')) {
                    $commentColumns[] = 'posts.content as post_content';
                }
                if (Schema::hasColumn('posts', 'post_type')) {
                    $commentColumns[] = 'posts.post_type as post_type';
                }
            }

            $commentActivities = $commentQuery
                ->orderByDesc('post_comments.created_at')
                ->limit(300)
                ->get($commentColumns)
                ->map(function ($commentRow) {
                    $commentText = isset($commentRow->content) ? trim((string) $commentRow->content) : '';
                    $postSummary = $this->summarizePostText(
                        isset($commentRow->post_content) ? (string) $commentRow->post_content : '',
                        isset($commentRow->post_type) ? (string) $commentRow->post_type : null
                    );

                    $description = $commentText !== ''
                        ? 'Ban da binh luan "' . Str::limit($commentText, 60) . '" trong bai viet "' . $postSummary . '".'
                        : 'Ban da binh luan vao bai viet "' . $postSummary . '".';

                    return [
                        'type' => 'comment',
                        'description' => $description,
                        'at' => Carbon::parse((string) $commentRow->created_at),
                    ];
                });

            $allActivities = $allActivities->concat($commentActivities);
        }

        if (Schema::hasTable('post_reactions') && Schema::hasColumn('post_reactions', 'user_id') && Schema::hasColumn('post_reactions', 'created_at')) {
            $reactionQuery = DB::table('post_reactions')->where('post_reactions.user_id', $targetUserId);

            $reactionColumns = ['post_reactions.created_at'];
            if (Schema::hasColumn('post_reactions', 'reaction_type')) {
                $reactionColumns[] = 'post_reactions.reaction_type';
            }

            $canJoinPosts = Schema::hasTable('posts') && Schema::hasColumn('post_reactions', 'post_id') && Schema::hasColumn('posts', 'id');
            if ($canJoinPosts) {
                $reactionQuery->leftJoin('posts', 'posts.id', '=', 'post_reactions.post_id');
                if (Schema::hasColumn('posts', 'content')) {
                    $reactionColumns[] = 'posts.content as post_content';
                }
                if (Schema::hasColumn('posts', 'post_type')) {
                    $reactionColumns[] = 'posts.post_type as post_type';
                }
            }

            $reactionActivities = $reactionQuery
                ->orderByDesc('post_reactions.created_at')
                ->limit(300)
                ->get($reactionColumns)
                ->map(function ($reactionRow) {
                    $postSummary = $this->summarizePostText(
                        isset($reactionRow->post_content) ? (string) $reactionRow->post_content : '',
                        isset($reactionRow->post_type) ? (string) $reactionRow->post_type : null
                    );

                    $reactionType = isset($reactionRow->reaction_type) ? Str::lower((string) $reactionRow->reaction_type) : 'like';
                    $verb = $reactionType === 'love'
                        ? 'yeu thich'
                        : ($reactionType === 'haha'
                            ? 'tha ha ha cho'
                            : ($reactionType === 'wow'
                                ? 'tha wow cho'
                                : ($reactionType === 'sad'
                                    ? 'tha buon cho'
                                    : ($reactionType === 'angry'
                                        ? 'tha phan no cho'
                                        : 'thich'))));

                    return [
                        'type' => 'like',
                        'description' => 'Ban da ' . $verb . ' bai viet "' . $postSummary . '".',
                        'at' => Carbon::parse((string) $reactionRow->created_at),
                    ];
                });

            $allActivities = $allActivities->concat($reactionActivities);
        }

        if (Schema::hasTable('stories') && Schema::hasColumn('stories', 'user_id') && Schema::hasColumn('stories', 'created_at')) {
            $storyColumns = ['created_at'];
            if (Schema::hasColumn('stories', 'caption')) {
                $storyColumns[] = 'caption';
            }
            if (Schema::hasColumn('stories', 'music_name')) {
                $storyColumns[] = 'music_name';
            }

            $storyActivities = Story::query()
                ->where('user_id', $targetUserId)
                ->orderByDesc('created_at')
                ->limit(300)
                ->get($storyColumns)
                ->map(function ($storyRow) {
                    $caption = isset($storyRow->caption) ? trim((string) $storyRow->caption) : '';
                    $musicName = isset($storyRow->music_name) ? trim((string) $storyRow->music_name) : '';

                    if ($caption !== '') {
                        $description = 'Ban da dang tin "' . Str::limit($caption, 70) . '".';
                    } else {
                        $description = 'Ban da dang mot tin moi.';
                    }

                    if ($musicName !== '') {
                        $description .= ' Nhac: ' . Str::limit($musicName, 40) . '.';
                    }

                    return [
                        'type' => 'story',
                        'description' => $description,
                        'at' => Carbon::parse((string) $storyRow->created_at),
                    ];
                });

            $allActivities = $allActivities->concat($storyActivities);
        }

        $allActivities = $allActivities
            ->sortByDesc(function ($activity) {
                return optional($activity['at'])->timestamp ?? 0;
            })
            ->values();

        $years = $allActivities
            ->map(function ($activity) {
                return (int) $activity['at']->year;
            })
            ->unique()
            ->sortDesc()
            ->values();

        if ($years->isEmpty()) {
            $years = collect([(int) Carbon::now()->year]);
        }

        $selectedYear = (int) $request->query('year', (int) Carbon::now()->year);
        if (!$years->contains($selectedYear)) {
            $selectedYear = (int) ($years->first() ?? Carbon::now()->year);
        }

        $selectedType = (string) $request->query('type', 'all');
        $allowedTypes = ['all', 'like', 'comment', 'post', 'story'];
        if (!in_array($selectedType, $allowedTypes, true)) {
            $selectedType = 'all';
        }

        $filtered = $allActivities
            ->filter(function ($activity) use ($selectedYear) {
                return (int) $activity['at']->year === $selectedYear;
            })
            ->when($selectedType !== 'all', function ($collection) use ($selectedType) {
                return $collection->where('type', $selectedType);
            })
            ->sortByDesc(function ($activity) {
                return $activity['at']->timestamp;
            })
            ->values();

        $activityGroups = $filtered->groupBy(function ($activity) {
            $activityTime = $activity['at'];
            if ($activityTime->isToday()) {
                return 'Hom nay';
            }
            if ($activityTime->isYesterday()) {
                return 'Hom qua';
            }

            return 'Ngay ' . $activityTime->format('d/m/Y');
        });

        return view('profilepersonalization.activity-log', [
            'activityGroups' => $activityGroups,
            'selectedYear' => $selectedYear,
            'selectedType' => $selectedType,
            'years' => $years,
            'profileId' => $targetUserId,
            'typeOptions' => [
                'all' => 'Tat ca',
                'like' => 'Like',
                'comment' => 'Comment',
                'post' => 'Post',
                'story' => 'Story',
            ],
        ]);
    }

    private function summarizePostText(string $rawContent, ?string $postType = null): string
    {
        $plain = trim((string) preg_replace('/\s+/u', ' ', strip_tags(str_replace('&nbsp;', ' ', $rawContent))));
        if ($plain !== '') {
            return Str::limit($plain, 70);
        }

        $type = Str::lower((string) $postType);
        if ($type === 'image') {
            return 'Anh moi';
        }
        if ($type === 'video') {
            return 'Video moi';
        }

        return 'Khong co noi dung';
    }

    private function resolveExistingUsersColumn(array $candidates): ?string
    {
        $existingColumns = Schema::getColumnListing('users');

        foreach ($candidates as $candidate) {
            foreach ($existingColumns as $existingColumn) {
                if (strcasecmp($existingColumn, $candidate) === 0) {
                    return $existingColumn;
                }
            }
        }

        return null;
    }

    private function firstUserValue(User $user, array $candidates)
    {
        foreach ($candidates as $column) {
            if (array_key_exists($column, $user->getAttributes())) {
                return $user->{$column};
            }
        }

        return null;
    }

    private function normalizeGenderValue(string $value, string $column): string
    {
        $normalized = Str::lower(Str::ascii(trim($value)));

        if ($column === 'Gender') {
            if (in_array($normalized, ['male', 'nam'], true)) {
                return 'Nam';
            }
            if (in_array($normalized, ['female', 'nu'], true)) {
                return 'Nu';
            }

            return 'Khac';
        }

        if (in_array($normalized, ['nam'], true)) {
            return 'male';
        }
        if (in_array($normalized, ['nu'], true)) {
            return 'female';
        }
        if (in_array($normalized, ['khac'], true)) {
            return 'other';
        }

        return $value;
    }
}
