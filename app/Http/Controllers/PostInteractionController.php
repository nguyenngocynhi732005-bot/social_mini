<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostReaction;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PostInteractionController extends Controller
{
    private const REACTION_TYPES = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];

    public function storeReaction(Request $request, Post $post)
    {
        $validated = $request->validate([
            'reaction_type' => ['required', 'in:' . implode(',', self::REACTION_TYPES)],
        ]);

        $user = $this->resolveInteractionUser();
        $reactionType = $validated['reaction_type'];

        $existingReaction = PostReaction::query()
            ->where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingReaction && $existingReaction->reaction_type === $reactionType) {
            $existingReaction->delete();
            $message = 'Đã gỡ cảm xúc.';
            $activeReaction = null;
        } else {
            PostReaction::query()->updateOrCreate(
                [
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                ],
                [
                    'reaction_type' => $reactionType,
                ]
            );
            $message = 'Đã cập nhật cảm xúc.';
            $activeReaction = $reactionType;

            $this->createPostNotification($post, $user->id, 'like_post');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'post_id' => $post->id,
                'active_reaction' => $activeReaction,
                'counts' => $this->buildPostCounts($post),
            ]);
        }

        return back()->with('success', $message);
    }

    public function storeComment(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:post_comments,id'],
        ]);

        $user = $this->resolveInteractionUser();

        $comment = PostComment::query()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $this->createPostNotification($post, $user->id, 'comment_post');

        if ($request->expectsJson() || $request->ajax()) {
            $freshComment = PostComment::query()
                ->with('user')
                ->find($comment->id);

            return response()->json([
                'ok' => true,
                'message' => 'Đã đăng bình luận.',
                'post_id' => $post->id,
                'comment' => [
                    'id' => $freshComment ? $freshComment->id : $comment->id,
                    'user_name' => optional(optional($freshComment)->user)->name ?? 'Người dùng',
                    'content' => $freshComment ? $freshComment->content : $validated['content'],
                    'created_at' => optional(optional($freshComment)->created_at)->toIso8601String(),
                    'parent_id' => $freshComment ? $freshComment->parent_id : null,
                ],
                'counts' => $this->buildPostCounts($post),
            ]);
        }

        return back()->with('success', 'Đã đăng bình luận.');
    }

    public function updateComment(Request $request, PostComment $comment)
    {
        $this->authorizeCommentAction($comment);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $comment->update([
            'content' => $validated['content'],
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            $freshComment = PostComment::query()->with('user')->find($comment->id);

            return response()->json([
                'ok' => true,
                'message' => 'Đã cập nhật bình luận.',
                'comment' => [
                    'id' => $freshComment ? $freshComment->id : $comment->id,
                    'user_name' => optional(optional($freshComment)->user)->name ?? 'Người dùng',
                    'content' => $freshComment ? $freshComment->content : $validated['content'],
                    'updated_at' => optional(optional($freshComment)->updated_at)->toIso8601String(),
                ],
            ]);
        }

        return back()->with('success', 'Đã cập nhật bình luận.');
    }

    public function destroyComment(Request $request, PostComment $comment)
    {
        $this->authorizeCommentAction($comment);

        $comment->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Đã xóa bình luận.',
                'comment_id' => $comment->id,
            ]);
        }

        return back()->with('success', 'Đã xóa bình luận.');
    }

    public function share(Request $request, Post $post)
    {
        $user = $this->resolveInteractionUser();

        Post::query()->create([
            'user_id' => $user->id,
            'content' => $post->content,
            'media_path' => $post->media_path,
            'media_type' => $post->media_type,
            'image_url' => $post->image_url,
            'post_type' => $post->post_type,
            'text_color' => $post->text_color,
            'font_family' => $post->font_family,
            'shared_from' => $post->id,
            'created_at' => now(),
        ]);

        $this->createPostNotification($post, $user->id, 'share_post');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Đã chia sẻ bài viết.',
                'post_id' => $post->id,
                'counts' => $this->buildPostCounts($post),
            ]);
        }

        return back()->with('success', 'Đã chia sẻ bài viết.');
    }

    private function buildPostCounts(Post $post): array
    {
        $freshPost = Post::query()
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
            ->find($post->id);

        if (!$freshPost) {
            return [
                'total_reactions' => 0,
                'reaction_counts' => [
                    'like' => 0,
                    'love' => 0,
                    'haha' => 0,
                    'wow' => 0,
                    'sad' => 0,
                    'angry' => 0,
                ],
                'comments_count' => 0,
                'shares_count' => 0,
            ];
        }

        return [
            'total_reactions' => (int) (collect(['like_count', 'love_count', 'haha_count', 'wow_count', 'sad_count', 'angry_count'])
                ->sum(function ($key) use ($freshPost) {
                    return (int) ($freshPost->{$key} ?? 0);
                })),
            'reaction_counts' => [
                'like' => (int) ($freshPost->like_count ?? 0),
                'love' => (int) ($freshPost->love_count ?? 0),
                'haha' => (int) ($freshPost->haha_count ?? 0),
                'wow' => (int) ($freshPost->wow_count ?? 0),
                'sad' => (int) ($freshPost->sad_count ?? 0),
                'angry' => (int) ($freshPost->angry_count ?? 0),
            ],
            'comments_count' => (int) ($freshPost->comments_count ?? 0),
            'shares_count' => (int) ($freshPost->shares_count ?? 0),
        ];
    }

    private function authorizeCommentAction(PostComment $comment): void
    {
        $user = $this->resolveInteractionUser();
        if ($comment->user_id !== $user->id) {
            abort(403, 'Không có quyền thực hiện hành động này.');
        }
    }

    private function resolveInteractionUser(): User
    {
        if (auth()->check()) {
            $authenticatedUser = auth()->user();
            if ($authenticatedUser && $authenticatedUser->getKey() !== null) {
                return $authenticatedUser;
            }
        }

        abort(401, 'Bạn cần đăng nhập để thực hiện thao tác này.');
    }

    private function createPostNotification(Post $post, int $senderId, string $type): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        $receiverId = (int) $post->user_id;
        if ($receiverId <= 0 || $receiverId === $senderId) {
            return;
        }

        Notification::query()->create([
            'receiver_id' => $receiverId,
            'sender_id' => $senderId,
            'type' => $type,
            'is_read' => false,
            'post_id' => (int) $post->id,
        ]);
    }
}
