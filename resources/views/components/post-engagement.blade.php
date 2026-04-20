@php
    $reactionMap = [
        'like' => ['label' => 'Thích', 'emoji' => '👍'],
        'love' => ['label' => 'Yêu thích', 'emoji' => '❤️'],
        'haha' => ['label' => 'Haha', 'emoji' => '😆'],
        'wow' => ['label' => 'Wow', 'emoji' => '😮'],
        'sad' => ['label' => 'Buồn', 'emoji' => '😢'],
        'angry' => ['label' => 'Phẫn nộ', 'emoji' => '😡'],
    ];
    $reactionCounts = [
        'like' => (int) ($post->like_count ?? 0),
        'love' => (int) ($post->love_count ?? 0),
        'haha' => (int) ($post->haha_count ?? 0),
        'wow' => (int) ($post->wow_count ?? 0),
        'sad' => (int) ($post->sad_count ?? 0),
        'angry' => (int) ($post->angry_count ?? 0),
    ];
    $totalReactions = array_sum($reactionCounts);
    $comments = $post->comments ?? collect();
    $commentsCount = (int) ($post->comments_count ?? $comments->count());
    $sharesCount = (int) ($post->shares_count ?? 0);
    
    // Kiểm tra reaction của user hiện tại
    $currentUserReaction = null;
    $currentUser = auth()->user();
    if ($currentUser) {
        $userReaction = $post->reactions()
            ->where('user_id', $currentUser->id)
            ->first();
        $currentUserReaction = $userReaction ? $userReaction->reaction_type : null;
    }
@endphp

<div class="mt-3 pt-2 border-top post-engagement" data-post-engagement="{{ $post->id }}">
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-2">
        <div class="small text-muted">
            <span data-post-total-reactions="{{ $post->id }}">{{ $totalReactions }}</span> cảm xúc ·
            <span data-post-comments-count="{{ $post->id }}">{{ $commentsCount }}</span> bình luận ·
            <span data-post-shares-count="{{ $post->id }}">{{ $sharesCount }}</span> chia sẻ
        </div>
        @if($totalReactions > 0)
            <div class="small text-muted" data-post-reaction-summary="{{ $post->id }}">
                @foreach($reactionMap as $type => $meta)
                    @if(($reactionCounts[$type] ?? 0) > 0)
                        <span class="me-2">{{ $meta['emoji'] }} {{ $reactionCounts[$type] }}</span>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <div class="d-flex align-items-stretch gap-2 mb-3 post-action-row">
        <div class="position-relative post-like-wrap">
            <form action="{{ route('post.reactions.store', $post->id) }}" method="POST" class="h-100 post-ajax-form" data-post-ajax-form="reaction" data-post-id="{{ $post->id }}">
                @csrf
                @php
                    $defaultReaction = $currentUserReaction ?? 'like';
                    $reactionMeta = $currentUserReaction && isset($reactionMap[$currentUserReaction]) 
                        ? $reactionMap[$currentUserReaction] 
                        : ['label' => 'Thích', 'emoji' => '👍'];
                @endphp
                <input type="hidden" name="reaction_type" value="{{ $defaultReaction }}">
                <button type="submit" class="btn btn-light border rounded-pill w-100 h-100 fw-semibold post-like-btn" data-post-like-button="{{ $post->id }}" data-default-label="Thích" data-default-emoji="👍" @if($currentUserReaction) style="background-color: #e7f3ff; color: #0066cc;" @endif>
                    <span class="me-1">{{ $reactionMeta['emoji'] }}</span>{{ $reactionMeta['label'] }}
                </button>
            </form>

            <div class="post-reaction-picker shadow-sm bg-white border rounded-pill px-2 py-2 d-flex align-items-center gap-1">
                @foreach($reactionMap as $type => $meta)
                    <form action="{{ route('post.reactions.store', $post->id) }}" method="POST" class="d-inline-flex post-ajax-form" data-post-ajax-form="reaction" data-post-id="{{ $post->id }}">
                        @csrf
                        <input type="hidden" name="reaction_type" value="{{ $type }}">
                        <button type="submit" class="btn btn-light btn-sm rounded-pill px-2 py-1 post-reaction-btn" title="{{ $meta['label'] }}" data-reaction-type="{{ $type }}" data-reaction-label="{{ $meta['label'] }}" data-reaction-emoji="{{ $meta['emoji'] }}">
                            <span class="d-block" style="font-size: 1.05rem; line-height: 1;">{{ $meta['emoji'] }}</span>
                            <span class="d-block small lh-1">{{ $meta['label'] }}</span>
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        <button type="button"
                class="btn btn-light border rounded-pill fw-semibold post-comment-toggle"
                data-post-comment-toggle="{{ $post->id }}"
                aria-expanded="false">
            <i class="fas fa-comment-dots me-1 text-success"></i> Bình luận
        </button>

        <form action="{{ route('post.share', $post->id) }}" method="POST" class="d-inline-flex flex-shrink-0 post-ajax-form" data-post-ajax-form="share" data-post-id="{{ $post->id }}">
            @csrf
            <button type="submit" class="btn btn-light border rounded-pill fw-semibold">
                <i class="fas fa-share me-1 text-info"></i> Share
            </button>
        </form>
    </div>

    @php
        $sharedFrom = $post->sharedFromPost ?? null;
    @endphp

    @if($sharedFrom)
        <div class="alert alert-light border small py-2 mb-3">
            Chia sẻ từ bài viết gốc của {{ optional($sharedFrom->user)->name ?? 'người dùng' }}
        </div>
    @endif

    <div id="postCommentPanel-{{ $post->id }}" class="post-comment-panel d-none mb-3">
        <div class="d-grid gap-2 mb-3 post-comment-list" data-post-comment-list="{{ $post->id }}">
            @php
                $topLevelComments = $comments->where('parent_id', null)->sortByDesc('id');
            @endphp
            @if($commentsCount > 0)
                @foreach($topLevelComments as $comment)
                    @include('components.comment-item', ['comment' => $comment, 'postId' => $post->id, 'currentUser' => $currentUser])
                @endforeach
            @endif
        </div>

        <form action="{{ route('post.comments.store', $post->id) }}" method="POST" class="mb-0 post-ajax-form" data-post-ajax-form="comment" data-post-id="{{ $post->id }}">
            @csrf
            <div class="input-group">
                <input type="hidden" name="parent_id" value="">
                <input type="text" name="content" class="form-control" placeholder="Viết bình luận..." maxlength="2000" required>
                <button type="submit" class="btn btn-primary">Gửi</button>
            </div>
        </form>
    </div>

    @if($commentsCount > 0)
        <div class="d-grid gap-2 mb-3 post-comment-preview" data-post-comment-preview="{{ $post->id }}">
            @php
                $previewComments = $comments->where('parent_id', null)->sortByDesc('id')->take(2);
            @endphp
            @if($commentsCount <= 1)
                @foreach($previewComments as $comment)
                    @include('components.comment-item', ['comment' => $comment, 'postId' => $post->id, 'currentUser' => $currentUser])
                @endforeach
            @else
                <button type="button"
                        class="btn btn-link btn-sm text-decoration-none text-start px-0 post-comment-toggle post-comment-toggle-show"
                        data-post-comment-toggle="{{ $post->id }}"
                        aria-expanded="false">
                    Xem thêm bình luận
                </button>
            @endif
        </div>
    @endif
</div>

@once
    @push('styles')
        <style>
            .post-action-row {
                gap: .5rem;
            }

            .post-like-wrap {
                min-width: 0;
                flex: 1;
            }

            .post-like-wrap form {
                height: 100%;
                display: flex;
            }

            .post-like-btn,
            .post-comment-toggle {
                min-height: 42px;
                box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
                flex: 1;
            }

            .post-action-row > button,
            .post-action-row > form {
                flex: 1;
            }

            .post-action-row > form > button {
                width: 100%;
            }

            .post-like-wrap:hover .post-reaction-picker,
            .post-like-wrap:focus-within .post-reaction-picker,
            .post-reaction-picker:hover {
                opacity: 1;
                pointer-events: auto;
                transform: translateY(0) scale(1);
            }

            .post-reaction-picker {
                position: absolute;
                left: 0;
                bottom: calc(100% + 5px);
                z-index: 20;
                opacity: 0;
                pointer-events: none;
                transform: translateY(8px) scale(0.98);
                transition: opacity .15s ease, transform .15s ease;
                flex-wrap: nowrap;
                width: max-content;
                max-width: min(100vw - 2rem, 540px);
                overflow-x: auto;
            }

            .post-reaction-btn {
                min-width: 64px;
                line-height: 1;
            }

            .post-comment-preview {
                margin-top: .25rem;
            }

            .post-comment-panel {
                border: 1px solid #e9ecef;
                border-radius: 16px;
                padding: 12px;
                background: #fff;
            }

            .comment-card {
                position: relative;
                transition: background-color .2s ease;
                border-radius: 8px !important;
                padding: 10px !important;
                margin-bottom: 8px;
            }

            .comment-card:hover {
                background-color: #f8f9fa !important;
            }

            .comment-author {
                font-size: 0.9rem;
                margin-bottom: 2px;
            }

            .comment-content {
                font-size: 0.9rem;
                word-break: break-word;
                line-height: 1.4;
                color: #333;
            }

            .comment-meta {
                font-size: 0.75rem !important;
            }

            .comment-time {
                color: #999;
            }

            .comment-actions {
                margin-top: 6px;
            }

            .comment-action-btn {
                font-size: 0.75rem !important;
                color: #0066cc;
                padding: 0 !important;
                text-decoration: none;
                border: none;
                background: none;
                cursor: pointer;
                transition: opacity .15s ease;
            }

            .comment-action-btn:hover {
                opacity: 0.7;
                text-decoration: underline;
            }

            .comment-delete-btn {
                color: #dc3545 !important;
            }

            .comment-delete-btn:hover {
                opacity: 0.7;
            }

            .ps-2 {
                padding-left: 0.5rem !important;
            }

            .ps-md-3 {
                padding-left: 1rem !important;
            }

            .border-2 {
                border-width: 2px !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function csrfTokenFromForm(form) {
                    const tokenInput = form.querySelector('input[name="_token"]');
                    return tokenInput ? tokenInput.value : '';
                }

                function getCsrfToken() {
                    // Cơ 1: Lấy từ meta tag
                    const metaToken = document.querySelector('meta[name="csrf-token"]');
                    if (metaToken && metaToken.content) {
                        return metaToken.content;
                    }
                    
                    // Cơ 2: Lấy từ form đầu tiên có _token
                    const formWithToken = document.querySelector('form input[name="_token"]');
                    if (formWithToken) {
                        return formWithToken.value;
                    }
                    
                    return '';
                }

                function updateCounts(postId, counts) {
                    if (!counts) {
                        return;
                    }

                    const engagement = document.querySelector('[data-post-engagement="' + postId + '"]');
                    const totalReactions = engagement ? engagement.querySelector('[data-post-total-reactions="' + postId + '"]') : null;
                    const commentsCount = engagement ? engagement.querySelector('[data-post-comments-count="' + postId + '"]') : null;
                    const sharesCount = engagement ? engagement.querySelector('[data-post-shares-count="' + postId + '"]') : null;
                    const summary = engagement ? engagement.querySelector('[data-post-reaction-summary="' + postId + '"]') : null;

                    if (totalReactions) {
                        totalReactions.textContent = counts.total_reactions ?? 0;
                    }
                    if (commentsCount) {
                        commentsCount.textContent = counts.comments_count ?? 0;
                    }
                    if (sharesCount) {
                        sharesCount.textContent = counts.shares_count ?? 0;
                    }

                    if (summary) {
                        const reactionCounts = counts.reaction_counts || {};
                        const reactionLabels = {
                            like: '👍',
                            love: '❤️',
                            haha: '😆',
                            wow: '😮',
                            sad: '😢',
                            angry: '😡'
                        };

                        summary.innerHTML = '';
                        Object.keys(reactionLabels).forEach(function (type) {
                            const count = parseInt(reactionCounts[type] || 0, 10);
                            if (count > 0) {
                                const span = document.createElement('span');
                                span.className = 'me-2';
                                span.textContent = reactionLabels[type] + ' ' + count;
                                summary.appendChild(span);
                            }
                        });
                    }
                }

                function setLikeButtonState(postId, activeReaction) {
                    const likeButton = document.querySelector('[data-post-like-button="' + postId + '"]');
                    if (!likeButton) {
                        return;
                    }

                    const reactionMeta = {
                        like: { label: 'Thích', emoji: '👍' },
                        love: { label: 'Yêu thích', emoji: '❤️' },
                        haha: { label: 'Haha', emoji: '😆' },
                        wow: { label: 'Wow', emoji: '😮' },
                        sad: { label: 'Buồn', emoji: '😢' },
                        angry: { label: 'Phẫn nộ', emoji: '😡' },
                    };

                    const meta = activeReaction && reactionMeta[activeReaction] ? reactionMeta[activeReaction] : { label: likeButton.dataset.defaultLabel || 'Thích', emoji: likeButton.dataset.defaultEmoji || '👍' };
                    likeButton.innerHTML = '<span class="me-1">' + meta.emoji + '</span>' + meta.label;
                    likeButton.classList.toggle('btn-primary', !!activeReaction || activeReaction === 'like');
                    likeButton.classList.toggle('btn-light', !(activeReaction || activeReaction === 'like'));
                }

                function appendComment(postId, comment) {
                    if (!comment) {
                        return;
                    }

                    const panel = document.getElementById('postCommentPanel-' + postId);
                    const preview = document.querySelector('[data-post-comment-preview="' + postId + '"]');
                    const listHost = panel ? panel.querySelector('[data-post-comment-list="' + postId + '"]') : null;

                    if (!panel && !preview) {
                        return;
                    }

                    // Tạo comment card với đầy đủ nút
                    const commentCard = document.createElement('div');
                    commentCard.className = 'bg-light rounded-3 p-2 small comment-card';
                    commentCard.setAttribute('data-comment-id', comment.id);
                    
                    commentCard.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="fw-bold comment-author">${comment.user_name || 'Người dùng'}</div>
                                <div class="comment-content mt-1">${comment.content || ''}</div>
                                <div class="comment-meta text-muted mt-1">
                                    <span class="comment-time">Vừa xong</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-2 flex-wrap comment-actions">
                            <button class="comment-action-btn comment-reply-btn" 
                                    data-comment-id="${comment.id}" 
                                    data-post-id="${postId}">
                                Trả lời
                            </button>
                            <button class="comment-action-btn comment-edit-btn" 
                                    data-comment-id="${comment.id}">
                                Sửa
                            </button>
                            <button class="comment-action-btn comment-delete-btn" 
                                    data-comment-id="${comment.id}">
                                Xóa
                            </button>
                        </div>
                    `;

                    if (listHost) {
                        listHost.prepend(commentCard);
                    }

                    if (preview) {
                        const previewCard = commentCard.cloneNode(true);
                        preview.prepend(previewCard);
                    }

                    if (panel) {
                        panel.classList.remove('d-none');
                        const toggleBtn = document.querySelector('[data-post-comment-toggle="' + postId + '"]');
                        if (toggleBtn) {
                            toggleBtn.setAttribute('aria-expanded', 'true');
                        }
                    }
                }

                async function submitAjaxForm(form) {
                    const postId = form.getAttribute('data-post-id');
                    const formType = form.getAttribute('data-post-ajax-form');
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfTokenFromForm(form)
                        },
                        body: new FormData(form)
                    });

                    const data = await response.json().catch(function () { return null; });
                    if (!response.ok || !data || data.ok !== true) {
                        throw new Error((data && data.message) ? data.message : 'Không thể xử lý thao tác này.');
                    }

                    updateCounts(postId, data.counts);

                    if (formType === 'reaction') {
                        setLikeButtonState(postId, data.active_reaction);
                    }

                    if (formType === 'comment') {
                        const input = form.querySelector('input[name="content"]');
                        if (input) {
                            input.value = '';
                        }
                        const panel = document.getElementById('postCommentPanel-' + postId);
                        if (panel) {
                            panel.classList.remove('d-none');
                        }
                        appendComment(postId, data.comment);
                    }

                    if (formType === 'share') {
                        const shareButton = form.querySelector('button[type="submit"]');
                        if (shareButton) {
                            shareButton.classList.add('btn-success');
                            shareButton.classList.remove('btn-light');
                        }
                    }

                    return data;
                }

                document.querySelectorAll('[data-post-comment-toggle]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const postId = button.getAttribute('data-post-comment-toggle');
                        const panel = document.getElementById('postCommentPanel-' + postId);
                        const preview = document.querySelector('[data-post-comment-preview="' + postId + '"]');

                        if (!panel) {
                            return;
                        }

                        const isHidden = panel.classList.contains('d-none');
                        
                        // Toggle panel
                        panel.classList.toggle('d-none');
                        button.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                        
                        // Hide/show preview when opening/closing panel
                        if (preview) {
                            if (isHidden) {
                                // Panel is being opened -> hide preview, show all comments in panel
                                preview.style.display = 'none';
                                button.textContent = 'Ẩn bình luận';
                            } else {
                                // Panel is being closed -> show preview, hide panel
                                preview.style.display = '';
                                button.textContent = 'Xem thêm bình luận';
                            }
                        }
                    });
                });

                // Handle comment reply
                document.addEventListener('click', function (e) {
                    if (e.target.closest('.comment-reply-btn')) {
                        const btn = e.target.closest('.comment-reply-btn');
                        const commentId = btn.getAttribute('data-comment-id');
                        const postId = btn.getAttribute('data-post-id');
                        const panel = document.getElementById('postCommentPanel-' + postId);
                        const form = panel ? panel.querySelector('.post-ajax-form[data-post-ajax-form="comment"]') : null;
                        const parentIdInput = form ? form.querySelector('input[name="parent_id"]') : null;

                        if (parentIdInput) {
                            parentIdInput.value = commentId;
                            const input = form.querySelector('input[name="content"]');
                            if (input) {
                                input.focus();
                                input.placeholder = 'Viết trả lời...';
                            }
                        }
                    }
                });

                // Handle comment delete
                document.addEventListener('click', function (e) {
                    if (e.target.closest('.comment-delete-btn')) {
                        const btn = e.target.closest('.comment-delete-btn');
                        const commentId = btn.getAttribute('data-comment-id');

                        if (!confirm('Xóa bình luận này?')) {
                            return;
                        }

                        fetch('/comments/' + commentId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json',
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.ok) {
                                const commentCard = document.querySelector('[data-comment-id="' + commentId + '"]');
                                if (commentCard) {
                                    commentCard.remove();
                                }
                            } else {
                                alert(data.message || 'Không thể xóa bình luận.');
                            }
                        })
                        .catch(err => alert('Lỗi: ' + err.message));
                    }
                });

                // Handle comment edit
                document.addEventListener('click', function (e) {
                    if (e.target.closest('.comment-edit-btn')) {
                        const btn = e.target.closest('.comment-edit-btn');
                        const commentId = btn.getAttribute('data-comment-id');
                        const commentCard = document.querySelector('[data-comment-id="' + commentId + '"]');

                        if (!commentCard) {
                            return;
                        }

                        const contentDiv = commentCard.querySelector('.comment-content');
                        const currentContent = contentDiv.textContent;

                        const editForm = document.createElement('div');
                        editForm.className = 'mb-0';
                        
                        const inputGroup = document.createElement('div');
                        inputGroup.className = 'input-group input-group-sm';
                        
                        const input = document.createElement('input');
                        input.type = 'text';
                        input.className = 'form-control';
                        input.name = 'content';
                        input.value = currentContent;
                        input.maxLength = '2000';
                        input.required = true;
                        
                        const saveBtn = document.createElement('button');
                        saveBtn.type = 'submit';
                        saveBtn.className = 'btn btn-primary btn-sm';
                        saveBtn.textContent = 'Lưu';
                        
                        const cancelBtn = document.createElement('button');
                        cancelBtn.type = 'button';
                        cancelBtn.className = 'btn btn-secondary btn-sm cancel-edit';
                        cancelBtn.textContent = 'Hủy';
                        
                        inputGroup.appendChild(input);
                        inputGroup.appendChild(saveBtn);
                        inputGroup.appendChild(cancelBtn);
                        editForm.appendChild(inputGroup);

                        contentDiv.replaceWith(editForm);

                        saveBtn.addEventListener('click', async function (evt) {
                            evt.preventDefault();
                            const newContent = input.value;
                            
                            try {
                                const res = await fetch('/comments/' + commentId, {
                                    method: 'PUT',
                                    headers: {
                                        'X-CSRF-TOKEN': getCsrfToken(),
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({ content: newContent })
                                });
                                const data = await res.json();
                                if (data.ok) {
                                    const newContentDiv = document.createElement('div');
                                    newContentDiv.className = 'comment-content mt-1';
                                    newContentDiv.textContent = data.comment.content;
                                    editForm.replaceWith(newContentDiv);
                                } else {
                                    alert(data.message || 'Không thể cập nhật bình luận.');
                                }
                            } catch (err) {
                                alert('Lỗi: ' + err.message);
                            }
                        });

                        cancelBtn.addEventListener('click', function () {
                            const newContentDiv = document.createElement('div');
                            newContentDiv.className = 'comment-content mt-1';
                            newContentDiv.textContent = currentContent;
                            editForm.replaceWith(newContentDiv);
                        });
                    }
                });

                document.querySelectorAll('.post-ajax-form').forEach(function (form) {
                    form.addEventListener('submit', async function (event) {
                        event.preventDefault();

                        const submitButton = form.querySelector('button[type="submit"]');
                        const formType = form.getAttribute('data-post-ajax-form');
                        if (submitButton) {
                            submitButton.disabled = true;
                        }

                        try {
                            await submitAjaxForm(form);
                        } catch (error) {
                            alert(error && error.message ? error.message : 'Không thể xử lý thao tác này.');
                        } finally {
                            if (submitButton) {
                                submitButton.disabled = false;
                            }
                        }
                    });
                });
            });
        </script>
    @endpush
@endonce
