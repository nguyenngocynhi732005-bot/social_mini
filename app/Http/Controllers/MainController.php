<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\Song;
use App\Models\Post;
use App\Models\Story;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MainController extends Controller
{
    public function index(Request $request) {
    $this->purgeExpiredStories();

    // 1. Lấy dữ liệu từ bảng stories, kèm thông tin người dùng (user)
    $stories = Story::with('user')
        ->where('created_at', '>=', Carbon::now()->subDay())
        ->orderBy('created_at', 'desc')
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

    $latestStoryId = (int) optional($stories->first())->id;
    $storyCount = (int) $stories->count();

    $posts = $this->getPosts($request);

    // 2. Truyền biến $stories sang view newsfeed
    // Chú ý: Tên trong compact('stories') phải khớp với tên biến $stories
    return view('pages.newsfeed', compact('stories', 'hotSongs', 'latestStoryId', 'storyCount', 'posts'));
}

private function getPosts(Request $request)
{
    $viewerId = $this->resolveViewerId($request);
    $friendIds = $this->getFriendIds($viewerId);

    $query = Post::query()
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
        ]);

    if (Schema::hasColumn('posts', 'privacy_status')) {
        $query->where(function ($visibilityQuery) use ($viewerId, $friendIds) {
            $visibilityQuery
                ->whereNull('privacy_status')
                ->orWhere('privacy_status', 'public');

            if ($viewerId > 0) {
                $visibilityQuery->orWhere(function ($friendsQuery) use ($friendIds) {
                    $friendsQuery->where('privacy_status', 'friends');

                    if (!empty($friendIds)) {
                        $friendsQuery->whereIn('user_id', $friendIds);
                    } else {
                        $friendsQuery->whereRaw('1 = 0');
                    }
                });

                $visibilityQuery->orWhere(function ($privateQuery) use ($viewerId) {
                    $privateQuery
                        ->where('privacy_status', 'private')
                        ->where('user_id', $viewerId);
                });
            }
        });
    }

    return $query->latest()->get();
}

private function resolveViewerId(Request $request): int
{
    $rawProfileId = $request->input('profile_id');
    if ($rawProfileId !== null && $rawProfileId !== '' && is_numeric($rawProfileId)) {
        $profileId = (int) $rawProfileId;
        if ($profileId > 0) {
            return $profileId;
        }
    }

    $authUser = Auth::user();
    if ($authUser && $authUser->getKey() !== null) {
        return (int) $authUser->getKey();
    }

    $keyName = (new User())->getKeyName();
    $fallbackUser = User::query()->whereNotNull($keyName)->orderBy($keyName)->first();

    return $fallbackUser ? (int) $fallbackUser->getKey() : 0;
}

private function getFriendIds(int $viewerId): array
{
    if ($viewerId <= 0 || !Schema::hasTable('friendships')) {
        return [];
    }

    return DB::table('friendships')
        ->where('status', 'accepted')
        ->where(function ($query) use ($viewerId) {
            $query->where('user_id', $viewerId)
                ->orWhere('friend_id', $viewerId);
        })
        ->get(['user_id', 'friend_id'])
        ->map(function ($row) use ($viewerId) {
            return (int) ($row->user_id == $viewerId ? $row->friend_id : $row->user_id);
        })
        ->filter(function ($id) use ($viewerId) {
            return $id > 0 && $id !== $viewerId;
        })
        ->unique()
        ->values()
        ->all();
}

public function storiesSnapshot()
{
    $this->purgeExpiredStories();

    $latestStory = Story::query()
        ->where('created_at', '>=', Carbon::now()->subDay())
        ->orderByDesc('id')
        ->first();

    return response()->json([
        'latestStoryId' => (int) optional($latestStory)->id,
        'storyCount' => (int) Story::query()->where('created_at', '>=', Carbon::now()->subDay())->count(),
    ]);
}

public function videos(Request $request) {
    $videoPostsQuery = Post::query()->with('user');

    if (Schema::hasColumn('posts', 'media_type')) {
        $videoPostsQuery->where('media_type', 'video');
    } elseif (Schema::hasColumn('posts', 'post_type')) {
        $videoPostsQuery->where('post_type', 'video');
    } else {
        // Keep behavior predictable if media type columns are missing.
        $videoPostsQuery->whereRaw('1 = 0');
    }

    $videoPosts = $videoPostsQuery
        ->latest()
        ->get();

    if ($request->ajax()) {
        return view('pages.videos', compact('videoPosts'));
    }
    return view('pages.videos', compact('videoPosts'));
}

public function friends(Request $request) {
    $viewerId = $this->resolveViewerId($request);
    $viewerKeyName = (new User())->getKeyName();
    $targetId = (int) $request->query('target_id', 0);

    $displayName = function ($user) {
        $first = $user->First_name ?? $user->first_name ?? null;
        $last = $user->Last_name ?? $user->last_name ?? null;
        $fullName = trim(implode(' ', array_filter([$first, $last])));

        if ($fullName !== '') {
            return $fullName;
        }

        return trim($user->name ?? $user->Name ?? ('User #' . ($user->id ?? '')));
    };

    $friendships = Friendship::query()
        ->whereIn('status', ['pending', 'accepted', 'blocked'])
        ->where(function ($query) use ($viewerId) {
            $query->where('user_id', $viewerId)
                ->orWhere('friend_id', $viewerId);
        })
        ->get(['user_id', 'friend_id', 'status']);

    $relationStatusByUser = [];
    foreach ($friendships as $friendship) {
        $otherId = (int) ($friendship->user_id == $viewerId ? $friendship->friend_id : $friendship->user_id);
        $relationStatusByUser[$otherId] = $friendship->status;
    }

    $acceptedFriendIds = $friendships
        ->where('status', 'accepted')
        ->map(function ($friendship) use ($viewerId) {
            return (int) ($friendship->user_id == $viewerId ? $friendship->friend_id : $friendship->user_id);
        })
        ->filter(function ($id) use ($viewerId) {
            return $id > 0 && $id !== $viewerId;
        })
        ->unique()
        ->values();

    $pendingRequesterIds = $friendships
        ->where('status', 'pending')
        ->map(function ($friendship) use ($viewerId) {
            return (int) ($friendship->friend_id == $viewerId ? $friendship->user_id : 0);
        })
        ->filter(function ($id) {
            return $id > 0;
        })
        ->unique()
        ->values();

    $blockedIds = $friendships
        ->where('status', 'blocked')
        ->map(function ($friendship) use ($viewerId) {
            return (int) ($friendship->user_id == $viewerId ? $friendship->friend_id : $friendship->user_id);
        })
        ->filter(function ($id) use ($viewerId) {
            return $id > 0 && $id !== $viewerId;
        })
        ->unique()
        ->values();

    $friends = User::query()
        ->whereIn($viewerKeyName, $acceptedFriendIds)
        ->orderBy('Name')
        ->get();

    $highlightUser = null;
    if ($targetId > 0 && $targetId !== $viewerId) {
        $highlightUser = User::query()
            ->whereKey($targetId)
            ->first();
    }

    $suggestedUsers = User::query()
        ->when($viewerId > 0, function ($query) use ($viewerKeyName, $viewerId) {
            $query->where($viewerKeyName, '!=', $viewerId);
        })
        ->when($blockedIds->isNotEmpty(), function ($query) use ($viewerKeyName, $blockedIds) {
            $query->whereNotIn($viewerKeyName, $blockedIds->all());
        })
        ->when($acceptedFriendIds->isNotEmpty(), function ($query) use ($viewerKeyName, $acceptedFriendIds) {
            $query->whereNotIn($viewerKeyName, $acceptedFriendIds->all());
        })
        ->when($pendingRequesterIds->isNotEmpty(), function ($query) use ($viewerKeyName, $pendingRequesterIds) {
            $query->whereNotIn($viewerKeyName, $pendingRequesterIds->all());
        })
        ->when($highlightUser, function ($query) use ($viewerKeyName, $highlightUser) {
            $query->where($viewerKeyName, '!=', $highlightUser->getKey());
        })
        ->orderBy('Name')
        ->limit(8)
        ->get();

    $pendingRequests = User::query()
        ->whereIn($viewerKeyName, $pendingRequesterIds)
        ->orderBy('Name')
        ->get();

    $blockedUsers = User::query()
        ->whereIn($viewerKeyName, $blockedIds)
        ->orderBy('Name')
        ->get();

    return view('pages.friends', compact(
        'highlightUser',
        'suggestedUsers',
        'friends',
        'relationStatusByUser',
        'pendingRequests',
        'blockedUsers',
        'displayName'
    ));
}
    public function storeStory(Request $request) {
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'caption' => 'nullable|string|max:500',
        'text_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        'music_id' => 'nullable|integer|exists:songs,id',
        'image_scale' => 'nullable|numeric|min:0.5|max:2',
    ]);

    $storyUser = $this->resolveStoryUser($request);
    $storyUserId = (int) ($storyUser ? $storyUser->getKey() : 0);
    if ($storyUserId <= 0) {
        return back()->withErrors(['story' => 'Khong tim thay nguoi dung de dang story.']);
    }

    if ($request->hasFile('image')) {
        // Lưu ảnh vào thư mục storage/app/public/stories
        $path = $request->file('image')->store('stories', 'public');

        $selectedSong = null;
        if ($request->filled('music_id')) {
            $selectedSong = Song::query()->find((int) $request->input('music_id'));
        }

        $musicName = $selectedSong ? $selectedSong->title : null;
        $musicPath = $selectedSong ? $selectedSong->playable_url : null;

        // Lưu vào database
        Story::create([
            'user_id' => $storyUserId,
            'image_path' => $path,
            'caption' => $request->input('caption'),
            'text_color' => $request->input('text_color', '#ffffff'),
            'music_name' => $musicName,
            'music_path' => $musicPath,
            'image_scale' => $request->input('image_scale', 1),
        ]);
    }

    return back()->with('success', 'Đã đăng tin thành công!');
}

public function destroyStory(Story $story)
{
    if ($story->image_path) {
        Storage::disk('public')->delete($story->image_path);
    }

    $story->delete();

    return back()->with('success', 'Đã xóa story.');
}

private function purgeExpiredStories(): void
{
    $expiredStories = Story::query()
        ->where('created_at', '<', Carbon::now()->subDay())
        ->get(['id', 'image_path']);

    if ($expiredStories->isEmpty()) {
        return;
    }

    foreach ($expiredStories as $expiredStory) {
        if ($expiredStory->image_path) {
            try {
                Storage::disk('public')->delete($expiredStory->image_path);
            } catch (\Throwable $exception) {
                Log::warning('Failed to delete expired story image', [
                    'story_id' => $expiredStory->id,
                    'image_path' => $expiredStory->image_path,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    Story::query()->whereIn('id', $expiredStories->pluck('id'))->delete();
}

private function resolveStoryUser(Request $request): ?User
{
    $rawProfileId = $request->input('profile_id');
    if ($rawProfileId !== null && $rawProfileId !== '' && is_numeric($rawProfileId)) {
        $profileId = (int) $rawProfileId;
        $byProfile = User::query()->whereKey($profileId)->first();
        if ($byProfile) {
            return $byProfile;
        }
    }

    $authUser = Auth::user();
    if ($authUser && $authUser->getKey() !== null) {
        return $authUser;
    }

    $keyName = (new User())->getKeyName();
    return User::query()->whereNotNull($keyName)->orderBy($keyName)->first();
}
}