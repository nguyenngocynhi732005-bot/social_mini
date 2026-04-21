@extends('layouts.app')

@section('content')
<style>
	.groups-page {
		--group-radius: 14px;
		--group-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
		--group-border: #e9ecef;
		--group-muted: #6c757d;
		--group-bg-soft: #f8f9fb;
	}

	.groups-page .groups-sidebar-sticky {
		position: sticky;
		top: 1.25rem;
	}

	.groups-page .groups-panel {
		background: #fff;
		border-radius: var(--group-radius);
		border: 1px solid var(--group-border);
		box-shadow: var(--group-shadow);
	}

	.groups-page .groups-panel-title {
		font-size: 1rem;
		font-weight: 700;
		color: #1f2937;
	}

	.groups-page .groups-your-list {
		display: flex;
		flex-direction: column;
		gap: .65rem;
	}

	.groups-page .groups-your-item {
		display: flex;
		align-items: center;
		gap: .75rem;
		padding: .6rem .7rem;
		border-radius: 12px;
		text-decoration: none;
		color: #1f2937;
		transition: .2s ease;
		border: 1px solid transparent;
	}

	.groups-page .groups-your-item:hover {
		background: var(--group-bg-soft);
		border-color: var(--group-border);
	}

	.groups-page .groups-your-avatar {
		width: 40px;
		height: 40px;
		border-radius: 50%;
		object-fit: cover;
		border: 2px solid #fff;
		box-shadow: 0 2px 8px rgba(2, 6, 23, 0.12);
	}

	.groups-page .groups-your-name {
		font-size: .95rem;
		font-weight: 600;
		line-height: 1.3;
	}

	.groups-page .groups-empty {
		margin: 0;
		padding: .75rem;
		border-radius: 12px;
		background: var(--group-bg-soft);
		color: var(--group-muted);
		font-size: .92rem;
	}

	.groups-page .groups-discover-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
		gap: 1rem;
	}

	.groups-page .groups-card {
		border: 1px solid var(--group-border);
		border-radius: 16px;
		overflow: hidden;
		background: #fff;
		box-shadow: var(--group-shadow);
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.groups-page .groups-card-cover-wrap {
		position: relative;
		width: 100%;
		aspect-ratio: 16 / 9;
		background: #e9ecef;
	}

	.groups-page .groups-card-cover {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.groups-page .groups-card-body {
		padding: 1rem;
		display: flex;
		flex-direction: column;
		gap: .6rem;
		flex: 1;
	}

	.groups-page .groups-card-name {
		font-size: 1.02rem;
		font-weight: 700;
		margin: 0;
		color: #111827;
		line-height: 1.35;
	}

	.groups-page .groups-card-meta {
		margin: 0;
		color: var(--group-muted);
		font-size: .9rem;
	}

	.groups-page .groups-card-action {
		margin-top: auto;
	}

	.groups-page .groups-modal .modal-content {
		border: 1px solid var(--group-border);
		border-radius: 16px;
		box-shadow: var(--group-shadow);
	}

	.groups-page .groups-modal .form-control,
	.groups-page .groups-modal .form-select {
		border-radius: 12px;
		border-color: #dee2e6;
		padding: .65rem .85rem;
	}

	@media (max-width: 991.98px) {
		.groups-page .groups-sidebar-sticky {
			position: static;
		}
	}
</style>

<div class="container py-3 py-md-4 groups-page group-index-page" id="group-index-page">
	<div class="row g-3 g-lg-4">
		<div class="col-12 col-lg-3">
			<div class="groups-sidebar-sticky">
				<button
					type="button"
					class="btn btn-primary w-100 mb-3 js-open-create-group"
					data-bs-toggle="modal"
					data-bs-target="#createGroupModal"
				>
					Tạo nhóm mới
				</button>

				<div class="groups-panel p-3 groups-your-groups-panel">
					<h2 class="groups-panel-title mb-3">Nhóm của bạn</h2>

					@if(($joinedGroups ?? collect())->count())
						<div class="groups-your-list js-joined-groups-list" id="joined-groups-list">
							@foreach($joinedGroups as $joinedGroup)
								<a
									href="{{ route('social.groups.show', $joinedGroup->id) }}"
									class="groups-your-item js-joined-group-item"
									data-group-id="{{ $joinedGroup->id }}"
								>
									<img
										src="{{ $joinedGroup->avatar_image ?? $joinedGroup->cover_image ?? 'https://placehold.co/80x80?text=Group' }}"
										alt="{{ $joinedGroup->name }}"
										class="groups-your-avatar"
									>
									<span class="groups-your-name">{{ $joinedGroup->name }}</span>
								</a>
							@endforeach
						</div>
					@else
						<p class="groups-empty js-joined-groups-empty">Bạn chưa tham gia nhóm nào.</p>
					@endif
				</div>
			</div>
		</div>

		<div class="col-12 col-lg-9">
			<div class="d-flex align-items-center justify-content-between mb-3">
				<h1 class="h4 mb-0 fw-bold">Khám phá nhóm</h1>
			</div>

			@if(($systemGroups ?? collect())->count())
				<div class="groups-discover-grid js-system-groups-grid" id="system-groups-grid">
					@foreach($systemGroups as $group)
						<article class="groups-card js-system-group-card" data-group-id="{{ $group->id }}">
							<div class="groups-card-cover-wrap">
								<img
									src="{{ $group->cover_image ?? 'https://placehold.co/800x450?text=Group+Cover' }}"
									alt="{{ $group->name }}"
									class="groups-card-cover"
								>
							</div>

							<div class="groups-card-body">
								<h3 class="groups-card-name">{{ $group->name }}</h3>
								<p class="groups-card-meta">
									{{ number_format((int) ($group->members_count ?? 0)) }} thành viên
								</p>
								<div class="groups-card-action">
									<a
										href="{{ route('social.groups.show', $group->id) }}"
										class="btn btn-outline-primary w-100 js-view-group-detail"
										data-group-id="{{ $group->id }}"
									>
										Xem chi tiết
									</a>
								</div>
							</div>
						</article>
					@endforeach
				</div>
			@else
				<div class="groups-panel p-4">
					<p class="groups-empty mb-0">Hiện chưa có nhóm nào để khám phá.</p>
				</div>
			@endif
		</div>
	</div>
</div>

<div class="modal fade groups-modal" id="createGroupModal" tabindex="-1" aria-labelledby="createGroupModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header border-0 pb-0">
				<h5 class="modal-title fw-bold" id="createGroupModalLabel">Tạo nhóm mới</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body pt-3">
				<form method="POST" action="{{ route('social.groups.store') }}" class="js-create-group-form" id="create-group-form">
					@csrf

					<div class="mb-3">
						<label for="group-name" class="form-label fw-semibold">Tên nhóm</label>
						<input
							type="text"
							id="group-name"
							name="name"
							class="form-control js-group-name-input"
							placeholder="Nhập tên nhóm"
							required
						>
					</div>

					<div class="mb-3">
						<label for="group-description" class="form-label fw-semibold">Mô tả</label>
						<textarea
							id="group-description"
							name="description"
							class="form-control js-group-description-input"
							rows="4"
							placeholder="Viết mô tả ngắn cho nhóm"
						></textarea>
					</div>

					<div class="mb-3">
						<label for="group-privacy" class="form-label fw-semibold">Quyền riêng tư</label>
						<select id="group-privacy" name="privacy" class="form-select js-group-privacy-select" required>
							<option value="public">Công khai</option>
							<option value="private">Riêng tư</option>
						</select>
					</div>

					<div class="d-flex justify-content-end gap-2 mt-4">
						<button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
						<button type="submit" class="btn btn-primary js-submit-create-group">Tạo nhóm</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection
