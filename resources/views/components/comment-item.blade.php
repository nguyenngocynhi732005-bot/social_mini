@php
    $replies = $comment->replies;
    $isOwner = $currentUser && $currentUser->id === $comment->user_id;
@endphp

<div class="bg-light rounded-3 p-2 small comment-card" data-comment-id="{{ $comment->id }}">
    <div class="d-flex gap-2">
        <div class="flex-grow-1 min-width-0">
            <div class="fw-bold comment-author">{{ optional($comment->user)->name ?? 'Người dùng' }}</div>
            <div class="comment-content mt-1">{{ $comment->content }}</div>
            <div class="comment-meta text-muted mt-1 d-flex gap-2">
                <span class="comment-time">{{ optional($comment->created_at)->diffForHumans() }}</span>
            </div>
        </div>
    </div>
    
    <div class="d-flex gap-2 mt-2 flex-wrap comment-actions">
        <button class="comment-action-btn comment-reply-btn" 
                data-comment-id="{{ $comment->id }}" 
                data-post-id="{{ $postId }}">
            Trả lời
        </button>
        
        @if($isOwner)
            <button class="comment-action-btn comment-edit-btn" 
                    data-comment-id="{{ $comment->id }}">
                Sửa
            </button>
            <button class="comment-action-btn comment-delete-btn" 
                    data-comment-id="{{ $comment->id }}">
                Xóa
            </button>
        @endif
    </div>

    @if($replies && $replies->count() > 0)
        <div class="ps-2 ps-md-3 mt-3 border-start border-2 border-secondary-subtle" style="margin-left: 8px;">
            @foreach($replies as $reply)
                @include('components.comment-item', ['comment' => $reply, 'postId' => $postId, 'currentUser' => $currentUser])
            @endforeach
        </div>
    @endif
</div>
