<?php

namespace App\Http\Controllers;

use App\Models\Post;            
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,bmp,avif,heic,heif,mp4,webm,ogg,mov|max:2097152',
            'uploaded_media_path' => 'nullable|string|max:255',
            'uploaded_media_type' => 'nullable|in:image,video',
            'privacy_status' => 'nullable|in:public,friends,private',
            'text_color' => 'nullable|string|max:32',
            'font_family' => 'nullable|string|max:100',
        ], [
            'media.max' => 'Video của bạn vượt giới hạn 2GB nên không đăng được.',
        ]);

        $rawContent = (string) $request->input('content', '');
        $rawContent = str_replace('<span class="ql-cursor">﻿</span>', '', $rawContent);
        $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags(str_replace('&nbsp;', ' ', $rawContent))));
        $hasUploadedMedia = (bool) $request->filled('uploaded_media_path') || (bool) $request->filled('uploaded_media_type');
        $content = $plainText !== '' ? $rawContent : null;

        if ($plainText === '' && !$request->hasFile('media') && !$hasUploadedMedia) {
            return back()->withErrors(['post' => 'Bài viết cần nội dung hoặc ảnh/video.'])->withInput();
        }

        $postUser = $this->resolvePostUser($request);
        if (!$postUser || $postUser->getKey() === null) {
            return back()->withErrors(['post' => 'Khong tim thay nguoi dung de dang bai viet.'])->withInput();
        }

        $mediaPath = $request->input('uploaded_media_path');
        $mediaType = $request->input('uploaded_media_type');

        if ($mediaPath && !$mediaType) {
            $extension = strtolower(pathinfo((string) $mediaPath, PATHINFO_EXTENSION));
            $mediaType = in_array($extension, ['mp4', 'webm', 'ogg', 'mov', 'm4v'], true) ? 'video' : 'image';
        }

        if (!$mediaPath && $request->hasFile('media')) {
            $file = $request->file('media');
            $mediaPath = $file->store('posts', 'public');
            $mediaType = Str::startsWith((string) $file->getMimeType(), 'video/') ? 'video' : 'image';
        }

        $payload = [
            'user_id' => (int) $postUser->getKey(),
            'content' => $content,
        ];

        $privacyStatus = (string) $request->input('privacy_status', 'public');
        if (!in_array($privacyStatus, ['public', 'friends', 'private'], true)) {
            $privacyStatus = 'public';
        }
        if (Schema::hasColumn('posts', 'privacy_status')) {
            $payload['privacy_status'] = $privacyStatus;
        }

        if (Schema::hasColumn('posts', 'media_path')) {
            $payload['media_path'] = $mediaPath;
        }
        if (Schema::hasColumn('posts', 'media_type')) {
            $payload['media_type'] = $mediaType;
        }
        if (Schema::hasColumn('posts', 'image_url')) {
            $payload['image_url'] = $mediaPath;
        }
        if (Schema::hasColumn('posts', 'post_type')) {
            $payload['post_type'] = $mediaType;
        }
        if (Schema::hasColumn('posts', 'text_color') && $request->filled('text_color')) {
            $payload['text_color'] = (string) $request->input('text_color');
        }
        if (Schema::hasColumn('posts', 'font_family') && $request->filled('font_family')) {
            $payload['font_family'] = (string) $request->input('font_family');
        }
        if (Schema::hasColumn('posts', 'created_at')) {
            $payload['created_at'] = now();
        }
        if (Schema::hasColumn('posts', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('posts')->insert($payload);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Đã đăng bài viết thành công.',
                'redirect' => $mediaType === 'video' ? route('videos') : route('newsfeed'),
            ]);
        }

        return redirect()->route($mediaType === 'video' ? 'videos' : 'newsfeed')->with('success', 'Đã đăng bài viết thành công.');
    }

    public function updatePrivacy(Request $request, Post $post)
    {
        if (!Schema::hasColumn('posts', 'privacy_status')) {
            return response()->json([
                'ok' => false,
                'message' => 'Bang posts chua co cot privacy_status.',
            ], 422);
        }

        $validated = $request->validate([
            'privacy_status' => 'required|in:public,friends,private',
        ]);

        $postUser = $this->resolvePostUser($request);
        if (!$postUser || (int) $postUser->getKey() <= 0) {
            return response()->json([
                'ok' => false,
                'message' => 'Khong tim thay nguoi dung cap nhat bai viet.',
            ], 403);
        }

        if ((int) $post->user_id !== (int) $postUser->getKey()) {
            return response()->json([
                'ok' => false,
                'message' => 'Ban khong co quyen thay doi quyen rieng tu bai viet nay.',
            ], 403);
        }

        $post->privacy_status = (string) $validated['privacy_status'];
        $post->save();

        return response()->json([
            'ok' => true,
            'message' => 'Da cap nhat quyen rieng tu bai viet.',
            'data' => [
                'post_id' => (int) $post->id,
                'privacy_status' => (string) $post->privacy_status,
            ],
        ]);
    }

    private function resolvePostUser(Request $request): ?User
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
        if ($authUser instanceof User && $authUser->getKey() !== null) {
            return $authUser;
        }

        $keyName = (new User())->getKeyName();
        return User::query()->whereNotNull($keyName)->orderBy($keyName)->first();
    }

    public function destroy(Post $post)
    {
        foreach ([$post->media_path, $post->image_url] as $mediaPath) {
            if (!$mediaPath) {
                continue;
            }

            $normalizedPath = preg_replace('#^storage/#', '', (string) $mediaPath);
            Storage::disk('public')->delete($normalizedPath);
        }

        $post->delete();

        return back()->with('success', 'Đã xóa bài viết.');
    }

public function share(Request $request, $id)
{
    // 1. Tìm bài viết gốc
    $originalPost = Post::findOrFail($id);

    // 2. Tạo bài viết mới (đây chính là bài share)
    $sharedPost = new Post();
    $sharedPost->user_id = auth()->id(); // Người đang share
    $sharedPost->shared_from_id = $originalPost->id; // Lưu ID bài gốc
    
    // Bạn có thể cho phép người dùng viết thêm caption khi share
    $sharedPost->content = $request->input('content', 'Đã chia sẻ một bài viết'); 
    $sharedPost->privacy_status = 'public';
    $sharedPost->save();

    // 3. Tăng số lượng share ở bài viết gốc (nếu bạn đã thêm cột shares_count)
    $originalPost->increment('shares_count');

    // 4. Trả về JSON để AJAX trong file post-engagement.blade.php xử lý UI
    return response()->json([
        'ok' => true,
        'message' => 'Đã chia sẻ lên trang cá nhân!',
        'shares_count' => $originalPost->shares_count
    ]);
}
}
