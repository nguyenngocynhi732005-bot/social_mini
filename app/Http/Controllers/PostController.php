<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,mp4,webm,ogg,mov|max:2097152',
            'uploaded_media_path' => 'nullable|string|max:255',
            'uploaded_media_type' => 'nullable|in:image,video',
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

        $postUser = User::query()->select('id')->first();
        if (!$postUser) {
            $postUser = User::query()->firstOrCreate(
                ['email' => 'post_uploader@socialmini.local'],
                [
                    'name' => 'Post Uploader',
                    'password' => Hash::make(Str::random(24)),
                ]
            );
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
            'user_id' => (int) $postUser->id,
            'content' => $content,
        ];

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
}
