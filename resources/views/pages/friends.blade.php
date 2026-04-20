@extends('layouts.app')

@section('content')
@php
    $displayName = function ($user) {
        $first = $user->First_name ?? $user->first_name ?? null;
        $last = $user->Last_name ?? $user->last_name ?? null;
        $fullName = trim(implode(' ', array_filter([$first, $last])));

        if ($fullName !== '') {
            return $fullName;
        }

        return trim($user->name ?? $user->Name ?? ('User #' . ($user->id ?? '')));
    };

    $relationStatusByUser = $relationStatusByUser ?? [];
    $isPending = function ($userId) use ($relationStatusByUser) {
        return ($relationStatusByUser[$userId] ?? null) === 'pending';
    };

    $pendingCount = isset($pendingRequests) ? $pendingRequests->count() : 0;
    $friendCount = isset($friends) ? $friends->count() : 0;
    $suggestedCount = isset($suggestedUsers) ? $suggestedUsers->count() : 0;
    // Đã chuyển biến đếm người bị chặn lên đúng vị trí này
    $blockedCount = isset($blockedUsers) ? $blockedUsers->count() : 0; 
@endphp

<style>
    /* CSS cho khối giao diện chính */
    .friends-shell {
        width: 70vw; /* Chiếm 70% chiều rộng màn hình */
        position: relative;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(180deg, #d9edf9 0%, #eaf5fb 100%);
        border-radius: 18px;
        padding: 16px;
    }

    .friends-panel {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(125, 143, 160, 0.25);
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(42, 64, 82, 0.08);
        height: 100%;
    }

    .friends-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .friends-panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        font-size: 1.1rem;
        font-weight: 800;
        color: #222;
    }

    .count-badge {
        min-width: 22px;
        height: 22px;
        padding: 0 7px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        line-height: 1;
    }

    .count-badge-danger {
        background: #dc3545;
        color: #fff;
    }

    .count-badge-muted {
        background: #6c757d;
        color: #fff;
    }

    .request-card,
    .suggestion-card {
        background: #fff;
        border: 1px solid rgba(125, 143, 160, 0.2);
        border-radius: 16px;
        box-shadow: 0 6px 16px rgba(42, 64, 82, 0.06);
    }

    .friend-empty {
        min-height: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #7f8a94;
    }

    .friend-empty-icon {
        font-size: 3rem;
        color: #6c7780;
        margin-bottom: 10px;
    }

    .avatar-circle {
        object-fit: cover;
        border-radius: 999px;
        flex-shrink: 0;
    }

    .btn-pill-soft {
        border-radius: 999px;
        padding-left: 18px;
        padding-right: 18px;
    }

    .subtle-text {
        color: #7f8a94;
    }
    
    /* Ẩn thanh cuộn cho danh sách bạn bè */
    .custom-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .custom-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<div class="friends-shell">
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="friends-panel p-3 p-md-4">
                <div class="friends-panel-header">
                    <h3 class="friends-panel-title">
                        Lời mời kết bạn
                        <span class="count-badge count-badge-danger">{{ $pendingCount }}</span>
                    </h3>
                </div>

                @if($pendingCount > 0)
                    <div class="d-grid gap-3">
                        @foreach($pendingRequests as $requester)
                            <div class="request-card p-3 request-item">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($displayName($requester)) }}&background=random" alt="{{ $displayName($requester) }}" width="56" height="56" class="avatar-circle">
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fw-bold text-dark text-truncate">{{ $displayName($requester) }}</div>
                                        <div class="subtle-text small">Chờ phản hồi</div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-3">
                                    <button type="button" class="btn btn-primary flex-grow-1 btn-pill-soft respond-btn fw-semibold" data-id="{{ $requester->id }}" data-action="accept">Xác nhận</button>
                                    <button type="button" class="btn btn-outline-secondary flex-grow-1 btn-pill-soft respond-btn fw-semibold" data-id="{{ $requester->id }}" data-action="decline">Xóa</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="friend-empty">
                        <div>
                            <i class="fas fa-user-clock friend-empty-icon"></i>
                            <div class="fw-semibold text-dark">Không có lời mời mới</div>
                            <div class="small">Khi có người gửi lời mời, nó sẽ hiện ở đây.</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="friends-panel p-3 p-md-4">
                <div class="friends-panel-header">
                    <h3 class="friends-panel-title">
                        Tất cả bạn bè
                        <span class="count-badge count-badge-muted">{{ $friendCount }}</span>
                    </h3>
                </div>

                @if($friendCount > 0)
                    <div class="custom-scrollbar" style="max-height: 450px; overflow-y: auto; padding-right: 8px; padding-bottom: 120px;">
                        <div class="d-grid gap-2">
                            @foreach($friends as $friend)
                                <div class="suggestion-card p-2 d-flex align-items-center justify-content-between friend-item transition-all" style="border-radius: 12px;">
                                    <div class="d-flex align-items-center gap-3 min-w-0">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($displayName($friend)) }}&background=random" alt="{{ $displayName($friend) }}" width="48" height="48" class="avatar-circle shadow-sm">
                                        <div class="min-w-0">
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.95rem;">{{ $displayName($friend) }}</div>
                                            <div class="subtle-text text-truncate" style="font-size: 0.8rem;">Bạn bè</div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center gap-2">
                                        <button class="btn btn-light rounded-circle text-primary" style="width: 36px; height: 36px; padding: 0;" title="Nhắn tin">
                                            <i class="fab fa-facebook-messenger"></i>
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn btn-light rounded-circle" type="button" data-bs-toggle="dropdown" data-bs-boundary="window" aria-expanded="false" style="width: 36px; height: 36px; padding: 0;">
                                                <i class="fas fa-ellipsis-h text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 12px;">
                                                <li>
                                                    <a class="dropdown-item text-danger unfriend-btn py-2" href="#" data-id="{{ $friend->id }}">
                                                        <i class="fas fa-user-times me-2 width-15 text-center"></i> Hủy kết bạn
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item block-btn py-2" href="#" data-id="{{ $friend->id }}">
                                                        <i class="fas fa-ban me-2 text-muted width-15 text-center"></i> Chặn người dùng
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="friend-empty">
                        <div>
                            <i class="fas fa-user-friends friend-empty-icon"></i>
                            <div class="fw-semibold text-dark">Chưa có bạn bè nào</div>
                            <div class="small">Hãy tìm kiếm và kết nối với mọi người nhé!</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(isset($highlightUser) && $highlightUser)
        <div class="friends-panel p-3 p-md-4 mb-3">
            <div class="friends-panel-header mb-0">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($displayName($highlightUser)) }}&background=random" alt="{{ $displayName($highlightUser) }}" width="56" height="56" class="avatar-circle">
                    <div class="min-w-0">
                        <div class="fw-bold text-dark text-truncate">{{ $displayName($highlightUser) }}</div>
                        <div class="subtle-text small text-truncate">{{ $isPending($highlightUser->id) ? 'Đã gửi lời mời' : 'Người bạn có thể biết' }}</div>
                    </div>
                </div>

                @if($isPending($highlightUser->id))
                    <button type="button" class="btn btn-secondary btn-pill-soft cancel-request-btn" data-id="{{ $highlightUser->id }}">Hủy lời mời</button>
                @else
                    <button type="button" class="btn btn-primary btn-pill-soft add-friend-btn" data-id="{{ $highlightUser->id }}">Thêm bạn bè</button>
                @endif
            </div>
        </div>
    @endif

    @if($suggestedCount > 0)
        <div class="friends-panel p-3 p-md-4 mb-3">
            <div class="friends-panel-header">
                <h3 class="friends-panel-title">
                    Gợi ý kết bạn
                    <span class="count-badge count-badge-muted">{{ $suggestedCount }}</span>
                </h3>
            </div>

            <div class="row g-3">
                @foreach($suggestedUsers as $user)
                    <div class="col-12 col-md-6">
                        <div class="suggestion-card p-3 h-100 d-flex align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3 min-w-0">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($displayName($user)) }}&background=random" alt="{{ $displayName($user) }}" width="56" height="56" class="avatar-circle">
                                <div class="min-w-0">
                                    <div class="fw-semibold text-dark text-truncate">{{ $displayName($user) }}</div>
                                    <div class="subtle-text small text-truncate">{{ $isPending($user->id) ? 'Đã gửi lời mời' : 'Người bạn có thể biết' }}</div>
                                </div>
                            </div>

                            @if($isPending($user->id))
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-pill-soft cancel-request-btn" data-id="{{ $user->id }}">Hủy lời mời</button>
                            @else
                                <button type="button" class="btn btn-primary btn-sm btn-pill-soft add-friend-btn" data-id="{{ $user->id }}">Thêm bạn bè</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="friends-panel p-3 p-md-4">
        <div class="friends-panel-header">
            <h3 class="friends-panel-title">
                Đã chặn
                <span class="count-badge count-badge-danger">{{ $blockedCount }}</span>
            </h3>
        </div>

        @if($blockedCount > 0)
            <div class="custom-scrollbar" style="max-height: 350px; overflow-y: auto; padding-right: 8px;">
                <div class="row g-2">
                    @foreach($blockedUsers as $blocked)
                        <div class="col-12 col-md-6">
                            <div class="suggestion-card p-2 d-flex align-items-center justify-content-between blocked-item transition-all" style="border-radius: 12px; background: #fffcfc; border: 1px solid #ffeeba;">
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($displayName($blocked)) }}&background=random" width="48" height="48" class="avatar-circle shadow-sm">
                                    <div class="min-w-0">
                                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.95rem;">{{ $displayName($blocked) }}</div>
                                        <div class="subtle-text text-truncate text-danger" style="font-size: 0.8rem;">Đã chặn</div>
                                    </div>
                                </div>
                                
                                <button class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold unblock-btn" data-id="{{ $blocked->id }}">
                                    Bỏ chặn
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="friend-empty" style="min-height: 150px;">
                <div>
                    <i class="fas fa-user-shield friend-empty-icon" style="font-size: 2rem;"></i>
                    <div class="fw-semibold text-dark">Chưa chặn ai</div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
    const unfriendUrlTemplate = '{{ route('social.friends.unfriend', ['user' => '__USER__']) }}';
    const unblockUrlTemplate = '{{ route('social.friends.unblock', ['user' => '__USER__']) }}';

    const requestJson = async (url, method, body, fallbackMessage) => {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: body ? JSON.stringify(body) : undefined
        });

        const raw = await response.text();
        let data = {};

        try {
            data = raw ? JSON.parse(raw) : {};
        } catch (error) {
            data = { message: raw || fallbackMessage };
        }

        if (!response.ok) {
            throw new Error(data.message || fallbackMessage);
        }

        return data;
    };

    const setLoading = (button, text) => {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = text;
    };

    const restoreButton = (button) => {
        button.disabled = false;
        if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
        }
    };

    document.addEventListener('click', async function (event) {
        // XỬ LÝ HỦY KẾT BẠN & CHẶN
        const menuActionBtn = event.target.closest('.unfriend-btn, .block-btn');
        if (menuActionBtn) {
            event.preventDefault();
            const targetId = menuActionBtn.dataset.id;
            const isUnfriend = menuActionBtn.classList.contains('unfriend-btn');
            const friendItem = menuActionBtn.closest('.friend-item');

            if (!isUnfriend && !confirm('Bạn có chắc chắn muốn chặn người dùng này không? Người này sẽ không thể tìm thấy bạn nữa.')) {
                return;
            }

            try {
                if (isUnfriend) {
                    await requestJson(unfriendUrlTemplate.replace('__USER__', targetId), 'DELETE', null, 'Không thể hủy kết bạn.');
                } else {
                    await requestJson('{{ route('social.friends.block') }}', 'POST', { target_id: targetId }, 'Không thể chặn.');
                }
                
                friendItem.style.transition = "opacity 0.3s";
                friendItem.style.opacity = 0;
                setTimeout(() => friendItem.remove(), 300);
            } catch (error) {
                alert(error.message);
            }
            return;
        }

        // XỬ LÝ PHẢN HỒI LỜI MỜI (XÁC NHẬN / XÓA)
        const respondBtn = event.target.closest('.respond-btn');
        if (respondBtn) {
            event.preventDefault();
            const requesterId = respondBtn.dataset.id;
            const action = respondBtn.dataset.action;
            const requestItem = respondBtn.closest('.request-item');

            setLoading(respondBtn, 'Đang xử lý...');

            try {
                await requestJson('{{ route('social.friends.respond') }}', 'POST', {
                    requester_id: requesterId,
                    action: action
                }, 'Không thể phản hồi lời mời.');

                if (requestItem) {
                    requestItem.remove();
                }
            } catch (error) {
                alert(error.message || 'Không thể phản hồi lời mời.');
                restoreButton(respondBtn);
            }
            return;
        }

        // XỬ LÝ BỎ CHẶN (MỚI ĐƯỢC THÊM VÀO ĐÂY NÈ)
        const unblockBtn = event.target.closest('.unblock-btn');
        if (unblockBtn) {
            event.preventDefault();
            const targetId = unblockBtn.dataset.id;
            const blockedItem = unblockBtn.closest('.blocked-item');

            if (!confirm('Bạn có chắc chắn muốn bỏ chặn người này? Họ sẽ có thể thấy và kết bạn lại với bạn.')) {
                return;
            }

            setLoading(unblockBtn, 'Đang...');

            try {
                await requestJson(unblockUrlTemplate.replace('__USER__', targetId), 'DELETE', null, 'Không thể bỏ chặn.');
                
                blockedItem.style.transition = "opacity 0.3s, transform 0.3s";
                blockedItem.style.opacity = 0;
                blockedItem.style.transform = "translateX(20px)";
                setTimeout(() => {
                    blockedItem.remove();
                }, 300);
            } catch (error) {
                alert(error.message);
                restoreButton(unblockBtn);
            }
            return;
        }

        // XỬ LÝ GỬI / HỦY LỜI MỜI KẾT BẠN
        const toggleBtn = event.target.closest('.add-friend-btn, .cancel-request-btn');
        if (toggleBtn) {
            event.preventDefault();
            const targetId = toggleBtn.dataset.id;
            const isAdd = toggleBtn.classList.contains('add-friend-btn');
            const endpoint = isAdd ? '{{ route('social.friends.request') }}' : '{{ route('social.friends.cancel') }}';

            setLoading(toggleBtn, isAdd ? 'Đang gửi...' : 'Đang hủy...');

            try {
                await requestJson(
                    endpoint,
                    'POST',
                    { target_id: targetId },
                    isAdd ? 'Không thể gửi lời mời.' : 'Không thể hủy lời mời.'
                );

                if (isAdd) {
                    toggleBtn.classList.remove('btn-primary', 'add-friend-btn');
                    toggleBtn.classList.add('btn-outline-secondary', 'cancel-request-btn');
                    toggleBtn.textContent = 'Hủy lời mời';
                } else {
                    toggleBtn.classList.remove('btn-outline-secondary', 'cancel-request-btn');
                    toggleBtn.classList.add('btn-primary', 'add-friend-btn');
                    toggleBtn.textContent = 'Thêm bạn bè';
                }
                toggleBtn.disabled = false;
            } catch (error) {
                alert(error.message || (isAdd ? 'Không thể gửi lời mời.' : 'Không thể hủy lời mời.'));
                restoreButton(toggleBtn);
            }
        }
    });
});
</script>
@endsection