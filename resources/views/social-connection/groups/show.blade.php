@extends('layouts.app')

@section('content')
<style>
	.group-show-page {
		--group-radius: 14px;
		--group-shadow: 0 10px 26px rgba(15, 23, 42, 0.1);
		--group-border: #e9ecef;
		--group-muted: #6b7280;
		--group-bg-soft: #f8f9fb;
	}

	.group-show-page .group-header {
		position: relative;
		border-radius: 16px;
		overflow: hidden;
		border: 1px solid var(--group-border);
		box-shadow: var(--group-shadow);
		background: #fff;
	}

	.group-show-page .group-header-cover {
		position: relative;
		width: 100%;
		height: clamp(220px, 32vw, 340px);
		background: #dbe4ff;
	}

	.group-show-page .group-cover-actions {
		position: absolute;
		top: 1rem;
		right: 1rem;
		display: flex;
		gap: .5rem;
		z-index: 3;
	}

	.group-show-page .group-cover-action-btn,
	.group-show-page .group-avatar-action-btn {
		display: inline-flex;
		align-items: center;
		gap: .4rem;
		padding: .45rem .75rem;
		border: 0;
		border-radius: 999px;
		background: rgba(255, 255, 255, .88);
		color: #111827;
		font-size: .85rem;
		font-weight: 600;
		box-shadow: 0 6px 18px rgba(15, 23, 42, .16);
		backdrop-filter: blur(8px);
		cursor: pointer;
	}

	.group-show-page .group-cover-action-btn:hover,
	.group-show-page .group-avatar-action-btn:hover {
		background: #fff;
	}

	.group-show-page .group-header-cover img {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.group-show-page .group-header-overlay {
		position: absolute;
		inset: 0;
		background: linear-gradient(to top, rgba(2, 6, 23, 0.6), rgba(2, 6, 23, 0.1) 55%, rgba(2, 6, 23, 0));
	}

	.group-show-page .group-header-content {
		position: absolute;
		left: 1.25rem;
		right: 1.25rem;
		bottom: 1rem;
		display: flex;
		align-items: flex-end;
		justify-content: space-between;
		gap: 1rem;
		z-index: 2;
	}

	.group-show-page .group-header-main {
		display: flex;
		align-items: flex-end;
		gap: .9rem;
		min-width: 0;
	}

	.group-show-page .group-avatar {
		width: 88px;
		height: 88px;
		border-radius: 16px;
		border: 3px solid #fff;
		object-fit: cover;
		box-shadow: 0 6px 18px rgba(0, 0, 0, .18);
		flex-shrink: 0;
		background: #fff;
	}

	.group-show-page .group-avatar-wrap {
		position: relative;
		flex-shrink: 0;
	}

	.group-show-page .group-avatar-actions {
		position: absolute;
		left: 50%;
		bottom: -0.55rem;
		transform: translateX(-50%);
		z-index: 3;
	}

	.group-show-page .group-avatar-action-btn {
		padding: .35rem .65rem;
		font-size: .78rem;
	}

	.group-show-page .group-title {
		margin: 0;
		color: #fff;
		font-size: clamp(1.15rem, 2.1vw, 1.8rem);
		font-weight: 800;
		text-shadow: 0 2px 10px rgba(0, 0, 0, .35);
		line-height: 1.25;
		word-break: break-word;
	}

	.group-show-page .group-join-btn {
		border-radius: 12px;
		padding: .55rem 1rem;
		font-weight: 600;
		white-space: nowrap;
	}

	.group-show-page .group-content-card {
		margin-top: 1rem;
		background: #fff;
		border: 1px solid var(--group-border);
		border-radius: var(--group-radius);
		box-shadow: var(--group-shadow);
		overflow: hidden;
	}

	.group-show-page .group-tabs.nav-tabs {
		border-bottom: 1px solid var(--group-border);
		padding: .65rem .85rem 0;
		gap: .25rem;
	}

	.group-show-page .group-tabs .nav-link {
		border: none;
		border-radius: 10px 10px 0 0;
		color: #374151;
		font-weight: 600;
		padding: .65rem 1rem;
	}

	.group-show-page .group-tabs .nav-link.active {
		color: #0d6efd;
		background: #eef4ff;
		border-bottom: 2px solid #0d6efd;
	}

	.group-show-page .group-tab-pane {
		padding: 1rem;
	}

	.group-show-page .group-empty-state {
		margin: 0;
		padding: .9rem;
		border-radius: 12px;
		background: var(--group-bg-soft);
		color: var(--group-muted);
	}

	.group-show-page .group-members-list {
		display: flex;
		flex-direction: column;
		gap: .7rem;
	}

	.group-show-page .group-member-item {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: .75rem;
		padding: .7rem .8rem;
		border: 1px solid var(--group-border);
		border-radius: 12px;
		background: #fff;
	}

	.group-show-page .group-member-left {
		display: flex;
		align-items: center;
		gap: .75rem;
		min-width: 0;
	}

	.group-show-page .group-member-avatar {
		width: 44px;
		height: 44px;
		border-radius: 50%;
		object-fit: cover;
		flex-shrink: 0;
	}

	.group-show-page .group-member-name {
		margin: 0;
		font-weight: 600;
		color: #111827;
		line-height: 1.25;
	}

	.group-show-page .group-member-role {
		font-size: .82rem;
		font-weight: 600;
		color: #2563eb;
		background: #eef4ff;
		border-radius: 999px;
		padding: .32rem .7rem;
		text-transform: capitalize;
	}

	@media (max-width: 767.98px) {
		.group-show-page .group-header-content {
			left: .85rem;
			right: .85rem;
			bottom: .85rem;
			flex-direction: column;
			align-items: stretch;
		}

		.group-show-page .group-avatar {
			width: 72px;
			height: 72px;
			border-radius: 14px;
		}

		.group-show-page .group-cover-actions {
			top: .75rem;
			right: .75rem;
			flex-direction: column;
		}

		.group-show-page .group-join-btn {
			width: 100%;
		}

		.group-show-page .group-member-item {
			align-items: flex-start;
			flex-direction: column;
		}
	}
</style>

<div class="container py-3 py-md-4 group-show-page" id="group-show-page">
	<section class="group-header js-group-header" id="group-header" data-group-id="{{ $group->id }}">
		<div class="group-header-cover">
			@if(isset($isAdmin) && $isAdmin)
				<div class="group-cover-actions">
					<button type="button" class="group-cover-action-btn js-pick-group-cover-btn" data-target="#group-cover-input">
						<i class="fas fa-image"></i>
						Đổi bìa
					</button>
				</div>
				<input type="file" id="group-cover-input" class="d-none js-group-cover-input" accept="image/*" data-upload-field="cover_image" data-group-id="{{ $group->id }}">
			@endif
			<img
				src="{{ $group->cover_image ?? 'https://placehold.co/1400x600?text=Group+Cover' }}"
				alt="{{ $group->name }}"
				class="js-group-cover"
			>
			<div class="group-header-overlay"></div>
		</div>

		<div class="group-header-content">
			<div class="group-header-main">
				<div class="group-avatar-wrap">
					<img
						src="{{ $group->avatar_image ?? $group->cover_image ?? 'https://placehold.co/200x200?text=Group' }}"
						alt="{{ $group->name }}"
						class="group-avatar js-group-avatar"
					>
					@if(isset($isAdmin) && $isAdmin)
						<div class="group-avatar-actions">
							<button type="button" class="group-avatar-action-btn js-pick-group-avatar-btn" data-target="#group-avatar-input">
								<i class="fas fa-camera"></i>
								Đổi avatar
							</button>
						</div>
						<input type="file" id="group-avatar-input" class="d-none js-group-avatar-input" accept="image/*" data-upload-field="avatar_image" data-group-id="{{ $group->id }}">
					@endif
				</div>
				<h1 class="group-title js-group-name">{{ $group->name }}</h1>
			</div>

			@if($isJoined)
				<button
					type="button"
					class="btn btn-success group-join-btn js-group-joined-btn"
					data-group-id="{{ $group->id }}"
					disabled
				>
					Đã tham gia
				</button>
			@else
				<button
					type="button"
					class="btn btn-primary group-join-btn js-group-join-btn"
					data-group-id="{{ $group->id }}"
				>
					Tham gia nhóm
				</button>
			@endif
		</div>
	</section>

	<section class="group-content-card">
		<ul class="nav nav-tabs group-tabs" id="groupDetailTabs" role="tablist">
			<li class="nav-item" role="presentation">
				<button
					class="nav-link active"
					id="discussion-tab"
					data-bs-toggle="tab"
					data-bs-target="#discussion-pane"
					type="button"
					role="tab"
					aria-controls="discussion-pane"
					aria-selected="true"
				>
					Thảo luận
				</button>
			</li>
			<li class="nav-item" role="presentation">
				<button
					class="nav-link"
					id="members-tab"
					data-bs-toggle="tab"
					data-bs-target="#members-pane"
					type="button"
					role="tab"
					aria-controls="members-pane"
					aria-selected="false"
				>
					Thành viên
				</button>
			</li>
		</ul>

		<div class="tab-content" id="groupDetailTabsContent">
			<div
				class="tab-pane fade show active group-tab-pane"
				id="discussion-pane"
				role="tabpanel"
				aria-labelledby="discussion-tab"
				tabindex="0"
			>
				@if($isJoined)
					<div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; background: #fff;">
						<div class="card-body p-3">
							<form action="{{ route('social.groups.posts.store', $group->id) }}" method="POST" id="form-create-group-post" enctype="multipart/form-data">
    @csrf
    <textarea name="content" class="form-control mb-2" placeholder="Bạn đang nghĩ gì?"></textarea>
    
    <div class="d-flex justify-content-between align-items-center">
        <label for="post-image-input" style="cursor: pointer;" class="text-primary">
            <i class="fas fa-image me-1"></i> Ảnh/Video
        </label>
        <input type="file" name="image" id="post-image-input" class="d-none" accept="image/*">
        
        <button type="submit" class="btn btn-primary js-submit-post-btn">Đăng ngay</button>
    </div>
    <div id="post-image-preview" class="mt-2 d-none">
        <img src="" class="img-thumbnail" style="max-height: 150px;">
    </div>
</form>
						</div>
					</div>
				@endif

				<div id="group-posts-feed">
					@forelse($posts as $post)
						@php
							$postUser = $post->user;
							$postUserName = $postUser->Name ?? $postUser->name ?? $postUser->first_name ?? 'Thành viên';
						@endphp
						<div class="card border-0 shadow-sm mb-3" style="border-radius: 15px;">
							<div class="card-body">
								<div class="d-flex align-items-center mb-2">
									<img src="https://ui-avatars.com/api/?name={{ urlencode($postUserName ?: 'U') }}&background=random"
										class="rounded-circle me-2" width="38">
									<div>
										<h6 class="mb-0 fw-bold">{{ $postUserName }}</h6>
										<small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
									</div>
								</div>
								<p class="mb-0" style="white-space: pre-wrap;">{{ $post->content }}</p>
								@if(!empty($post->image_url))
									<img src="{{ asset($post->image_url) }}" class="img-fluid rounded shadow-sm mt-2" style="max-height: 400px; width: 100%; object-fit: cover;" alt="Ảnh bài viết">
								@endif
							</div>
						</div>
					@empty
						<div class="text-center py-5 bg-light rounded-4 js-empty-posts">
							<p class="text-muted mb-0">Chưa có bài viết nào. Hãy là người đầu tiên!</p>
						</div>
					@endforelse
				</div>
			</div>

		<div class="tab-pane fade group-tab-pane" id="members-pane" role="tabpanel" aria-labelledby="members-tab" tabindex="0">
            @if(($members ?? collect())->count())
            <div class="group-members-list js-group-members-list" id="group-members-list">
                @foreach($members as $groupMember)
                @php 
                // 1. Phải lấy thông tin từ bảng users thông qua relationship 'user'
                $user = $groupMember->user; 
                // 2. Xử lý tên hiển thị để không bị lỗi null
                $displayName = $user->name ?? $user->Name ?? $user->first_name ?? 'Thành viên';
                @endphp
                    
            <div class="group-member-item js-group-member-item d-flex align-items-center justify-content-between p-3 mb-2 border rounded-3 bg-white" data-member-id="{{ $user->id }}">
            <div class="group-member-left d-flex align-items-center gap-3">
                <img
                src="https://ui-avatars.com/api/?name={{ urlencode($displayName) }}&background=random&color=fff"
                alt="{{ $displayName }}"
                class="group-member-avatar rounded-circle"
                width="44" height="44"
                style="object-fit: cover;"
                >
                
                <div>
                        <p class="group-member-name fw-bold mb-0 text-dark">{{ $displayName }}</p>
                        <span class="badge {{ $groupMember->role === 'admin' ? 'bg-primary' : 'bg-light text-dark' }} small">
                            {{ $groupMember->role === 'admin' ? 'Quản trị viên' : 'Thành viên' }}
                        </span>
                    </div>
                </div>
                    
                    <div class="d-flex align-items-center gap-2">
                        @if(isset($isAdmin) && $isAdmin && $user->id !== auth()->id())
                            <div class="dropdown">
                                <button class="btn btn-light rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px; padding: 0;">
                                    <i class="fas fa-ellipsis-v text-muted" style="font-size: 0.85rem;"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 12px; font-size: 0.9rem;">
                                    @if($groupMember->role !== 'admin')
                                        <li>
                                            <a class="dropdown-item py-2 promote-btn" href="#" data-user-id="{{ $user->id }}">
                                                <i class="fas fa-user-shield me-2 text-primary"></i> Chỉ định Admin
                                            </a>
                                        </li>
                                    @endif
                                    <li>
                                        <a class="dropdown-item py-2 text-danger kick-btn" href="#" data-user-id="{{ $user->id }}">
                                            <i class="fas fa-user-minus me-2"></i> Kích khỏi nhóm
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-4 bg-light rounded-3">
            <i class="fas fa-users-slash text-muted mb-2" style="font-size: 2rem;"></i>
            <p class="text-muted mb-0">Chưa có thành viên nào trong nhóm.</p>
        </div>
    @endif
</div>
		</div>
	</section>
</div>
@endsection
