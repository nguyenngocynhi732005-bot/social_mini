<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\Post;
use App\Models\Story;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MainController extends Controller
{
    public function index() {
    $this->purgeExpiredStories();

    // 1. Lấy dữ liệu từ bảng stories, kèm thông tin người dùng (user)
    $stories = Story::with('user')
        ->where('created_at', '>=', Carbon::now()->subDay())
        ->orderBy('created_at', 'desc')
        ->get();

    $hotSongs = Song::query()
        ->where('is_hot', true)
        ->where('is_active', true)
        ->whereNotNull('file_path')
        ->orderBy('hot_rank')
        ->get()
        ->unique('title')
        ->take(10)
        ->values();

    $latestStoryId = (int) optional($stories->first())->id;
    $storyCount = (int) $stories->count();

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
        ->latest()
        ->get();

    // 2. Truyền biến $stories sang view newsfeed
    // Chú ý: Tên trong compact('stories') phải khớp với tên biến $stories
    return view('pages.newsfeed', compact('stories', 'hotSongs', 'latestStoryId', 'storyCount', 'posts'));
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
        $currentUserId = auth()->id() ?? 1;

        $suggestedUsers = User::query()
            ->where('id', '!=', $currentUserId)
            ->orderBy('name')
            ->limit(8)
            ->get();

        if ($request->ajax()) {
            return view('pages.friends_content', compact('suggestedUsers'));
        }

        $highlightUser = null;

        return view('pages.friends', compact('highlightUser', 'suggestedUsers'));
}
    public function storeStory(Request $request) {
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'caption' => 'nullable|string|max:500',
        'text_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        'music_id' => 'nullable|integer|exists:songs,id',
        'image_scale' => 'nullable|numeric|min:0.5|max:2',
    ]);

    // Không yêu cầu đăng nhập: luôn resolve 1 user hợp lệ để tránh user_id null.
    $storyUser = User::query()->select('id')->first();
    if (!$storyUser) {
        $storyUser = User::query()->firstOrCreate(
            ['email' => 'story_uploader@socialmini.local'],
            [
                'name' => 'Story Uploader',
                'password' => Hash::make(Str::random(24)),
            ]
        );
    }

    $storyUserId = (int) ($storyUser->id ?? 0);
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
}