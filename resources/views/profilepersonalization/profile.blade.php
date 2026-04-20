@extends('layouts.app')

@section('title', 'Profile')

@push('styles')
<style>
    #ajax-content {
        width: 100%;
        max-width: 1180px;
        flex: 0 0 100%;
    }

    .pp-profile-shell {
        max-width: 1120px;
        margin: 0 auto;
    }

    .pp-bento-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 24px rgba(22, 30, 58, 0.08);
        border: 1px solid rgba(172, 183, 203, 0.24);
    }

    .pp-cover {
        height: 220px;
        background: linear-gradient(135deg, #a8d7f5 0%, #d8b7e7 100%);
    }

    .pp-cover-wrap {
        position: relative;
    }

    .pp-cover-edit-btn {
        position: absolute;
        right: 14px;
        bottom: 14px;
        border-radius: 10px;
        border: 1px solid #d9e2f2;
        background: rgba(255, 255, 255, 0.96);
        color: #1f2937;
        font-weight: 600;
        box-shadow: 0 6px 18px rgba(16, 24, 40, 0.16);
    }

    .pp-avatar-wrap {
        position: relative;
        display: inline-block;
    }

    .pp-avatar-wrap.has-story::before {
        content: '';
        position: absolute;
        inset: -5px;
        border-radius: 999px;
        border: 4px solid #16a34a;
        box-shadow: 0 0 0 2px #ffffff;
        pointer-events: none;
    }

    .pp-avatar-story-trigger {
        cursor: pointer;
    }

    .pp-avatar-edit-btn {
        position: absolute;
        right: 2px;
        bottom: 2px;
        width: 30px;
        height: 30px;
        border-radius: 999px;
        border: 2px solid #ffffff;
        background: #eef3fb;
        color: #2d3748;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(16, 24, 40, 0.2);
    }

    .pp-main-grid {
        display: grid;
        grid-template-columns: 40% 60%;
        gap: 16px;
    }

    .pp-status-input {
        background: #f3f5f9;
        border: 1px solid #e4e8f0;
        border-radius: 999px;
        color: #6b7280;
    }

    .pp-profile-composer-trigger {
        background: #f0f2f5;
        border: 1px solid #e4e8f0;
    }

    .pp-profile-composer-trigger:hover {
        background: #e9edf4;
    }

    .pp-post-card .post-content {
        word-break: break-word;
    }

    .pp-friend-thumb {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        object-fit: cover;
    }

    .pp-photo-thumb {
        width: 100%;
        aspect-ratio: 1 / 1;
        border-radius: 10px;
        object-fit: cover;
    }

    .pp-profile-menu {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e9edf5;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .pp-profile-menu-item {
        border: 1px solid #e4e8f0;
        background: #f6f8fc;
        color: #4b5563;
        border-radius: 10px;
        padding: 8px 12px;
        font-weight: 600;
        font-size: 0.94rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .pp-profile-menu-item:hover {
        background: #edf2fb;
        color: #1f2937;
    }

    .pp-profile-menu-item.active {
        background: #e8f0ff;
        border-color: #c7dafd;
        color: #1f4ca3;
    }

    .pp-intro-edit-modal .modal-content {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 18px 36px rgba(18, 28, 45, 0.16);
    }

    .pp-intro-edit-modal .modal-header,
    .pp-intro-edit-modal .modal-footer {
        border-color: #edf1f7;
    }

    .pp-intro-edit-modal .form-label {
        font-weight: 600;
        color: #344054;
        margin-bottom: 6px;
    }

    .pp-intro-edit-modal .input-group-text {
        background: #f8fafc;
        border-color: #dbe4f1;
        color: #667085;
        min-width: 44px;
        justify-content: center;
    }

    .pp-intro-edit-modal .form-control,
    .pp-intro-edit-modal .form-select {
        border-color: #dbe4f1;
        border-radius: 10px;
    }

    .pp-cover-modal .modal-content {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 18px 36px rgba(18, 28, 45, 0.16);
    }

    .pp-cover-dropzone {
        border: 1.5px dashed #cfd8e8;
        border-radius: 14px;
        background: #f8fbff;
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 18px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .pp-cover-dropzone.dragover {
        border-color: #4f8cff;
        background: #eef5ff;
        transform: scale(1.01);
    }

    .pp-cover-gallery-item {
        border: 2px solid transparent;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .pp-cover-gallery-item:hover,
    .pp-cover-gallery-item.active {
        border-color: #4f8cff;
        box-shadow: 0 8px 18px rgba(79, 140, 255, 0.18);
    }

    .pp-cover-crop-stage {
        background: #0f172a;
        border-radius: 14px;
        overflow: hidden;
        min-height: 190px;
        max-height: 230px;
        position: relative;
    }

    .pp-cover-crop-preview {
        width: 100%;
        height: 100%;
        max-height: 230px;
        display: block;
    }

    .pp-cover-crop-hint {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .hidden {
        display: none !important;
    }

    .pp-avatar-modal .modal-content {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 18px 36px rgba(18, 28, 45, 0.16);
    }

    .pp-avatar-dropzone {
        border: 1.5px dashed #cfd8e8;
        border-radius: 14px;
        background: #f8fbff;
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 18px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .pp-avatar-dropzone.dragover {
        border-color: #4f8cff;
        background: #eef5ff;
        transform: scale(1.01);
    }

    .pp-avatar-gallery-item {
        border: 2px solid transparent;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .pp-avatar-gallery-item:hover,
    .pp-avatar-gallery-item.active {
        border-color: #4f8cff;
        box-shadow: 0 8px 18px rgba(79, 140, 255, 0.18);
    }

    .pp-avatar-crop-stage {
        background: #0f172a;
        border-radius: 999px;
        overflow: hidden;
        width: 100%;
        max-width: 360px;
        aspect-ratio: 1 / 1;
        margin: 0 auto;
        position: relative;
    }

    .pp-avatar-crop-preview {
        width: 100%;
        height: 100%;
        display: block;
    }

    .pp-avatar-crop-hint {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .pp-avatar-zoom-wrap {
        max-width: 360px;
        margin: 12px auto 0;
    }

    .pp-avatar-zoom-wrap input[type="range"] {
        width: 100%;
    }

    .pp-avatar-modal .cropper-view-box,
    .pp-avatar-modal .cropper-face {
        border-radius: 50%;
    }

    .pp-avatar-modal .cropper-point,
    .pp-avatar-modal .cropper-line {
        display: none;
    }

    .pp-intro-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
        color: #6b7280;
        font-size: 0.95rem;
        line-height: 1.4;
    }

    .pp-intro-row:last-of-type {
        margin-bottom: 0;
    }

    .pp-intro-icon {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        background: #f3f6fb;
        color: #6b7280;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 28px;
        margin-top: 1px;
    }

    .pp-intro-content {
        flex: 1;
        min-width: 0;
    }

    .pp-intro-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #9aa4b2;
        margin-bottom: 2px;
    }

    .pp-intro-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 12px;
        font-weight: 700;
        font-size: 0.86rem;
        line-height: 1;
    }

    @media (max-width: 991.98px) {
        #ajax-content {
            max-width: 100%;
        }

        .pp-main-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $profileName = $profileDisplayName ?? trim((string) (optional($currentUser)->name ?? ''));
    if ($profileName === '') {
        $profileName = (string) (optional($currentUser)->email ?? 'Nguoi dung');
    }
    $profileFriendCount = (int) ($friendCount ?? 0);
    $profilePostCount = (int) ($postCount ?? 0);

    $profileStoryItems = collect($profileStories ?? collect())
        ->map(function ($story) {
            $path = (string) ($story->image_path ?? '');
            if ($path === '') {
                return null;
            }

            $url = \Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])
                ? $path
                : asset('storage/' . ltrim($path, '/'));

            return [
                'image' => $url,
                'caption' => (string) ($story->caption ?? ''),
                'textColor' => (string) ($story->text_color ?? '#ffffff'),
                'musicName' => (string) ($story->music_name ?? ''),
                'musicPath' => (string) ($story->music_path ?? ''),
                'scale' => (float) ($story->image_scale ?? 1),
                'createdAt' => optional($story->created_at)->toIso8601String(),
            ];
        })
        ->filter()
        ->values();

    $hasActiveStoryState = (bool) ($hasActiveStory ?? false);
@endphp
<div class="pp-profile-shell" data-profile-id="{{ (int) ($profileId ?? optional($currentUser)->id ?? 0) }}">
    <div class="pp-bento-card overflow-hidden">
        <div class="pp-cover-wrap">
            <div
                class="pp-cover"
                style="{{ !empty($currentCoverUrl) ? 'background-image: url(' . $currentCoverUrl . '); background-size: cover; background-position: center;' : '' }}"
            ></div>
            <button type="button" class="btn btn-sm pp-cover-edit-btn" data-bs-toggle="modal" data-bs-target="#profileCoverModal">
                <i class="fas fa-camera me-2"></i>Thay đổi ảnh bìa
            </button>
        </div>

        <div class="p-3 p-md-4" style="margin-top: -58px;">
            <div class="d-flex align-items-end justify-content-between gap-3 flex-wrap">
                <div class="d-flex align-items-end gap-3 flex-wrap">
                    <div class="pp-avatar-wrap {{ $hasActiveStoryState ? 'has-story' : '' }}">
                        <img
                            id="profileAvatarStoryTrigger"
                            src="{{ $currentAvatarUrl ?? 'https://i.pravatar.cc/160?u=nhi' }}"
                            alt="avatar"
                            data-current-user-avatar="1"
                            class="rounded-circle border border-4 border-white shadow pp-avatar-story-trigger"
                            width="112"
                            height="112"
                            title="{{ $hasActiveStoryState ? 'Xem tin' : 'Chua co tin moi' }}"
                        >
                        <button type="button" class="pp-avatar-edit-btn" aria-label="Thay đổi ảnh đại diện" data-bs-toggle="modal" data-bs-target="#profileAvatarModal">
                            <i class="fas fa-camera" style="font-size: 12px;"></i>
                        </button>
                    </div>
                    <div class="pb-1">
                        <h3 class="mb-1 fw-bold">{{ $profileName }}</h3>
                        <div class="text-muted">{{ $profileFriendCount }} ban be · {{ $profilePostCount }} bài viết</div>
                    </div>
                </div>

                <div class="d-flex gap-2 pb-1">
                    <button type="button" class="btn btn-light border" onclick="openAccountSettingsModal()">Chỉnh sửa trang cá nhân</button>
                    <button type="button" id="profileHeaderStoryBtn" class="btn btn-primary">Thêm vào tin</button>
                </div>
            </div>

            <div class="pp-profile-menu" aria-label="Profile menu">
                <button type="button" data-tab="gioi-thieu" class="pp-profile-menu-item border-b-2 border-transparent text-slate-600 active">Giới thiệu</button>
                <button type="button" data-tab="ban-be" class="pp-profile-menu-item border-b-2 border-transparent text-slate-600">Bạn bè</button>
                <button type="button" data-tab="anh" class="pp-profile-menu-item border-b-2 border-transparent text-slate-600">Ảnh</button>
                <button type="button" data-tab="video" class="pp-profile-menu-item border-b-2 border-transparent text-slate-600">Video</button>
            </div>
        </div>
    </div>

    <div class="modal fade pp-cover-modal" id="profileCoverModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Cập nhật ảnh bìa</h5>
                        <div class="small text-muted">Chọn ảnh từ thiết bị hoặc từ kho ảnh đã đăng, sau đó kéo để crop theo tỷ lệ ảnh bìa.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <ul class="nav nav-pills gap-2 mb-3" id="coverSourceTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="cover-upload-tab" data-bs-toggle="tab" data-bs-target="#cover-upload-pane" type="button" role="tab">Tải ảnh lên</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="cover-gallery-tab" data-bs-toggle="tab" data-bs-target="#cover-gallery-pane" type="button" role="tab">Chọn từ kho ảnh</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="cover-upload-pane" role="tabpanel">
                            <div id="profileCoverDropzone" class="pp-cover-dropzone mb-3">
                                <div>
                                    <i class="fas fa-cloud-upload-alt fs-2 text-primary mb-2"></i>
                                    <div class="fw-bold">Kéo thả ảnh vào đây</div>
                                    <div class="small text-muted mb-2">Hoặc bấm để chọn file từ thiết bị</div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="profileCoverChooseBtn">Chọn ảnh</button>
                                </div>
                            </div>
                            <input type="file" id="coverInput" class="hidden" accept="image/*">
                        </div>

                        <div class="tab-pane fade" id="cover-gallery-pane" role="tabpanel">
                            <div class="row g-2" id="profileCoverGallery">
                                @php
                                    $galleryImages = collect(($posts ?? collect()))
                                        ->map(function ($post) {
                                            $mediaPath = $post->media_path ?? $post->image_url ?? null;
                                            if (!$mediaPath) {
                                                return null;
                                            }

                                            $mediaType = $post->media_type ?? $post->post_type ?? null;
                                            if ($mediaType === 'video') {
                                                return null;
                                            }

                                            $url = \Illuminate\Support\Str::startsWith((string) $mediaPath, ['http://', 'https://'])
                                                ? $mediaPath
                                                : asset('storage/' . ltrim((string) $mediaPath, '/'));

                                            return [
                                                'url' => $url,
                                                'title' => trim((string) ($post->content ?? '')),
                                            ];
                                        })
                                        ->filter()
                                        ->take(8)
                                        ->values();
                                @endphp

                                @forelse($galleryImages as $image)
                                    <div class="col-6 col-md-3">
                                        <div class="pp-cover-gallery-item" data-cover-gallery-item data-cover-url="{{ $image['url'] }}">
                                            <img src="{{ $image['url'] }}" alt="gallery-cover" style="width: 100%; height: 120px; object-fit: cover; display: block;">
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-light border mb-0">Chưa có ảnh trong kho để chọn.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="fw-bold">Crop ảnh bìa</div>
                            <div class="pp-cover-crop-hint">Kéo để điều chỉnh vùng hiển thị</div>
                        </div>

                        <div class="pp-cover-crop-stage">
                            <img id="profileCoverCropImage" class="pp-cover-crop-preview" alt="Cover crop preview">
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="profileCoverSaveBtn">Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade pp-avatar-modal" id="profileAvatarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Cập nhật ảnh đại diện</h5>
                        <div class="small text-muted">Chọn ảnh từ thiết bị hoặc từ ảnh bạn đã đăng, sau đó zoom và kéo để chỉnh khung tròn.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <ul class="nav nav-pills gap-2 mb-3" id="avatarSourceTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="avatar-upload-tab" data-bs-toggle="tab" data-bs-target="#avatar-upload-pane" type="button" role="tab">Tải ảnh lên</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="avatar-gallery-tab" data-bs-toggle="tab" data-bs-target="#avatar-gallery-pane" type="button" role="tab">Ảnh của bạn</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="avatar-upload-pane" role="tabpanel">
                            <div id="profileAvatarDropzone" class="pp-avatar-dropzone mb-3">
                                <div>
                                    <i class="fas fa-cloud-upload-alt fs-2 text-primary mb-2"></i>
                                    <div class="fw-bold">Kéo thả ảnh vào đây</div>
                                    <div class="small text-muted mb-2">Hoặc bấm để chọn file từ thiết bị</div>
                                    <button type="button" class="btn btn-outline-primary btn-sm">Chọn ảnh</button>
                                </div>
                            </div>
                            <input type="file" id="profileAvatarInput" accept="image/*" class="d-none">
                        </div>

                        <div class="tab-pane fade" id="avatar-gallery-pane" role="tabpanel">
                            <div class="row g-2" id="profileAvatarGallery">
                                @php
                                    $avatarImages = collect(($posts ?? collect()))
                                        ->map(function ($post) {
                                            $mediaPath = $post->media_path ?? $post->image_url ?? null;
                                            if (!$mediaPath) {
                                                return null;
                                            }

                                            $mediaType = $post->media_type ?? $post->post_type ?? null;
                                            if ($mediaType === 'video') {
                                                return null;
                                            }

                                            $url = \Illuminate\Support\Str::startsWith((string) $mediaPath, ['http://', 'https://'])
                                                ? $mediaPath
                                                : asset('storage/' . ltrim((string) $mediaPath, '/'));

                                            return [
                                                'url' => $url,
                                                'title' => trim((string) ($post->content ?? '')),
                                            ];
                                        })
                                        ->filter()
                                        ->take(12)
                                        ->values();
                                @endphp

                                @forelse($avatarImages as $image)
                                    <div class="col-4 col-md-3">
                                        <div class="pp-avatar-gallery-item" data-avatar-gallery-item data-avatar-url="{{ $image['url'] }}">
                                            <img src="{{ $image['url'] }}" alt="avatar-gallery" style="width: 100%; aspect-ratio: 1 / 1; object-fit: cover; display: block;">
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-light border mb-0">Chưa có ảnh trong kho để chọn.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="fw-bold">Crop ảnh đại diện</div>
                            <div class="pp-avatar-crop-hint">Kéo ảnh để chọn góc mặt đẹp nhất</div>
                        </div>

                        <div class="pp-avatar-crop-stage">
                            <img id="profileAvatarCropImage" class="pp-avatar-crop-preview" alt="Avatar crop preview">
                        </div>

                        <div class="pp-avatar-zoom-wrap">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="profileAvatarZoom" class="form-label mb-0 small text-muted">Zoom</label>
                                <button type="button" class="btn btn-sm btn-light border" id="profileAvatarResetBtn">Đặt lại</button>
                            </div>
                            <input type="range" id="profileAvatarZoom" min="0.5" max="3" step="0.01" value="1">
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="profileAvatarSaveBtn">Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <div id="profileTabLayout" class="pp-main-grid mt-3">
        <div>
            <div class="pp-bento-card p-3 mb-3" data-tab-section="gioi-thieu">
                <h5 class="fw-bold mb-3">Giới thiệu</h5>
                @php
                    $relationshipValue = optional($currentUser)->relationship;
                    $relationshipMeta = ['label' => 'Chưa cập nhật', 'class' => 'bg-light text-muted border', 'icon' => 'fa-heart'];

                    if ($relationshipValue === 'doc-than') {
                        $relationshipMeta = ['label' => 'Độc thân', 'class' => 'bg-success-subtle text-success border border-success-subtle', 'icon' => 'fa-heart-crack'];
                    } elseif ($relationshipValue === 'hen-ho') {
                        $relationshipMeta = ['label' => 'Hẹn hò', 'class' => 'bg-danger-subtle text-danger border border-danger-subtle', 'icon' => 'fa-heart'];
                    } elseif ($relationshipValue === 'da-ket-hon') {
                        $relationshipMeta = ['label' => 'Đã kết hôn', 'class' => 'bg-primary-subtle text-primary border border-primary-subtle', 'icon' => 'fa-ring'];
                    }
                @endphp
                <div class="pp-intro-row">
                    <span class="pp-intro-icon"><i class="fas fa-align-left"></i></span>
                    <div class="pp-intro-content">
                        <span class="pp-intro-label">Bio</span>
                        <div>{{ optional($currentUser)->bio ?? 'Chưa cập nhật bio' }}</div>
                    </div>
                </div>
                <div class="pp-intro-row">
                    <span class="pp-intro-icon"><i class="fas fa-briefcase"></i></span>
                    <div class="pp-intro-content">
                        <span class="pp-intro-label">Làm việc tại</span>
                        <div>{{ optional($currentUser)->work ?? 'Chưa cập nhật' }}</div>
                    </div>
                </div>
                <div class="pp-intro-row">
                    <span class="pp-intro-icon"><i class="fas fa-graduation-cap"></i></span>
                    <div class="pp-intro-content">
                        <span class="pp-intro-label">Học tại</span>
                        <div>{{ optional($currentUser)->education ?? 'Chưa cập nhật' }}</div>
                    </div>
                </div>
                <div class="pp-intro-row">
                    <span class="pp-intro-icon"><i class="fas fa-location-dot"></i></span>
                    <div class="pp-intro-content">
                        <span class="pp-intro-label">Sống tại</span>
                        <div>{{ optional($currentUser)->location ?? 'Chưa cập nhật' }}</div>
                    </div>
                </div>
                <div class="pp-intro-row">
                    <span class="pp-intro-icon"><i class="fas fa-map-location-dot"></i></span>
                    <div class="pp-intro-content">
                        <span class="pp-intro-label">Đến từ</span>
                        <div>{{ optional($currentUser)->hometown ?? 'Chưa cập nhật' }}</div>
                    </div>
                </div>
                <div class="pp-intro-row mb-0">
                    <span class="pp-intro-icon"><i class="fas {{ $relationshipMeta['icon'] }}"></i></span>
                    <div class="pp-intro-content d-flex align-items-center justify-content-between gap-2 flex-wrap">
                        <div>
                            <span class="pp-intro-label mb-1">Tình trạng</span>
                            <span class="pp-intro-status-badge {{ $relationshipMeta['class'] }}">
                                {{ $relationshipMeta['label'] }}
                            </span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-light border w-100 mt-2" onclick="openProfileIntroModal()">Chỉnh sửa chi tiết</button>
            </div>

            <div class="pp-bento-card p-3 mb-3" data-tab-section="gioi-thieu">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Bạn bè</h5>
                    <button type="button" class="btn btn-link text-decoration-none p-0" data-switch-tab="ban-be">Xem tất cả</button>
                </div>

                <div class="row g-2">
                    @forelse(($friendsData ?? collect())->take(9) as $friend)
                        <div class="col-4 text-center">
                            <img src="{{ $friend->avatar_url }}" class="pp-friend-thumb mb-1" alt="friend-{{ $friend->getKey() }}">
                            <div class="small text-truncate">{{ $friend->name ?? $friend->Name ?? ('User #' . $friend->getKey()) }}</div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-light border mb-0">Chưa có bạn bè để hiển thị.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pp-bento-card p-3 mb-3" data-tab-section="gioi-thieu">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Ảnh</h5>
                    <button type="button" class="btn btn-link text-decoration-none p-0" data-switch-tab="anh">Xem tất cả</button>
                </div>

                <div class="row g-2">
                    @forelse(($photosData ?? collect())->take(9) as $photoPost)
                        @php
                            $photoPath = $photoPost->media_path ?? $photoPost->image_url ?? null;
                            $photoUrl = $photoPath
                                ? (\Illuminate\Support\Str::startsWith((string) $photoPath, ['http://', 'https://'])
                                    ? $photoPath
                                    : asset('storage/' . ltrim((string) $photoPath, '/')))
                                : null;
                        @endphp
                        @if($photoUrl)
                            <div class="col-4"><img src="{{ $photoUrl }}" class="pp-photo-thumb" alt="photo-{{ $photoPost->id }}"></div>
                        @endif
                    @empty
                        <div class="col-12">
                            <div class="alert alert-light border mb-0">Chưa có ảnh nào từ bài viết.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="modal fade pp-intro-edit-modal" id="accountSettingsModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Chỉnh sửa trang cá nhân</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="accountFirstNameInput" class="form-label">First Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input id="accountFirstNameInput" type="text" class="form-control" placeholder="First Name" value="{{ optional($currentUser)->first_name ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="accountLastNameInput" class="form-label">Last Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input id="accountLastNameInput" type="text" class="form-control" placeholder="Last Name" value="{{ optional($currentUser)->last_name ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label for="accountEmailInput" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input id="accountEmailInput" type="email" class="form-control" placeholder="Email" value="{{ optional($currentUser)->email ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="accountPhoneInput" class="form-label">Số điện thoại</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input id="accountPhoneInput" type="text" class="form-control" placeholder="Số điện thoại" value="{{ optional($currentUser)->phone ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label for="accountBirthDateInput" class="form-label">Ngày sinh</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-cake-candles"></i></span>
                                            <input id="accountBirthDateInput" type="date" class="form-control" value="{{ optional(optional($currentUser)->birth_date)->format('Y-m-d') }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="accountGenderInput" class="form-label">Giới tính</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                                            <select id="accountGenderInput" class="form-select">
                                                <option value="">Chọn giới tính</option>
                                                <option value="male" {{ optional($currentUser)->gender === 'male' ? 'selected' : '' }}>Nam</option>
                                                <option value="female" {{ optional($currentUser)->gender === 'female' ? 'selected' : '' }}>Nữ</option>
                                                <option value="other" {{ optional($currentUser)->gender === 'other' ? 'selected' : '' }}>Khác</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 border rounded-3 p-3 bg-light">
                                    <div class="fw-bold mb-2">Đổi mật khẩu</div>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="accountCurrentPasswordInput" class="form-label">Mật khẩu cũ</label>
                                            <input id="accountCurrentPasswordInput" type="password" class="form-control" placeholder="Nhập mật khẩu cũ">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="accountNewPasswordInput" class="form-label">Mật khẩu mới</label>
                                            <input id="accountNewPasswordInput" type="password" class="form-control" placeholder="Nhập mật khẩu mới">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="accountConfirmPasswordInput" class="form-label">Xác nhận mật khẩu</label>
                                            <input id="accountConfirmPasswordInput" type="password" class="form-control" placeholder="Nhập lại mật khẩu mới">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer justify-content-end">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                                <button type="button" class="btn btn-primary" id="profileAccountSaveBtn">Lưu tài khoản</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade pp-intro-edit-modal" id="profileIntroEditModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Chỉnh sửa chi tiết giới thiệu</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="profileBioInput" class="form-label">Tiểu sử (tối đa 101 ký tự)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                                        <textarea id="profileBioInput" class="form-control" rows="2" maxlength="101" placeholder="Nhap tieu su ngan">{{ optional($currentUser)->bio ?? '' }}</textarea>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="profileWorkInput" class="form-label">Làm việc tại</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                                        <input id="profileWorkInput" type="text" class="form-control" placeholder="Công ty / vị trí công việc" value="{{ optional($currentUser)->work ?? '' }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="profileEducationInput" class="form-label">Học tại</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                                        <input id="profileEducationInput" type="text" class="form-control" placeholder="Trường học / ngành học" value="{{ optional($currentUser)->education ?? '' }}">
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="profileLivingInput" class="form-label">Sống tại</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-city"></i></span>
                                            <input id="profileLivingInput" type="text" class="form-control" placeholder="Thanh pho hien tai" value="{{ optional($currentUser)->location ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="profileFromInput" class="form-label">Đến từ</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-map-location-dot"></i></span>
                                            <input id="profileFromInput" type="text" class="form-control" placeholder="Quê quán" value="{{ optional($currentUser)->hometown ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="profileRelationshipInput" class="form-label">Tình trạng mối quan hệ</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-heart"></i></span>
                                        <select id="profileRelationshipInput" class="form-select">
                                            <option value="">Chọn tình trạng</option>
                                            <option value="doc-than" {{ optional($currentUser)->relationship === 'doc-than' ? 'selected' : '' }}>Độc thân</option>
                                            <option value="hen-ho" {{ optional($currentUser)->relationship === 'hen-ho' ? 'selected' : '' }}>Hẹn hò</option>
                                            <option value="da-ket-hon" {{ optional($currentUser)->relationship === 'da-ket-hon' ? 'selected' : '' }}>Đã kết hôn</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer justify-content-end">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                                <button type="button" class="btn btn-primary" id="profileIntroSaveBtn">Lưu chi tiết</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div data-tab-section="ban-be" class="d-none">
                @include('profilepersonalization.components.friends_tab', ['friendsData' => ($friendsData ?? collect())])
            </div>

            <div data-tab-section="anh" class="d-none">
                @include('profilepersonalization.components.photos_tab', ['photosData' => ($photosData ?? collect())])
            </div>

            <div data-tab-section="video" class="d-none">
                <div class="pp-bento-card p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Video</h5>
                        <span class="small text-muted">{{ ($videosData ?? collect())->count() }} video</span>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        @forelse(($videosData ?? collect()) as $videoPost)
                            @php
                                $videoPath = $videoPost->media_path ?? null;
                                $videoUrl = $videoPath
                                    ? (\Illuminate\Support\Str::startsWith((string) $videoPath, ['http://', 'https://'])
                                        ? $videoPath
                                        : asset('storage/' . ltrim((string) $videoPath, '/')))
                                    : null;
                            @endphp
                            @if($videoUrl)
                                <video class="rounded w-100" style="max-height: 360px; object-fit: cover;" controls preload="metadata" playsinline>
                                    <source src="{{ $videoUrl }}">
                                </video>
                            @endif
                        @empty
                            <div class="alert alert-light border mb-0">Chưa có video nào trong bảng posts.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <div data-tab-section="gioi-thieu">
            <div class="pp-bento-card p-3 mb-3">
                <div id="profilePostAlert" class="alert d-none mb-3"></div>

                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="{{ $currentAvatarUrl ?? 'https://i.pravatar.cc/160?u=nhi' }}" data-current-user-avatar="1" class="rounded-circle" width="44" height="44" alt="composer-avatar">
                    <button
                        id="profileOpenPostComposerBtn"
                        type="button"
                        class="btn btn-light text-muted fw-bold border-0 bg-transparent rounded-pill flex-grow-1 text-start px-3"
                        data-bs-toggle="modal"
                        data-bs-target="#profilePostModal">
                        Bạn đang nghĩ gì thế?
                    </button>
                </div>

                <hr>
                <div class="d-flex justify-content-around">
                    <button id="profileQuickVideoBtn" type="button" class="btn btn-light text-muted fw-bold border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#profileVideoModal"><i class="fas fa-video text-danger me-2"></i>Quay video</button>
                    <button id="profileQuickMediaBtn" type="button" class="btn btn-light text-muted fw-bold border-0 bg-transparent"><i class="fas fa-images text-success me-2"></i>Ảnh/video</button>
                    <button id="profileQuickFeelingBtn" type="button" class="btn btn-light text-muted fw-bold border-0 bg-transparent"><i class="fas fa-smile text-warning me-2"></i>Cảm xúc</button>
                </div>
            </div>

            <div class="modal fade" id="profilePostModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-md">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                        <div class="modal-header border-bottom justify-content-center position-relative">
                            <h5 class="fw-bold m-0">Tạo bài viết</h5>
                            <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"></button>
                        </div>

                        <form id="profileCreatePostForm" action="{{ route('post.store') }}" method="POST" enctype="multipart/form-data" data-chunk-url="{{ route('post.upload.chunk') }}" data-complete-url="{{ route('post.upload.complete') }}">
                            @csrf
                            <input type="hidden" name="profile_id" value="{{ (int) ($profileId ?? 0) }}">
                            <div class="modal-body">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ $currentAvatarUrl ?? 'https://i.pravatar.cc/45?u=profile' }}" data-current-user-avatar="1" class="rounded-circle me-2 border" width="45" height="45" alt="profile-user-avatar">
                                    <div>
                                        <div class="fw-bold">{{ $profileName }}</div>
                                        <div class="dropdown mt-1">
                                            <button id="profilePostPrivacyBtn" type="button" class="badge bg-light text-dark fw-normal border d-inline-flex align-items-center" style="font-size: 12px;" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i id="profilePostPrivacyIcon" class="fas fa-globe-asia me-1"></i>
                                                <span id="profilePostPrivacyLabel">Công khai</span>
                                            </button>
                                            <ul class="dropdown-menu shadow-sm">
                                                <li><button type="button" class="dropdown-item" data-privacy-option data-privacy-target="profilePost" data-privacy-value="public">🌍 Công khai</button></li>
                                                <li><button type="button" class="dropdown-item" data-privacy-option data-privacy-target="profilePost" data-privacy-value="friends">👥 Bạn bè</button></li>
                                                <li><button type="button" class="dropdown-item" data-privacy-option data-privacy-target="profilePost" data-privacy-value="private">🔒 Chỉ mình tôi</button></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div id="profileEditor" style="min-height: 150px;"></div>
                                <input type="hidden" name="content" id="profilePostContentInput">
                                <input type="hidden" name="uploaded_media_path" id="profileUploadedMediaPathInput">
                                <input type="hidden" name="uploaded_media_type" id="profileUploadedMediaTypeInput">
                                <input type="hidden" name="privacy_status" id="profilePostPrivacyStatusInput" value="public">

                                <div id="profilePostMediaPreviewContainer" class="mt-2 position-relative d-none">
                                    <div class="border rounded p-1 bg-light">
                                        <img id="profileImgPreview" src="#" class="img-fluid rounded d-block d-none mx-auto" style="max-height: 320px; max-width: 100%; width: auto; object-fit: contain;" alt="image-preview">
                                        <video id="profileVideoPreview" class="rounded d-block d-none mx-auto" style="max-height: 320px; max-width: 100%; width: auto; object-fit: contain;" controls preload="metadata" playsinline></video>
                                        <button type="button" class="btn-close position-absolute top-0 end-0 m-2 bg-white shadow" id="profileClearMediaBtn"></button>
                                    </div>
                                </div>

                                <div id="profilePostUploadFeedback" class="small mt-2 text-muted d-none"></div>
                                <div id="profilePostUploadProgressWrap" class="progress mt-2 d-none" style="height: 10px;">
                                    <div id="profilePostUploadProgressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">0%</div>
                                </div>

                                <div class="border rounded-3 p-2 mt-3 d-flex align-items-center justify-content-between">
                                    <span class="small fw-bold ms-2">Thêm vào bài viết của bạn</span>
                                    <div class="d-flex gap-1">
                                        <label for="profilePostMedia" class="btn btn-light btn-sm rounded-circle p-2" title="Anh/Video">
                                            <i class="fas fa-images text-success fs-5"></i>
                                        </label>
                                        <input type="file" id="profilePostMedia" name="media" class="d-none" accept="image/*,video/mp4,video/webm,video/ogg,video/quicktime" onchange="previewMedia(this)">

                                        <button type="button" id="profileEmojiBtn" class="btn btn-light btn-sm rounded-circle p-2" title="Cam xuc">
                                            <i class="fas fa-smile text-warning fs-5"></i>
                                        </button>
                                    </div>
                                </div>

                                <div id="profileEmojiFallbackPanel" class="border rounded-3 mt-2 p-2 d-none" style="max-height: 130px; overflow-y: auto;">
                                    <div class="d-flex flex-wrap gap-1" id="profileEmojiFallbackList">
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="😀">😀</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="😁">😁</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="😂">😂</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="🤣">🤣</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="😊">😊</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="😍">😍</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="😘">😘</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="🤩">🤩</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="😎">😎</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="🥰">🥰</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="😢">😢</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="😭">😭</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="😡">😡</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="👍">👍</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="❤️">❤️</button>
                                        <button type="button" class="btn btn-light btn-sm" data-emoji="🎉">🎉</button>
                                    </div>
                                </div>

                                <div id="profileFeelingQuickBar" class="border rounded-3 mt-2 p-2 d-none">
                                    <div class="small text-muted mb-2">ạn đang cảm thấy thế nào?</div>
                                    <div class="d-flex flex-wrap gap-2" id="profileFeelingQuickList">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-feeling="😊 vui ve">😊 Vui vẻ</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-feeling="🥰 hanh phuc">🥰 Hạnh phúc</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-feeling="🤩 hao hung">🤩 Hào hứng</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-feeling="😢 buon">😢 Buồn</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-feeling="😡 buc minh">😡 Bực mình</button>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer border-0 p-3">
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Hủy</button>
                                <button id="profilePostSubmitBtn" type="submit" class="btn fw-bold text-white py-2" style="background: linear-gradient(45deg, #ff85a2, #ba62ff); border-radius: 8px;">
                                    Đăng bài viết
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="profileVideoModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                        <div class="modal-header border-bottom justify-content-center position-relative">
                            <h5 class="fw-bold m-0">Quay hoặc tải video</h5>
                            <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"></button>
                        </div>

                        <form id="profileCreateVideoForm" action="{{ route('post.store') }}" method="POST" enctype="multipart/form-data" data-chunk-url="{{ route('post.upload.chunk') }}" data-complete-url="{{ route('post.upload.complete') }}">
                            @csrf
                            <input type="hidden" name="profile_id" value="{{ (int) ($profileId ?? 0) }}">
                            <input type="hidden" name="post_type" value="video">

                            <div class="modal-body">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ $currentAvatarUrl ?? 'https://i.pravatar.cc/45?u=profile' }}" data-current-user-avatar="1" class="rounded-circle me-2 border" width="45" height="45" alt="profile-video-user-avatar">
                                    <div>
                                        <div class="fw-bold">{{ $profileName }}</div>
                                        <div class="dropdown mt-1">
                                            <button id="profileVideoPrivacyBtn" type="button" class="badge bg-light text-dark fw-normal border d-inline-flex align-items-center" style="font-size: 12px;" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i id="profileVideoPrivacyIcon" class="fas fa-globe-asia me-1"></i>
                                                <span id="profileVideoPrivacyLabel">Công khai</span>
                                            </button>
                                            <ul class="dropdown-menu shadow-sm">
                                                <li><button type="button" class="dropdown-item" data-privacy-option data-privacy-target="profileVideo" data-privacy-value="public">🌍 Công khai</button></li>
                                                <li><button type="button" class="dropdown-item" data-privacy-option data-privacy-target="profileVideo" data-privacy-value="friends">👥 Bạn bè</button></li>
                                                <li><button type="button" class="dropdown-item" data-privacy-option data-privacy-target="profileVideo" data-privacy-value="private">🔒 Chỉ mình tôi</button></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div id="profileVideoEditor" style="min-height: 100px;" class="mb-3 border rounded p-2 bg-light"></div>
                                <input type="hidden" name="content" id="profileVideoContentInput">
                                <input type="hidden" name="uploaded_media_path" id="profileVideoUploadedMediaPathInput">
                                <input type="hidden" name="uploaded_media_type" id="profileVideoUploadedMediaTypeInput">
                                <input type="hidden" name="privacy_status" id="profileVideoPrivacyStatusInput" value="public">

                                <div id="profileVideoMediaPreviewContainer" class="mt-2 position-relative d-none">
                                    <div class="border rounded p-1 bg-light">
                                        <video id="profileVideoComposerPreviewVideo" class="rounded d-block mx-auto" style="max-height: 320px; max-width: 100%; width: auto; object-fit: contain; display: none;" controls preload="metadata" playsinline></video>
                                        <button type="button" id="profileVideoPreviewClearBtn" class="btn-close position-absolute top-0 end-0 m-2 bg-white shadow"></button>
                                    </div>
                                </div>

                                <div id="liveRecorderPanel" class="border rounded-3 mt-2 p-2 d-none bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold small">Quay video</span>
                                        <span id="liveRecordTimer" class="badge bg-secondary">00:00</span>
                                    </div>

                                    <video id="livePreviewVideo" class="w-100 rounded" style="max-height: 280px; object-fit: cover; background:#111;" autoplay muted playsinline></video>

                                    <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
                                        <label for="liveDurationSelect" class="small text-muted mb-0">Thời lượng tối đa</label>
                                        <select id="liveDurationSelect" class="form-select form-select-sm" style="width: 120px;">
                                            <option value="120">2 phút</option>
                                            <option value="180" selected>3 phút</option>
                                            <option value="300">5 phút</option>
                                        </select>

                                        <select id="liveMusicSelect" class="form-select form-select-sm" style="min-width: 180px; max-width: 220px;">
                                            <option value="">Không dùng nhạc</option>
                                            @foreach(($hotSongs ?? collect()) as $song)
                                                <option value="{{ $song->playable_url }}">{{ $song->title }}</option>
                                            @endforeach
                                        </select>

                                        <label for="liveMusicVolume" class="small text-muted mb-0">Nhạc</label>
                                        <input id="liveMusicVolume" type="range" min="0" max="1" step="0.05" value="0.35" style="width: 100px;">
                                        <span id="liveMusicTime" class="small text-muted">00:00 / 00:00</span>
                                    </div>

                                    <div class="d-flex gap-2 mt-2">
                                        <button type="button" id="liveStartBtn" class="btn btn-danger btn-sm">Bắt đầu quay</button>
                                        <button type="button" id="liveStopBtn" class="btn btn-secondary btn-sm" disabled>Dừng</button>
                                        <button type="button" id="liveRetakeBtn" class="btn btn-warning btn-sm d-none">Ghi hình lại</button>
                                        <button type="button" id="liveUseBtn" class="btn btn-primary btn-sm" disabled>Dùng video này</button>
                                        <button type="button" id="liveCloseBtn" class="btn btn-light btn-sm">Đóng</button>
                                    </div>

                                    <audio id="liveMusicPlayer" preload="auto" style="display:none;" playsinline></audio>
                                </div>

                                <div id="liveEditorPanel" class="border rounded-3 mt-2 p-2 d-none bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold small">Chỉnh video trước khi đăng</span>
                                        <span id="liveEditDuration" class="badge bg-primary">Cắt: 0s → 0s</span>
                                    </div>

                                    <video id="liveEditPreviewVideo" class="w-100 rounded" style="max-height: 280px; object-fit: cover; background:#111;" controls playsinline></video>

                                    <div class="row g-2 mt-2">
                                        <div class="col-6">
                                            <label class="form-label small fw-semibold text-dark mb-1">Bắt đầu cắt</label>
                                            <input id="liveTrimStart" type="range" min="0" max="0" value="0" class="form-range" style="accent-color:#0d6efd;">
                                            <div id="liveTrimStartLabel" class="small fw-semibold text-primary">0s</div>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-semibold text-dark mb-1">Kết thúc cắt</label>
                                            <input id="liveTrimEnd" type="range" min="0" max="0" value="0" class="form-range" style="accent-color:#198754;">
                                            <div id="liveTrimEndLabel" class="small fw-semibold text-success">0s</div>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <div class="small text-muted mb-1">Vùng đã cắt</div>
                                        <div id="liveTrimTrack" class="position-relative rounded-3 overflow-hidden" style="height: 14px; background: linear-gradient(90deg, #dee2e6 0%, #dee2e6 100%);">
                                            <div id="liveTrimTrackSelected" class="position-absolute top-0 bottom-0" style="left: 0%; width: 100%; background: linear-gradient(90deg, rgba(13,110,253,.9), rgba(25,135,84,.9));"></div>
                                            <div id="liveTrimTrackStartMarker" class="position-absolute top-0 bottom-0" style="width: 2px; left: 0%; background: #0d6efd;"></div>
                                            <div id="liveTrimTrackEndMarker" class="position-absolute top-0 bottom-0" style="width: 2px; left: 100%; background: #198754;"></div>
                                        </div>
                                    </div>

                                    <div id="liveTrimSummary" class="small text-muted mt-1">Độ dài đã cắt: 0s</div>

                                    <div class="d-flex gap-2 mt-2 flex-wrap">
                                        <button type="button" id="liveApplyEditBtn" class="btn btn-primary btn-sm">Áp dụng chỉnh sửa</button>
                                        <button type="button" id="liveBackToRecordBtn" class="btn btn-light btn-sm">Quay lại</button>
                                        <button type="button" id="liveUseEditedBtn" class="btn btn-success btn-sm" disabled>Dùng video đã chỉnh</button>
                                    </div>
                                </div>

                                <div id="profileVideoUploadFeedback" class="small mt-2 text-muted d-none"></div>
                                <div id="profileVideoUploadProgressWrap" class="progress mt-2 d-none" style="height: 10px;">
                                    <div id="profileVideoUploadProgressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">0%</div>
                                </div>

                                <div class="border rounded-3 p-2 mt-3 d-flex align-items-center justify-content-between">
                                    <span class="small fw-bold ms-2">Them video</span>
                                    <div class="d-flex gap-1">
                                        <label for="profileVideoMedia" class="btn btn-light btn-sm rounded-circle p-2" title="Tai video">
                                            <i class="fas fa-video text-danger fs-5"></i>
                                        </label>
                                        <input type="file" id="profileVideoMedia" name="media" class="d-none" accept="video/mp4,video/webm,video/ogg,video/quicktime" onchange="previewVideoMedia(this)">
                                        <button type="button" id="liveRecordOpenBtn" class="btn btn-light btn-sm rounded-circle p-2" title="Quay video trực tiếp">
                                            <i class="fas fa-circle text-danger fs-5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer border-0 p-3">
                                <button id="profileVideoSubmitBtn" type="submit" class="btn w-100 fw-bold text-white py-2" style="background: linear-gradient(45deg, #ff85a2, #ba62ff); border-radius: 8px;">Dang video</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="profileStoryModal" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
                    <div class="modal-content" style="border-radius: 20px; border: 2px solid #1f1c1c; overflow: hidden;">
                        <form id="profileStoryForm" action="{{ route('story.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="profile_id" value="{{ (int) ($profileId ?? 0) }}">
                            <div class="modal-body p-0 position-relative bg-dark" style="min-height: 500px; display: flex; align-items: center; justify-content: center;">
                                <div id="profileStoryCanvas" style="width: min(100%, 300px); aspect-ratio: 9 / 16; overflow: hidden; position: relative; background: #000; transform: scale(1); transform-origin: center center; transition: transform 0.15s ease;">
                                    <img id="profileStoryPreview" src="" style="width: 100%; height: 100%; object-fit: contain; object-position: center; background: #000; transform: scale(1); transform-origin: center center; transition: transform 0.15s ease;">
                                </div>
                    
                                <textarea name="caption" id="profileStoryCaption" placeholder="Nhập nội dung tin..." 
                                style="position: absolute; top: 100px; left: 50px; width: auto; min-width: 150px; 
                                       background: transparent; color: white; border: 1px dashed rgba(255,255,255,0.5); 
                                       text-align: center; font-size: 1.5rem; font-weight: bold; 
                                       text-shadow: 2px 2px 4px rgba(0,0,0,0.5); resize: none; cursor: move;
                                       padding: 10px; overflow: hidden; white-space: nowrap;"></textarea>

                                <input type="hidden" name="pos_top" id="profilePosTopInput" value="100">
                                <input type="hidden" name="pos_left" id="profilePosLeftInput" value="50">

                                <div class="position-absolute end-0 top-50 translate-middle-y d-flex flex-column gap-2 p-3">
                                    <div onclick="changeProfileStoryTextColor('#ffffff')" style="width: 25px; height: 25px; border-radius: 50%; background: white; border: 2px solid #ddd; cursor: pointer;"></div>
                                    <div onclick="changeProfileStoryTextColor('#ff85a2')" style="width: 25px; height: 25px; border-radius: 50%; background: #ff85a2; cursor: pointer;"></div>
                                    <div onclick="changeProfileStoryTextColor('#84fab0')" style="width: 25px; height: 25px; border-radius: 50%; background: #84fab0; cursor: pointer;"></div>
                                    <div onclick="changeProfileStoryTextColor('#ffd166')" style="width: 25px; height: 25px; border-radius: 50%; background: #ffd166; cursor: pointer;"></div>
                                    <input type="hidden" name="text_color" id="profileTextColorInput" value="#ffffff">
                                    <input type="hidden" name="image_scale" id="profileImageScaleInput" value="1">
                                </div>

                                <div class="position-absolute top-0 start-0 m-3">
                                    <button type="button" class="btn btn-sm btn-light rounded-pill text-dark fw-bold" onclick="toggleProfileMusicList()">
                                        <i class="fas fa-music me-1"></i> <span id="profileSelectedMusic">Thêm nhạc</span>
                                    </button>
                                    <div id="profileMusicList" class="bg-white rounded shadow-lg p-2 mt-2 d-none" style="width: 200px; z-index: 10000; max-height: 200px; overflow-y: auto;">
                                        @forelse(($hotSongs ?? collect()) as $song)
                                            <div
                                                class="p-2 border-bottom profile-song-item"
                                                style="font-size: 0.85rem; cursor: pointer;"
                                                data-song-id="{{ $song->id }}"
                                                data-song-name="{{ $song->title }}"
                                                data-song-path="{{ $song->playable_url }}"
                                                onclick="selectProfileMusicFromElement(this)">
                                                <strong>{{ $song->hot_rank }}.</strong> {{ Illuminate\Support\Str::limit($song->title, 20) }}
                                                @if($song->artist)
                                                    <div style="font-size: 0.75rem; color: #666;">{{ $song->artist }}</div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="p-2 text-muted">Chưa có bài hát hot trong database.</div>
                                        @endforelse
                                        <audio id="profileMusicPreviewAudio" preload="auto" style="display:none;" playsinline></audio>
                                    </div>
                                    <input type="hidden" name="music_id" id="profileMusicIdInput" value="">
                                    <input type="hidden" name="music_path" id="profileMusicPathInput" value="">
                                    <input type="hidden" name="music_name" id="profileMusicNameInput" value="">
                                </div>

                                <div class="position-absolute bottom-0 start-50 translate-middle-x p-3" style="width: min(280px, 82%);">
                                    <div class="bg-white bg-opacity-10 rounded-3 px-3 py-2" style="backdrop-filter: blur(8px);">
                                        <input type="range" id="profileStoryZoomRange" min="0.5" max="2" step="0.05" value="1" class="form-range" oninput="setProfileStoryZoom(this.value)">
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer border-0 p-3">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-primary fw-bold">Đăng tin</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="profileStoryViewer" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.92); z-index: 12000;">
                <div style="position: relative; width: min(420px, 96vw); height: min(88vh, 760px); margin: 4vh auto; border-radius: 16px; overflow: hidden; background: #111;">
                    <div style="position: absolute; top: 12px; left: 12px; right: 12px; z-index: 3;">
                        <div style="height: 4px; background: rgba(255,255,255,0.35); border-radius: 999px; overflow: hidden;">
                            <div id="profileStoryViewerProgress" style="height: 100%; width: 0%; background: #ffffff;"></div>
                        </div>
                    </div>

                    <button type="button" id="profileCloseStoryViewer" style="position: absolute; top: 18px; right: 16px; z-index: 4; border: 0; width: 34px; height: 34px; border-radius: 50%; background: rgba(0,0,0,0.45); color: #fff; font-size: 18px;">×</button>

                    <div style="position: absolute; inset: 0;">
                        <img id="profileStoryViewerImage" src="" alt="story-image" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; object-position: center; background: #000; transform: scale(1); transform-origin: center center; transition: transform 0.15s ease;">
                        <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.08), rgba(0,0,0,0.3));"></div>

                        <div style="position: absolute; top: 18px; left: 16px; z-index: 4; color: #fff; text-shadow: 0 1px 3px rgba(0,0,0,0.6);">
                            <div style="font-weight: 700; font-size: 0.95rem;">{{ $profileName }}</div>
                            <div id="profileStoryViewerTime" style="font-size: 0.75rem; opacity: 0.88;"></div>
                            <div id="profileStoryViewerMusic" style="font-size: 0.8rem; opacity: 0.9;"></div>
                        </div>

                        <audio id="profileStoryViewerAudio" preload="auto" style="display:none;" playsinline></audio>
                        <div id="profileStoryViewerCaption" style="position: absolute; left: 16px; right: 16px; bottom: 20px; z-index: 4; color: #fff; text-align: center; font-weight: 700; font-size: 1.1rem; text-shadow: 0 2px 5px rgba(0,0,0,0.8);"></div>
                    </div>

                    <button type="button" id="profilePrevStoryBtn" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); z-index: 4; border: 0; width: 34px; height: 34px; border-radius: 50%; background: rgba(0,0,0,0.4); color: #fff;">‹</button>
                    <button type="button" id="profileNextStoryBtn" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); z-index: 4; border: 0; width: 34px; height: 34px; border-radius: 50%; background: rgba(0,0,0,0.4); color: #fff;">›</button>
                </div>
            </div>

            <input type="file" id="profileStoryInput" name="image" form="profileStoryForm" accept="image/*" style="display: none;" onchange="previewProfileStory(this)">

            @forelse(($posts ?? collect()) as $post)
                @php
                    $rawPostContent = (string) ($post->content ?? '');
                    $plainPostContent = trim(preg_replace('/\s+/u', ' ', strip_tags(str_replace('&nbsp;', ' ', $rawPostContent))));
                    $hasPostContent = $plainPostContent !== '';
                    $postPrivacyStatus = in_array((string) ($post->privacy_status ?? 'public'), ['public', 'friends', 'private'], true)
                        ? (string) $post->privacy_status
                        : 'public';
                    $postPrivacyIcon = $postPrivacyStatus === 'friends'
                        ? 'fa-user-friends'
                        : ($postPrivacyStatus === 'private' ? 'fa-lock' : 'fa-globe-asia');
                    $postPrivacyLabel = $postPrivacyStatus === 'friends'
                        ? 'Ban be'
                        : ($postPrivacyStatus === 'private' ? 'Chi minh toi' : 'Cong khai');
                    $postUser = optional($post->user);
                    $fullName = trim((string) ($postUser->First_name ?? '') . ' ' . (string) ($postUser->Last_name ?? ''));
                    $displayName = $fullName !== ''
                        ? $fullName
                        : ((string) ($postUser->name ?? $postUser->Email ?? $postUser->email ?? 'Nguoi dung'));

                    $postMediaType = $post->media_type ?? $post->post_type ?? null;
                    $postMediaPath = $post->media_path ?? $post->image_url ?? null;
                    $postMediaUrl = $postMediaPath
                        ? (\Illuminate\Support\Str::startsWith((string) $postMediaPath, ['http://', 'https://'])
                            ? $postMediaPath
                            : asset('storage/' . ltrim((string) $postMediaPath, '/')))
                        : null;
                @endphp

                <div class="pp-bento-card p-3 mb-3 pp-post-card">
                    <div class="d-flex align-items-start mb-2">
                        <img src="https://i.pravatar.cc/45?u={{ $post->user_id }}" class="rounded-circle me-2" width="42" height="42" alt="post-avatar">
                        <div>
                            <div class="fw-bold">{{ $displayName }}</div>
                            <small class="text-muted d-inline-flex align-items-center gap-2">
                                <span>{{ optional($post->created_at)->diffForHumans() }}</span>
                                <i class="fas {{ $postPrivacyIcon }}" title="{{ $postPrivacyLabel }}" aria-label="{{ $postPrivacyLabel }}"></i>
                            </small>
                        </div>

                        <div class="dropdown ms-auto">
                            <button class="btn btn-light btn-sm rounded-circle border-0 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Tuy chon bai viet">
                                <span class="fw-bold" style="font-size: 20px; line-height: 1;">&hellip;</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <button
                                        type="button"
                                        class="dropdown-item"
                                        data-open-privacy-modal
                                        data-post-id="{{ (int) $post->id }}"
                                        data-current-privacy="{{ $postPrivacyStatus }}">
                                        Chỉnh sửa quyền riêng tư
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('post.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Bạn có muốn xóa bài viết này không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">Xóa bài viết</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>

                    @if($hasPostContent)
                        <div class="mb-2 post-content">{!! $rawPostContent !!}</div>
                    @endif

                    @if($postMediaType === 'image' && $postMediaUrl)
                        <div class="{{ $hasPostContent ? 'bg-light rounded p-2 text-center' : 'text-center' }}">
                            <img src="{{ $postMediaUrl }}" class="img-fluid rounded d-block mx-auto" style="max-height: 560px; width: auto; max-width: 100%; object-fit: contain;" alt="post media">
                        </div>
                    @elseif($postMediaType === 'video' && $postMediaUrl)
                        <div class="{{ $hasPostContent ? 'bg-light rounded p-2 text-center' : 'text-center' }}">
                            <video class="rounded d-block mx-auto" style="max-height: 560px; width: auto; max-width: 100%; object-fit: contain;" controls preload="metadata" playsinline>
                                <source src="{{ $postMediaUrl }}">
                            </video>
                        </div>
                    @endif

                    @include('components.post-engagement', ['post' => $post])
                </div>
            @empty
                <div class="pp-bento-card p-4 text-center text-muted">Chưa có bài viết nào.</div>
            @endforelse

            <div class="modal fade" id="profilePostPrivacyEditModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-sm" style="border-radius: 14px;">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Chỉnh sửa quyền riêng tư</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex flex-column gap-2" id="profilePostPrivacyEditOptions">
                                <button type="button" class="btn btn-outline-secondary text-start" data-privacy-edit-option data-privacy-value="public">🌍 Công khai</button>
                                <button type="button" class="btn btn-outline-secondary text-start" data-privacy-edit-option data-privacy-value="friends">👥 Bạn bè</button>
                                <button type="button" class="btn btn-outline-secondary text-start" data-privacy-edit-option data-privacy-value="private">🔒 Chỉ mình tôi</button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Hủy</button>
                            <button type="button" class="btn btn-primary" id="profilePostPrivacySaveBtn">Lưu thay đổi</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($profileId))
        <div class="alert alert-info mt-3 mb-0">Đang xem profile id: {{ $profileId }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/video-recorder.js') }}"></script>
<script src="{{ asset('js/video-composer.js') }}"></script>
<script src="{{ asset('js/profile-story-functions.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var quill = null;
    var MAX_MEDIA_SIZE_MB = 2048;
    var CHUNK_SIZE_BYTES = 2 * 1024 * 1024;
    var COMPLETE_RETRY_LIMIT = 12;
    var CHUNK_TIMEOUT_MS = 180000;
    var COMPLETE_TIMEOUT_MS = 300000;
    var profileShell = document.querySelector('.pp-profile-shell');
    var profileUserId = profileShell ? parseInt(profileShell.getAttribute('data-profile-id') || '0', 10) : 0;
    var activeTab = 'gioi-thieu';
    var profileTabButtons = Array.prototype.slice.call(document.querySelectorAll('.pp-profile-menu [data-tab]'));
    var profileTabSections = Array.prototype.slice.call(document.querySelectorAll('[data-tab-section]'));

    function renderActiveTab() {
        profileTabButtons.forEach(function (button) {
            var tabName = button.getAttribute('data-tab');
            var isActive = tabName === activeTab;

            button.classList.toggle('active', isActive);
            button.classList.toggle('text-blue-800', isActive);
            button.classList.toggle('border-b-2', isActive);
            button.classList.toggle('border-blue-700', isActive);
            button.classList.toggle('text-slate-600', !isActive);
            button.classList.toggle('border-transparent', !isActive);
        });

        profileTabSections.forEach(function (section) {
            var sectionTab = section.getAttribute('data-tab-section');
            var shouldShow = false;

            switch (activeTab) {
                case 'gioi-thieu':
                    shouldShow = sectionTab === 'gioi-thieu';
                    break;
                case 'ban-be':
                    shouldShow = sectionTab === 'ban-be';
                    break;
                case 'anh':
                    shouldShow = sectionTab === 'anh';
                    break;
                case 'video':
                    shouldShow = sectionTab === 'video';
                    break;
                default:
                    shouldShow = sectionTab === 'gioi-thieu';
            }

            section.classList.toggle('d-none', !shouldShow);
        });
    }

    profileTabButtons.forEach(function (button) {
        if (button.dataset.bound === '1') {
            return;
        }
        button.dataset.bound = '1';
        button.addEventListener('click', function () {
            activeTab = button.getAttribute('data-tab') || 'gioi-thieu';
            renderActiveTab();
        });
    });

    Array.prototype.slice.call(document.querySelectorAll('[data-switch-tab]')).forEach(function (button) {
        if (button.dataset.boundSwitchTab === '1') {
            return;
        }

        button.dataset.boundSwitchTab = '1';
        button.addEventListener('click', function () {
            activeTab = button.getAttribute('data-switch-tab') || 'gioi-thieu';
            renderActiveTab();
        });
    });

    renderActiveTab();
        function getProfileIdPayloadValue() {
            return Number.isNaN(profileUserId) ? null : profileUserId;
        }

    var currentAvatarUrl = {!! json_encode($currentAvatarUrl ?? '') !!};
    var currentCoverUrl = {!! json_encode($currentCoverUrl ?? '') !!};
    var profileStoryItems = {!! json_encode($profileStoryItems ?? []) !!};

    function formatProfileStoryTime(createdAt) {
        if (!createdAt) {
            return '';
        }

        var createdDate = new Date(createdAt);
        if (isNaN(createdDate.getTime())) {
            return '';
        }

        var diffMs = Date.now() - createdDate.getTime();
        var diffMinutes = Math.floor(diffMs / 60000);
        if (diffMinutes < 1) {
            return 'Mới cập nhật';
        }
        if (diffMinutes < 60) {
            return diffMinutes + ' phút trước';
        }
        return Math.floor(diffMinutes / 60) + ' giờ trước';
    }

    var profileStoryViewerEl = document.getElementById('profileStoryViewer');
    var profileStoryViewerImage = document.getElementById('profileStoryViewerImage');
    var profileStoryViewerCaption = document.getElementById('profileStoryViewerCaption');
    var profileStoryViewerMusic = document.getElementById('profileStoryViewerMusic');
    var profileStoryViewerTime = document.getElementById('profileStoryViewerTime');
    var profileStoryViewerAudio = document.getElementById('profileStoryViewerAudio');
    var profileStoryViewerProgress = document.getElementById('profileStoryViewerProgress');
    var profileStoryPrevBtn = document.getElementById('profilePrevStoryBtn');
    var profileStoryNextBtn = document.getElementById('profileNextStoryBtn');
    var profileStoryCloseBtn = document.getElementById('profileCloseStoryViewer');
    var profileStoryIndex = 0;
    var profileStoryTimer = null;

    function clearProfileStoryTimer() {
        if (profileStoryTimer) {
            clearInterval(profileStoryTimer);
            profileStoryTimer = null;
        }
    }

    function closeProfileStoryViewer() {
        clearProfileStoryTimer();
        if (profileStoryViewerAudio) {
            profileStoryViewerAudio.pause();
            profileStoryViewerAudio.currentTime = 0;
            profileStoryViewerAudio.removeAttribute('src');
            profileStoryViewerAudio.load();
        }
        if (profileStoryViewerProgress) {
            profileStoryViewerProgress.style.width = '0%';
        }
        if (profileStoryViewerEl) {
            profileStoryViewerEl.style.display = 'none';
        }
    }

    function showProfileStoryAt(index) {
        if (!Array.isArray(profileStoryItems) || !profileStoryItems.length) {
            return;
        }

        if (index < 0) {
            index = profileStoryItems.length - 1;
        }
        if (index >= profileStoryItems.length) {
            index = 0;
        }
        profileStoryIndex = index;

        var story = profileStoryItems[index] || {};
        var scale = parseFloat(story.scale || 1);

        if (profileStoryViewerImage) {
            profileStoryViewerImage.src = story.image || '';
            profileStoryViewerImage.style.transform = 'scale(' + (isNaN(scale) ? 1 : scale) + ')';
        }
        if (profileStoryViewerCaption) {
            profileStoryViewerCaption.textContent = story.caption || '';
            profileStoryViewerCaption.style.color = story.textColor || '#ffffff';
        }
        if (profileStoryViewerMusic) {
            profileStoryViewerMusic.textContent = story.musicName ? ('♫ ' + story.musicName) : '';
        }
        if (profileStoryViewerTime) {
            profileStoryViewerTime.textContent = formatProfileStoryTime(story.createdAt || '');
        }

        if (profileStoryViewerAudio) {
            profileStoryViewerAudio.pause();
            profileStoryViewerAudio.currentTime = 0;
            if (story.musicPath) {
                profileStoryViewerAudio.src = story.musicPath;
                profileStoryViewerAudio.load();
                profileStoryViewerAudio.play().catch(function () {});
            } else {
                profileStoryViewerAudio.removeAttribute('src');
                profileStoryViewerAudio.load();
            }
        }

        clearProfileStoryTimer();
        var percent = 0;
        if (profileStoryViewerProgress) {
            profileStoryViewerProgress.style.width = '0%';
        }
        profileStoryTimer = setInterval(function () {
            percent += 2;
            if (profileStoryViewerProgress) {
                profileStoryViewerProgress.style.width = Math.min(100, percent) + '%';
            }
            if (percent >= 100) {
                clearProfileStoryTimer();
                showProfileStoryAt(profileStoryIndex + 1);
            }
        }, 100);
    }

    function openProfileStoryViewer() {
        if (!profileStoryViewerEl || !Array.isArray(profileStoryItems) || !profileStoryItems.length) {
            return;
        }

        profileStoryViewerEl.style.display = 'block';
        showProfileStoryAt(profileStoryIndex);
    }

    function normalizeAssetUrl(url) {
        if (!url) {
            return '';
        }
        return String(url).split('?')[0];
    }

    function applyCoverImage(coverUrl) {
        if (!coverUrl) {
            return;
        }

        var coverEl = document.querySelector('.pp-cover');
        if (coverEl) {
            coverEl.style.backgroundImage = 'url("' + coverUrl + '")';
            coverEl.style.backgroundSize = 'cover';
            coverEl.style.backgroundPosition = 'center';
        }

        currentCoverUrl = normalizeAssetUrl(coverUrl);
    }

    function applyAvatarEverywhere(avatarUrl) {
        if (!avatarUrl) {
            return;
        }

        var bustedAvatarUrl = avatarUrl + (avatarUrl.indexOf('?') === -1 ? '?t=' : '&t=') + Date.now();
        var normalizedNew = normalizeAssetUrl(avatarUrl);
        var normalizedOld = normalizeAssetUrl(currentAvatarUrl);

        document.querySelectorAll('img[data-current-user-avatar="1"]').forEach(function (img) {
            img.src = bustedAvatarUrl;
        });

        if (normalizedOld) {
            document.querySelectorAll('img[src]').forEach(function (img) {
                var src = img.getAttribute('src') || '';
                if (normalizeAssetUrl(src) === normalizedOld) {
                    img.src = bustedAvatarUrl;
                }
            });
        }

        currentAvatarUrl = normalizedNew;
    }

    function uploadProfileImages(avatarFile, coverFile) {
        if (!avatarFile && !coverFile) {
            return Promise.reject(new Error('Không có tệp nào để upload.'));
        }

        if (typeof axios === 'undefined') {
            return Promise.reject(new Error('Thiếu thư viện Axios.'));
        }

        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        var formData = new FormData();

        if (avatarFile) {
            formData.append('avatar', avatarFile);
        }
        if (coverFile) {
            formData.append('cover_image', coverFile);
        }
        if (!Number.isNaN(profileUserId)) {
            formData.append('profile_id', String(profileUserId));
        }

        return axios.post('/profile/update-images', formData, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : '',
                'Content-Type': 'multipart/form-data'
            }
        }).then(function (response) {
            var payload = response && response.data ? response.data : {};
            var data = payload.data || {};

            if (!payload.ok) {
                throw new Error(payload.message || 'Cập nhật ảnh thất bại.');
            }

            if (data.cover_url) {
                applyCoverImage(data.cover_url);
            }
            if (data.avatar_url) {
                applyAvatarEverywhere(data.avatar_url);
            }

            return payload;
        });
    }

    var postForm = document.getElementById('profileCreatePostForm');
    var postAlert = document.getElementById('profilePostAlert');
    var submitBtn = document.getElementById('profilePostSubmitBtn');
    var openComposerBtn = document.getElementById('profileOpenPostComposerBtn');
    var quickVideoBtn = document.getElementById('profileQuickVideoBtn');
    var quickMediaBtn = document.getElementById('profileQuickMediaBtn');
    var quickFeelingBtn = document.getElementById('profileQuickFeelingBtn');
    var modalEl = document.getElementById('profilePostModal');
    var editorEl = document.getElementById('profileEditor');
    var hiddenInput = document.getElementById('profilePostContentInput');
    var mediaInput = document.getElementById('profilePostMedia');
    var clearMediaBtn = document.getElementById('profileClearMediaBtn');
    var uploadFeedback = document.getElementById('profilePostUploadFeedback');
    var progressWrap = document.getElementById('profilePostUploadProgressWrap');
    var progressBar = document.getElementById('profilePostUploadProgressBar');
    var uploadedMediaPathInput = document.getElementById('profileUploadedMediaPathInput');
    var uploadedMediaTypeInput = document.getElementById('profileUploadedMediaTypeInput');
    var videoForm = document.getElementById('profileCreateVideoForm');
    var videoSubmitBtn = document.getElementById('profileVideoSubmitBtn');
    var videoEditorEl = document.getElementById('profileVideoEditor');
    var videoHiddenInput = document.getElementById('profileVideoContentInput');
    var videoMediaInput = document.getElementById('profileVideoMedia');
    var videoPreview = document.getElementById('profileVideoComposerPreviewVideo');
    var videoPreviewWrap = document.getElementById('profileVideoMediaPreviewContainer');
    var videoPreviewClearBtn = document.getElementById('profileVideoPreviewClearBtn');
    var videoUploadFeedback = document.getElementById('profileVideoUploadFeedback');
    var videoProgressWrap = document.getElementById('profileVideoUploadProgressWrap');
    var videoProgressBar = document.getElementById('profileVideoUploadProgressBar');
    var videoUploadedMediaPathInput = document.getElementById('profileVideoUploadedMediaPathInput');
    var videoUploadedMediaTypeInput = document.getElementById('profileVideoUploadedMediaTypeInput');
    var videoModalEl = document.getElementById('profileVideoModal');
    var coverModalEl = document.getElementById('profileCoverModal');
    var avatarModalEl = document.getElementById('profileAvatarModal');
    var coverDropzone = document.getElementById('profileCoverDropzone');
    var coverInput = document.getElementById('coverInput');
    var coverCropImage = document.getElementById('profileCoverCropImage');
    var coverSaveBtn = document.getElementById('profileCoverSaveBtn');
    var coverGallery = document.getElementById('profileCoverGallery');
    var coverPreview = document.querySelector('.pp-cover');
    var coverCropper = null;
    var coverCurrentObjectUrl = null;
    var coverCurrentSource = '';
    var avatarDropzone = document.getElementById('profileAvatarDropzone');
    var avatarInput = document.getElementById('profileAvatarInput');
    var avatarCropImage = document.getElementById('profileAvatarCropImage');
    var avatarSaveBtn = document.getElementById('profileAvatarSaveBtn');
    var avatarGallery = document.getElementById('profileAvatarGallery');
    var avatarPreview = document.querySelector('.pp-avatar-wrap img');
    var composerAvatarPreview = document.querySelector('.pp-profile-composer img.rounded-circle');
    var avatarZoomInput = document.getElementById('profileAvatarZoom');
    var avatarResetBtn = document.getElementById('profileAvatarResetBtn');
    var avatarCropper = null;
    var avatarCurrentObjectUrl = null;
    var avatarZoomValue = 1;
    var accountModalEl = document.getElementById('accountSettingsModal');
    var introModalEl = document.getElementById('profileIntroEditModal');
    var accountSaveBtn = document.getElementById('profileAccountSaveBtn');
    var accountFirstNameInput = document.getElementById('accountFirstNameInput');
    var accountLastNameInput = document.getElementById('accountLastNameInput');
    var accountEmailInput = document.getElementById('accountEmailInput');
    var accountPhoneInput = document.getElementById('accountPhoneInput');
    var accountBirthDateInput = document.getElementById('accountBirthDateInput');
    var accountGenderInput = document.getElementById('accountGenderInput');
    var accountCurrentPasswordInput = document.getElementById('accountCurrentPasswordInput');
    var accountNewPasswordInput = document.getElementById('accountNewPasswordInput');
    var accountConfirmPasswordInput = document.getElementById('accountConfirmPasswordInput');
    var introSaveBtn = document.getElementById('profileIntroSaveBtn');
    var introBioInput = document.getElementById('profileBioInput');
    var introWorkInput = document.getElementById('profileWorkInput');
    var introEducationInput = document.getElementById('profileEducationInput');
    var introLivingInput = document.getElementById('profileLivingInput');
    var introFromInput = document.getElementById('profileFromInput');
    var introRelationshipInput = document.getElementById('profileRelationshipInput');
    var emojiBtn = document.getElementById('profileEmojiBtn');
    var emojiFallbackPanel = document.getElementById('profileEmojiFallbackPanel');
    var emojiFallbackList = document.getElementById('profileEmojiFallbackList');
    var feelingQuickBar = document.getElementById('profileFeelingQuickBar');
    var feelingQuickList = document.getElementById('profileFeelingQuickList');

    var privacyState = {
        profilePost: 'public',
        profileVideo: 'public',
        edit: 'public'
    };

    var privacyMeta = {
        public: { label: 'Cong khai', icon: 'fas fa-globe-asia' },
        friends: { label: 'Ban be', icon: 'fas fa-user-friends' },
        private: { label: 'Chi minh toi', icon: 'fas fa-lock' }
    };

    function applyComposerPrivacy(target, value) {
        var meta = privacyMeta[value] || privacyMeta.public;
        var iconEl = document.getElementById(target + 'PrivacyIcon');
        var labelEl = document.getElementById(target + 'PrivacyLabel');
        var inputEl = document.getElementById(target + 'PrivacyStatusInput');

        privacyState[target] = value;

        if (iconEl) {
            iconEl.className = meta.icon + ' me-1';
        }
        if (labelEl) {
            labelEl.textContent = meta.label;
        }
        if (inputEl) {
            inputEl.value = value;
        }
    }

    Array.prototype.slice.call(document.querySelectorAll('[data-privacy-option]')).forEach(function (optionBtn) {
        if (optionBtn.dataset.privacyBound === '1') {
            return;
        }

        optionBtn.dataset.privacyBound = '1';
        optionBtn.addEventListener('click', function () {
            var target = optionBtn.getAttribute('data-privacy-target') || 'profilePost';
            var value = optionBtn.getAttribute('data-privacy-value') || 'public';
            applyComposerPrivacy(target, value);
        });
    });

    applyComposerPrivacy('profilePost', privacyState.profilePost);
    applyComposerPrivacy('profileVideo', privacyState.profileVideo);

    var privacyEditModalEl = document.getElementById('profilePostPrivacyEditModal');
    var privacyEditSaveBtn = document.getElementById('profilePostPrivacySaveBtn');
    var editingPostId = 0;

    function applyEditSelection(value) {
        privacyState.edit = value;
        Array.prototype.slice.call(document.querySelectorAll('[data-privacy-edit-option]')).forEach(function (btn) {
            var selected = (btn.getAttribute('data-privacy-value') || 'public') === value;
            btn.classList.toggle('btn-primary', selected);
            btn.classList.toggle('btn-outline-secondary', !selected);
        });
    }

    Array.prototype.slice.call(document.querySelectorAll('[data-open-privacy-modal]')).forEach(function (triggerBtn) {
        if (triggerBtn.dataset.privacyBound === '1') {
            return;
        }

        triggerBtn.dataset.privacyBound = '1';
        triggerBtn.addEventListener('click', function () {
            editingPostId = parseInt(triggerBtn.getAttribute('data-post-id') || '0', 10);
            var currentPrivacy = triggerBtn.getAttribute('data-current-privacy') || 'public';
            applyEditSelection(currentPrivacy);

            if (privacyEditModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(privacyEditModalEl).show();
            }
        });
    });

    Array.prototype.slice.call(document.querySelectorAll('[data-privacy-edit-option]')).forEach(function (btn) {
        if (btn.dataset.privacyBound === '1') {
            return;
        }

        btn.dataset.privacyBound = '1';
        btn.addEventListener('click', function () {
            applyEditSelection(btn.getAttribute('data-privacy-value') || 'public');
        });
    });

    if (privacyEditSaveBtn && !privacyEditSaveBtn.dataset.privacyBound) {
        privacyEditSaveBtn.dataset.privacyBound = '1';
        privacyEditSaveBtn.addEventListener('click', function () {
            if (editingPostId <= 0) {
                return;
            }

            var csrf = document.querySelector('meta[name="csrf-token"]');
            privacyEditSaveBtn.disabled = true;
            privacyEditSaveBtn.textContent = 'Đang lưu...';

            fetch('/api/posts/' + editingPostId + '/privacy', {
                method: 'PATCH',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : ''
                },
                body: JSON.stringify({
                    privacy_status: privacyState.edit,
                    profile_id: getProfileIdPayloadValue(),
                })
            })
                .then(function (response) {
                    return response.json().then(function (json) {
                        return { ok: response.ok, json: json };
                    }).catch(function () {
                        return { ok: response.ok, json: {} };
                    });
                })
                .then(function (result) {
                    if (!result.ok) {
                        var message = (result.json && result.json.message) ? result.json.message : 'Không cập nhật được quyền riêng tư.';
                        throw new Error(message);
                    }
                    window.location.reload();
                })
                .catch(function (error) {
                    window.alert(error && error.message ? error.message : 'Có lỗi xảy ra khi cập nhật quyền riêng tư.');
                })
                .finally(function () {
                    privacyEditSaveBtn.disabled = false;
                    privacyEditSaveBtn.textContent = 'Lưu thay đổi';
                });
        });
    }

    window.openAccountSettingsModal = function () {
        if (!accountModalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            return;
        }
        bootstrap.Modal.getOrCreateInstance(accountModalEl).show();
    };

    window.openProfileIntroModal = function () {
        if (!introModalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            return;
        }
        bootstrap.Modal.getOrCreateInstance(introModalEl).show();
    };

    function handleAccountSave() {
        var firstName = accountFirstNameInput ? accountFirstNameInput.value.trim() : '';
        var lastName = accountLastNameInput ? accountLastNameInput.value.trim() : '';
        var email = accountEmailInput ? accountEmailInput.value.trim() : '';
        var phone = accountPhoneInput ? accountPhoneInput.value.trim() : '';
        var birthDate = accountBirthDateInput ? accountBirthDateInput.value : '';
        var gender = accountGenderInput ? accountGenderInput.value : '';
        var currentPassword = accountCurrentPasswordInput ? accountCurrentPasswordInput.value : '';
        var newPassword = accountNewPasswordInput ? accountNewPasswordInput.value : '';
        var confirmPassword = accountConfirmPasswordInput ? accountConfirmPasswordInput.value : '';

        var payload = {
            first_name: firstName,
            last_name: lastName,
            email: email,
            phone: phone,
            birth_date: birthDate,
            gender: gender,
            current_password: currentPassword,
            new_password: newPassword,
            new_password_confirmation: confirmPassword,
            profile_id: getProfileIdPayloadValue(),
        };

        if (typeof axios === 'undefined') {
            window.alert('Thiếu thư viện Axios.');
            return;
        }

        var csrfToken = document.querySelector('meta[name="csrf-token"]');

        if (accountSaveBtn) {
            accountSaveBtn.disabled = true;
            accountSaveBtn.textContent = 'Đang lưu...';
        }

        axios.post('{{ route('profile.personalization.update-account') }}', payload, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : '',
                'Content-Type': 'application/json'
            }
        })
            .then(function (response) {
                if (!response || response.status !== 200) {
                    throw new Error('Cập nhật tài khoản thất bại.');
                }
                window.location.reload();
            })
            .catch(function (error) {
                var message = 'Không thể cập nhật tài khoản.';
                if (error && error.response && error.response.data && error.response.data.message) {
                    message = error.response.data.message;
                } else if (error && error.message) {
                    message = error.message;
                }
                window.alert(message);
            })
            .finally(function () {
                if (accountSaveBtn) {
                    accountSaveBtn.disabled = false;
                    accountSaveBtn.textContent = 'Lưu tài khoản';
                }
            });
    }

    function handleIntroSave() {
        var bio = introBioInput ? introBioInput.value.trim() : '';
        var work = introWorkInput ? introWorkInput.value.trim() : '';
        var education = introEducationInput ? introEducationInput.value.trim() : '';
        var location = introLivingInput ? introLivingInput.value.trim() : '';
        var hometown = introFromInput ? introFromInput.value.trim() : '';
        var relationship = introRelationshipInput ? introRelationshipInput.value : '';

        var payload = {
            bio: bio,
            work: work,
            education: education,
            location: location,
            hometown: hometown,
            relationship: relationship,
            profile_id: getProfileIdPayloadValue(),
        };

        if (typeof axios === 'undefined') {
            window.alert('Thieu thu vien Axios.');
            return;
        }

        var csrfToken = document.querySelector('meta[name="csrf-token"]');

        if (introSaveBtn) {
            introSaveBtn.disabled = true;
            introSaveBtn.textContent = 'Đang lưu...';
        }

        axios.post('{{ route('profile.personalization.update-intro') }}', payload, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : '',
                'Content-Type': 'application/json'
            }
        })
            .then(function (response) {
                if (!response || response.status !== 200) {
                    throw new Error('Cập nhật giới thiệu thất bại   .');
                }
                window.location.reload();
            })
            .catch(function (error) {
                var message = 'Không thể cập nhật giới thiệu.';
                if (error && error.response && error.response.data && error.response.data.message) {
                    message = error.response.data.message;
                } else if (error && error.message) {
                    message = error.message;
                }
                window.alert(message);
            })
            .finally(function () {
                if (introSaveBtn) {
                    introSaveBtn.disabled = false;
                    introSaveBtn.textContent = 'Lưu chi tiết';
                }
            });
    }

    if (accountSaveBtn && !accountSaveBtn.dataset.bound) {
        accountSaveBtn.dataset.bound = '1';
        accountSaveBtn.addEventListener('click', handleAccountSave);
    }

    if (introSaveBtn && !introSaveBtn.dataset.bound) {
        introSaveBtn.dataset.bound = '1';
        introSaveBtn.addEventListener('click', handleIntroSave);
    }

    if (!postForm || !hiddenInput || !editorEl) {
        return;
    }

    var videoQuill = null;
    if (typeof Quill !== 'undefined') {
        quill = new Quill('#profileEditor', {
            theme: 'snow',
            placeholder: 'Bạn đang nghĩ gì thế?',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'color': ['#000000', '#e91e63', '#9c27b0', '#3f51b5', '#00bcd4', '#4caf50', '#ffeb3b', '#ff9800'] }],
                ]
            }
        });
        quill.root.style.fontFamily = "'Quicksand', 'Segoe UI Emoji', 'Apple Color Emoji', 'Noto Color Emoji', sans-serif";
    window.profileQuillEditor = quill;

        if (videoEditorEl) {
            videoQuill = new Quill('#profileVideoEditor', {
                theme: 'snow',
                placeholder: 'Them mo ta cho video cua ban...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'color': ['#000000', '#e91e63', '#9c27b0', '#3f51b5', '#00bcd4', '#4caf50', '#ffeb3b', '#ff9800'] }],
                    ]
                }
            });
            videoQuill.root.style.fontFamily = "'Quicksand', 'Segoe UI Emoji', 'Apple Color Emoji', 'Noto Color Emoji', sans-serif";
        }

        if (typeof bindTrimListeners === 'function') {
            bindTrimListeners();
        }
        if (typeof bindRecorderButtonListeners === 'function') {
            bindRecorderButtonListeners();
        }
        if (typeof bindMusicListeners === 'function') {
            bindMusicListeners();
        }
        if (typeof bindEditorButtonListeners === 'function') {
            bindEditorButtonListeners();
        }
    }

    function openComposer(afterOpen) {
        if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            if (typeof afterOpen === 'function') {
                afterOpen();
            }
            return;
        }
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        if (typeof afterOpen === 'function') {
            setTimeout(afterOpen, 180);
        }
    }

    function showAlert(type, message) {
        if (!postAlert) {
            return;
        }
        postAlert.className = 'alert mb-3 alert-' + type;
        postAlert.textContent = message;
        postAlert.classList.remove('d-none');
    }

    function insertEmojiToEditor(emojiChar) {
        if (!emojiChar || !quill) {
            return;
        }
        quill.focus();
        var range = quill.getSelection(true) || { index: quill.getLength() };
        quill.insertText(range.index, emojiChar, 'user');
        quill.setSelection(range.index + emojiChar.length, 0, 'user');
    }

    if (emojiFallbackList && !emojiFallbackList.dataset.bound) {
        emojiFallbackList.dataset.bound = '1';
        emojiFallbackList.addEventListener('click', function (event) {
            var target = event.target.closest('[data-emoji]');
            if (!target) {
                return;
            }
            insertEmojiToEditor(target.getAttribute('data-emoji') || '');
        });
    }

    if (emojiBtn && !emojiBtn.dataset.bound) {
        emojiBtn.dataset.bound = '1';
        if (typeof EmojiButton !== 'undefined') {
            var picker = new EmojiButton({ position: 'top-start', autoHide: true });
            emojiBtn.addEventListener('click', function () { picker.togglePicker(emojiBtn); });
            picker.on('emoji', function (selection) {
                var emojiChar = typeof selection === 'string' ? selection : ((selection && (selection.emoji || selection.unicode)) || '');
                insertEmojiToEditor(emojiChar);
            });
        } else {
            emojiBtn.addEventListener('click', function () {
                if (emojiFallbackPanel) {
                    emojiFallbackPanel.classList.toggle('d-none');
                }
            });
        }
    }

    if (quickMediaBtn && !quickMediaBtn.dataset.bound) {
        quickMediaBtn.dataset.bound = '1';
        quickMediaBtn.addEventListener('click', function () {
            openComposer(function () {
                if (mediaInput) {
                    mediaInput.setAttribute('accept', 'image/*,video/mp4,video/webm,video/ogg,video/quicktime');
                    mediaInput.click();
                }
            });
        });
    }

    if (quickVideoBtn && !quickVideoBtn.dataset.bound) {
        quickVideoBtn.dataset.bound = '1';
    }

    if (quickFeelingBtn && !quickFeelingBtn.dataset.bound) {
        quickFeelingBtn.dataset.bound = '1';
        quickFeelingBtn.addEventListener('click', function () {
            openComposer(function () {
                if (feelingQuickBar) {
                    feelingQuickBar.classList.remove('d-none');
                }
            });
        });
    }

    var profileHeaderStoryBtn = document.getElementById('profileHeaderStoryBtn');
    if (profileHeaderStoryBtn && !profileHeaderStoryBtn.dataset.bound) {
        profileHeaderStoryBtn.dataset.bound = '1';
        profileHeaderStoryBtn.addEventListener('click', function() {
            var storyInput = document.getElementById('profileStoryInput');
            if (storyInput) {
                storyInput.click();
            }
        });
    }

    var profileAvatarStoryTrigger = document.getElementById('profileAvatarStoryTrigger');
    if (profileAvatarStoryTrigger && !profileAvatarStoryTrigger.dataset.storyBound) {
        profileAvatarStoryTrigger.dataset.storyBound = '1';
        profileAvatarStoryTrigger.addEventListener('click', function () {
            if (!Array.isArray(profileStoryItems) || !profileStoryItems.length) {
                return;
            }
            profileStoryIndex = 0;
            openProfileStoryViewer();
        });
    }

    if (profileStoryCloseBtn && !profileStoryCloseBtn.dataset.bound) {
        profileStoryCloseBtn.dataset.bound = '1';
        profileStoryCloseBtn.addEventListener('click', closeProfileStoryViewer);
    }

    if (profileStoryPrevBtn && !profileStoryPrevBtn.dataset.bound) {
        profileStoryPrevBtn.dataset.bound = '1';
        profileStoryPrevBtn.addEventListener('click', function () {
            showProfileStoryAt(profileStoryIndex - 1);
        });
    }

    if (profileStoryNextBtn && !profileStoryNextBtn.dataset.bound) {
        profileStoryNextBtn.dataset.bound = '1';
        profileStoryNextBtn.addEventListener('click', function () {
            showProfileStoryAt(profileStoryIndex + 1);
        });
    }

    if (profileStoryViewerEl && !profileStoryViewerEl.dataset.bound) {
        profileStoryViewerEl.dataset.bound = '1';
        profileStoryViewerEl.addEventListener('click', function (event) {
            if (event.target === profileStoryViewerEl) {
                closeProfileStoryViewer();
            }
        });
    }

    var profileQuickFeelingBtn = document.getElementById('profileQuickFeelingBtn');
    var profileFeelingQuickBar = document.getElementById('profileFeelingQuickBar');
    if (profileQuickFeelingBtn && !profileQuickFeelingBtn.dataset.bound) {
        profileQuickFeelingBtn.dataset.bound = '1';
        profileQuickFeelingBtn.addEventListener('click', function () {
            if (profileFeelingQuickBar) {
                profileFeelingQuickBar.classList.toggle('d-none');
            }
        });
    }

    var profileFeelingQuickList = document.getElementById('profileFeelingQuickList');
    if (profileFeelingQuickList && !profileFeelingQuickList.dataset.bound) {
        profileFeelingQuickList.dataset.bound = '1';
        profileFeelingQuickList.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-feeling]');
            const profileQuill = window.profileQuillEditor;
            if (!btn || !profileQuill) {
                return;
            }

            const feelingText = btn.getAttribute('data-feeling') || '';
            const prefix = 'Đang cảm thấy ' + feelingText + '. ';
            const currentText = (profileQuill.getText() || '').trim();

            profileQuill.focus();
            if (!currentText || currentText.indexOf('Đang cảm thấy ') !== 0) {
                profileQuill.insertText(0, prefix, 'user');
            }

            const range = profileQuill.getSelection(true) || { index: profileQuill.getLength() };
            profileQuill.setSelection(Math.max(range.index, prefix.length), 0, 'user');
        });
    }

    var profilePostMediaInput = document.getElementById('profilePostMedia');
    if (profilePostMediaInput && !profilePostMediaInput.dataset.feelingBound) {
        profilePostMediaInput.dataset.feelingBound = '1';
        profilePostMediaInput.addEventListener('change', function () {
            if (profileFeelingQuickBar) {
                profileFeelingQuickBar.classList.add('d-none');
            }
        });
    }

    if (openComposerBtn && !openComposerBtn.dataset.bound) {
        openComposerBtn.dataset.bound = '1';
        openComposerBtn.addEventListener('click', function () {
            if (feelingQuickBar) {
                feelingQuickBar.classList.add('d-none');
            }
        });
    }

    if (feelingQuickList && !feelingQuickList.dataset.bound) {
        feelingQuickList.dataset.bound = '1';
        feelingQuickList.addEventListener('click', function (event) {
            var btn = event.target.closest('[data-feeling]');
            if (!btn || !quill) {
                return;
            }
            var feelingText = btn.getAttribute('data-feeling') || '';
            var prefix = 'Đang cảm thấy ' + feelingText + '. ';
            var currentText = (quill.getText() || '').trim();
            quill.focus();
            if (!currentText || currentText.indexOf('Đang cảm thấy ') !== 0) {
                quill.insertText(0, prefix, 'user');
            }
            var range = quill.getSelection(true) || { index: quill.getLength() };
            quill.setSelection(Math.max(range.index, prefix.length), 0, 'user');
        });
    }

    function updateProgressBar(progressBarEl, percent) {
        if (!progressBarEl) {
            return;
        }
        var safePercent = Math.max(0, Math.min(100, Math.floor(percent)));
        progressBarEl.style.width = safePercent + '%';
        progressBarEl.setAttribute('aria-valuenow', String(safePercent));
        progressBarEl.textContent = safePercent + '%';
    }

    function smoothProgressTo(progressBarEl, targetPercent) {
        return new Promise(function (resolve) {
            if (!progressBarEl) {
                resolve();
                return;
            }
            var current = parseInt(progressBarEl.getAttribute('aria-valuenow') || '0', 10);
            var target = Math.max(current, Math.min(100, Math.floor(targetPercent)));
            if (target <= current) {
                resolve();
                return;
            }
            var value = current;
            var timer = setInterval(function () {
                value += 1;
                updateProgressBar(progressBarEl, value);
                if (value >= target) {
                    clearInterval(timer);
                    resolve();
                }
            }, 25);
        });
    }

    function previewMedia(input) {
        var container = document.getElementById('profilePostMediaPreviewContainer');
        var img = document.getElementById('profileImgPreview');
        var video = document.getElementById('profileVideoPreview');
        if (!container || !img || !video || !input.files || !input.files[0]) {
            return;
        }
        var file = input.files[0];
        var objectUrl = URL.createObjectURL(file);
        container.classList.remove('d-none');
        img.classList.add('d-none');
        video.classList.add('d-none');

        if (file.type.startsWith('video/')) {
            if (video.dataset.objectUrl) {
                URL.revokeObjectURL(video.dataset.objectUrl);
            }
            video.dataset.objectUrl = objectUrl;
            video.src = objectUrl;
            video.classList.remove('d-none');
            img.src = '#';
        } else {
            if (img.dataset.objectUrl) {
                URL.revokeObjectURL(img.dataset.objectUrl);
            }
            img.dataset.objectUrl = objectUrl;
            img.src = objectUrl;
            img.classList.remove('d-none');
            if (video.dataset.objectUrl) {
                URL.revokeObjectURL(video.dataset.objectUrl);
                delete video.dataset.objectUrl;
            }
            video.pause();
            video.removeAttribute('src');
            video.load();
        }
    }

    function clearMedia() {
        var container = document.getElementById('profilePostMediaPreviewContainer');
        var img = document.getElementById('profileImgPreview');
        var video = document.getElementById('profileVideoPreview');

        if (mediaInput) {
            mediaInput.value = '';
        }
        if (uploadedMediaPathInput) {
            uploadedMediaPathInput.value = '';
        }
        if (uploadedMediaTypeInput) {
            uploadedMediaTypeInput.value = '';
        }
        if (container) {
            container.classList.add('d-none');
        }
        if (uploadFeedback) {
            uploadFeedback.classList.add('d-none');
            uploadFeedback.classList.remove('text-danger');
            uploadFeedback.classList.add('text-muted');
            uploadFeedback.textContent = '';
        }
        if (progressWrap) {
            progressWrap.classList.add('d-none');
        }
        updateProgressBar(progressBar, 0);
        if (img) {
            if (img.dataset.objectUrl) {
                URL.revokeObjectURL(img.dataset.objectUrl);
                delete img.dataset.objectUrl;
            }
            img.src = '#';
            img.classList.add('d-none');
        }
        if (video) {
            if (video.dataset.objectUrl) {
                URL.revokeObjectURL(video.dataset.objectUrl);
                delete video.dataset.objectUrl;
            }
            video.pause();
            video.removeAttribute('src');
            video.load();
            video.classList.add('d-none');
        }
    }

    if (clearMediaBtn && !clearMediaBtn.dataset.bound) {
        clearMediaBtn.dataset.bound = '1';
        clearMediaBtn.addEventListener('click', clearMedia);
    }

    if (mediaInput && !mediaInput.dataset.bound) {
        mediaInput.dataset.bound = '1';
        mediaInput.addEventListener('change', function () {
            if (feelingQuickBar) {
                feelingQuickBar.classList.add('d-none');
            }
            if (emojiFallbackPanel) {
                emojiFallbackPanel.classList.add('d-none');
            }
            var file = mediaInput.files && mediaInput.files[0] ? mediaInput.files[0] : null;
            if (!file) {
                clearMedia();
                return;
            }
            previewMedia(mediaInput);

            var fileSizeMb = file.size / (1024 * 1024);
            if (fileSizeMb > MAX_MEDIA_SIZE_MB) {
                var overMb = fileSizeMb - MAX_MEDIA_SIZE_MB;
                if (uploadFeedback) {
                    uploadFeedback.classList.remove('d-none', 'text-muted');
                    uploadFeedback.classList.add('text-danger');
                    uploadFeedback.textContent = 'Video vượt giới hạn 2GB (vuot ' + overMb.toFixed(1) + 'MB).';
                }
                clearMedia();
                return;
            }

            if (uploadFeedback) {
                uploadFeedback.classList.remove('d-none', 'text-danger');
                uploadFeedback.classList.add('text-muted');
                uploadFeedback.textContent = 'Dung lượng tệp: ' + fileSizeMb.toFixed(1) + ' MB.';
            }
        });
    }

    async function fetchWithTimeout(url, options, timeoutMs) {
        if (typeof AbortController === 'undefined') {
            return fetch(url, options);
        }
        var controller = new AbortController();
        var timer = setTimeout(function () { controller.abort(); }, timeoutMs);
        try {
            var nextOptions = Object.assign({}, options, { signal: controller.signal });
            return await fetch(url, nextOptions);
        } finally {
            clearTimeout(timer);
        }
    }

    async function uploadFileInChunks(file, chunkUrl, completeUrl, csrfToken, uploadFeedbackEl, progressBarEl) {
        if (!chunkUrl || !completeUrl) {
            throw new Error('Thiếu cấu hình upload chunk trên form.');
        }

        var uploadId = Date.now().toString() + '_' + Math.random().toString(36).slice(2, 10);
        var totalChunks = Math.ceil(file.size / CHUNK_SIZE_BYTES);
        var uploadedChunkIndexes = new Set();

        async function uploadOneChunk(chunkIndex) {
            var start = chunkIndex * CHUNK_SIZE_BYTES;
            var end = Math.min(start + CHUNK_SIZE_BYTES, file.size);
            var blobChunk = file.slice(start, end);

            for (var attempt = 1; attempt <= 6; attempt++) {
                var formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('upload_id', uploadId);
                formData.append('chunk_index', String(chunkIndex));
                formData.append('total_chunks', String(totalChunks));
                formData.append('filename', file.name);
                formData.append('mime_type', file.type || 'application/octet-stream');
                formData.append('chunk', blobChunk, file.name + '.part');

                var response = null;
                try {
                    response = await fetchWithTimeout(chunkUrl, { method: 'POST', body: formData }, CHUNK_TIMEOUT_MS);
                } catch (error) {
                    response = null;
                }

                if (!response) {
                    if (attempt === 6) {
                        throw new Error('Không thể tải dữ liệu video lên server.');
                    }
                    await new Promise(function (resolve) { setTimeout(resolve, 300 * attempt); });
                    continue;
                }

                var responseData = null;
                try {
                    responseData = await response.json();
                } catch (error) {
                    responseData = null;
                }

                var ackOk = !!(response.ok && responseData && responseData.ok === true);
                var ackIndexMatches = responseData && typeof responseData.chunk_index === 'number'
                    ? responseData.chunk_index === chunkIndex
                    : true;

                if (!ackOk || !ackIndexMatches) {
                    if (attempt === 6) {
                        throw new Error('Server chưa xác nhận đủ dữ liệu video.');
                    }
                    await new Promise(function (resolve) { setTimeout(resolve, 300 * attempt); });
                    continue;
                }

                if (!uploadedChunkIndexes.has(chunkIndex)) {
                    uploadedChunkIndexes.add(chunkIndex);
                }

                var rawPercent = (uploadedChunkIndexes.size / totalChunks) * 95;
                var percent = Math.min(95, Math.max(0, Math.floor(rawPercent)));
                smoothProgressTo(progressBarEl, percent);
                if (uploadFeedbackEl) {
                    uploadFeedbackEl.classList.remove('d-none', 'text-danger');
                    uploadFeedbackEl.classList.add('text-muted');
                    uploadFeedbackEl.textContent = 'Dang tai len: ' + percent + '%';
                }
                return;
            }
        }

        for (var i = 0; i < totalChunks; i++) {
            await uploadOneChunk(i);
        }

        async function completeUploadRequest() {
            if (uploadFeedbackEl) {
                uploadFeedbackEl.classList.remove('d-none', 'text-danger');
                uploadFeedbackEl.classList.add('text-muted');
                uploadFeedbackEl.textContent = 'Đang ghép file trên server...';
            }

            var completeForm = new FormData();
            completeForm.append('_token', csrfToken);
            completeForm.append('upload_id', uploadId);
            completeForm.append('total_chunks', String(totalChunks));
            completeForm.append('filename', file.name);
            completeForm.append('mime_type', file.type || 'application/octet-stream');

            var completeResponse = null;
            try {
                completeResponse = await fetchWithTimeout(completeUrl, { method: 'POST', body: completeForm }, COMPLETE_TIMEOUT_MS);
            } catch (error) {
                completeResponse = null;
            }

            if (!completeResponse) {
                return { ok: false, data: { message: 'Server đang bản ghep video, đang thử lại...' } };
            }

            var completeData = null;
            try {
                completeData = await completeResponse.json();
            } catch (error) {
                completeData = null;
            }

            return { ok: completeResponse.ok, data: completeData };
        }

        for (var completeTry = 1; completeTry <= COMPLETE_RETRY_LIMIT; completeTry++) {
            var completeResult = await completeUploadRequest();
            var completeData = completeResult.data || {};

            if (completeResult.ok && completeData.media_path) {
                await smoothProgressTo(progressBarEl, 100);
                if (uploadFeedbackEl) {
                    uploadFeedbackEl.classList.remove('d-none', 'text-danger');
                    uploadFeedbackEl.classList.add('text-muted');
                    uploadFeedbackEl.textContent = 'Đã tải lên: 100%';
                }
                return completeData;
            }

            var missingChunks = Array.isArray(completeData.missing_chunks)
                ? completeData.missing_chunks
                : (typeof completeData.missing_chunk === 'number' ? [completeData.missing_chunk] : []);

            if (missingChunks.length === 0) {
                await new Promise(function (resolve) { setTimeout(resolve, 500 * completeTry); });
                continue;
            }

            if (uploadFeedbackEl) {
                uploadFeedbackEl.classList.remove('d-none', 'text-danger');
                uploadFeedbackEl.classList.add('text-muted');
                uploadFeedbackEl.textContent = 'Đang tự động tải bộ ' + missingChunks.length + ' phần còn thiếu...';
            }

            for (var m = 0; m < missingChunks.length; m++) {
                await uploadOneChunk(missingChunks[m]);
            }
        }

        throw new Error('Không thể hoàn tất upload video lúc này.');
    }

    postForm.addEventListener('submit', function (event) {
        event.preventDefault();

        if (submitBtn) {
            submitBtn.disabled = true;
        }

        if (postAlert) {
            postAlert.classList.add('d-none');
        }

        hiddenInput.value = quill ? quill.root.innerHTML : '';

        var csrf = document.querySelector('meta[name="csrf-token"]');
        var selectedFile = mediaInput && mediaInput.files && mediaInput.files[0] ? mediaInput.files[0] : null;
        var chunkUrl = postForm.getAttribute('data-chunk-url') || '';
        var completeUrl = postForm.getAttribute('data-complete-url') || '';
        var formData = new FormData(postForm);

        var runPostRequest = function () {
            return fetch(postForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : ''
                },
                body: formData
            });
        };

        var beforeSubmit = Promise.resolve();

        if (selectedFile) {
            if (uploadFeedback) {
                uploadFeedback.classList.remove('d-none', 'text-danger');
                uploadFeedback.classList.add('text-muted');
                uploadFeedback.textContent = 'Đang tải ảnh/video theo từng phần...';
            }
            if (progressWrap) {
                progressWrap.classList.remove('d-none');
            }
            updateProgressBar(progressBar, 0);

            beforeSubmit = uploadFileInChunks(selectedFile, chunkUrl, completeUrl, csrf ? csrf.getAttribute('content') : '', uploadFeedback, progressBar)
                .then(function (uploadResult) {
                    if (uploadedMediaPathInput) {
                        uploadedMediaPathInput.value = uploadResult.media_path || '';
                    }
                    if (uploadedMediaTypeInput) {
                        uploadedMediaTypeInput.value = uploadResult.media_type || '';
                    }
                    if (mediaInput) {
                        mediaInput.value = '';
                    }
                    formData = new FormData(postForm);
                });
        }

        beforeSubmit
            .then(function () { return runPostRequest(); })
            .then(function (response) {
                return response.json().then(function (json) {
                    return { ok: response.ok, status: response.status, json: json };
                }).catch(function () {
                    return { ok: response.ok, status: response.status, json: {} };
                });
            })
            .then(function (result) {
                if (!result.ok) {
                    var msg = (result.json && result.json.message) ? result.json.message : 'Đang bài thất bại. Vui lòng thử lại.';
                    if (result.json && result.json.errors) {
                        var firstKey = Object.keys(result.json.errors)[0];
                        if (firstKey && result.json.errors[firstKey] && result.json.errors[firstKey][0]) {
                            msg = result.json.errors[firstKey][0];
                        }
                    }
                    throw new Error(msg);
                }

                showAlert('success', 'Đang bài thành công. Đang tải lại feed...');
                if (progressWrap) {
                    progressWrap.classList.add('d-none');
                }
                window.setTimeout(function () {
                    window.location.reload();
                }, 500);
            })
            .catch(function (error) {
                showAlert('danger', error && error.message ? error.message : 'Có lỗi xảy ra khi đăng bài.');
                if (progressWrap) {
                    progressWrap.classList.add('d-none');
                }
            })
            .finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            });
    });

    function previewVideoMedia(input) {
        if (!videoPreviewWrap || !videoPreview || !input.files || !input.files[0]) {
            return;
        }

        var file = input.files[0];
        var objectUrl = URL.createObjectURL(file);
        videoPreviewWrap.classList.remove('d-none');

        if (videoPreview.dataset.objectUrl) {
            URL.revokeObjectURL(videoPreview.dataset.objectUrl);
        }

        videoPreview.dataset.objectUrl = objectUrl;
        videoPreview.src = objectUrl;
        videoPreview.style.display = 'block';
    }

    function clearVideoMedia() {
        if (videoMediaInput) {
            videoMediaInput.value = '';
        }
        if (videoUploadedMediaPathInput) {
            videoUploadedMediaPathInput.value = '';
        }
        if (videoUploadedMediaTypeInput) {
            videoUploadedMediaTypeInput.value = '';
        }
        if (videoPreviewWrap) {
            videoPreviewWrap.classList.add('d-none');
        }
        if (videoUploadFeedback) {
            videoUploadFeedback.classList.add('d-none');
            videoUploadFeedback.classList.remove('text-danger');
            videoUploadFeedback.classList.add('text-muted');
            videoUploadFeedback.textContent = '';
        }
        if (videoProgressWrap) {
            videoProgressWrap.classList.add('d-none');
        }
        updateProgressBar(videoProgressBar, 0);
        if (videoPreview) {
            if (videoPreview.dataset.objectUrl) {
                URL.revokeObjectURL(videoPreview.dataset.objectUrl);
                delete videoPreview.dataset.objectUrl;
            }
            videoPreview.pause();
            videoPreview.removeAttribute('src');
            videoPreview.load();
            videoPreview.style.display = 'none';
        }
    }

    if (videoPreviewClearBtn && !videoPreviewClearBtn.dataset.bound) {
        videoPreviewClearBtn.dataset.bound = '1';
        videoPreviewClearBtn.addEventListener('click', clearVideoMedia);
    }

    if (liveRecordOpenBtn && !liveRecordOpenBtn.dataset.bound) {
        liveRecordOpenBtn.dataset.bound = '1';
        liveRecordOpenBtn.addEventListener('click', function () {
            var liveRecorderPanel = document.getElementById('liveRecorderPanel');
            var liveEditorPanel = document.getElementById('liveEditorPanel');
            if (liveEditorPanel) {
                liveEditorPanel.classList.add('d-none');
            }
            if (liveRecorderPanel) {
                liveRecorderPanel.classList.remove('d-none');
            }
            initLivePreview().catch(function (error) {
                if (videoUploadFeedback) {
                    videoUploadFeedback.classList.remove('d-none', 'text-muted');
                    videoUploadFeedback.classList.add('text-danger');
                    videoUploadFeedback.textContent = error && error.message ? error.message : 'Khong the mo camera.';
                }
            });
        });
    }

    if (videoMediaInput && !videoMediaInput.dataset.bound) {
        videoMediaInput.dataset.bound = '1';
        videoMediaInput.addEventListener('change', function () {
            var file = videoMediaInput.files && videoMediaInput.files[0] ? videoMediaInput.files[0] : null;
            if (!file) {
                clearVideoMedia();
                return;
            }

            var fileSizeMb = file.size / (1024 * 1024);
            if (fileSizeMb > MAX_MEDIA_SIZE_MB) {
                var overMb = fileSizeMb - MAX_MEDIA_SIZE_MB;
                if (videoUploadFeedback) {
                    videoUploadFeedback.classList.remove('d-none', 'text-muted');
                    videoUploadFeedback.classList.add('text-danger');
                    videoUploadFeedback.textContent = 'Video vượt giới hạn 2GB (vượt ' + overMb.toFixed(1) + 'MB).';
                }
                clearVideoMedia();
                return;
            }

            previewVideoMedia(videoMediaInput);
            if (videoUploadFeedback) {
                videoUploadFeedback.classList.remove('d-none', 'text-danger');
                videoUploadFeedback.classList.add('text-muted');
                videoUploadFeedback.textContent = 'Dung lượng tep: ' + fileSizeMb.toFixed(1) + ' MB.';
            }
        });
    }

    if (videoForm && videoHiddenInput) {
        videoForm.addEventListener('submit', function (event) {
            event.preventDefault();

            videoHiddenInput.value = videoQuill ? videoQuill.root.innerHTML : '';

            if (videoSubmitBtn) {
                videoSubmitBtn.disabled = true;
                videoSubmitBtn.textContent = 'Đang đăng...';
            }

            var selectedFile = videoMediaInput && videoMediaInput.files && videoMediaInput.files[0] ? videoMediaInput.files[0] : null;
            var chunkUrl = videoForm.getAttribute('data-chunk-url') || '';
            var completeUrl = videoForm.getAttribute('data-complete-url') || '';
            var csrf = document.querySelector('meta[name="csrf-token"]');
            var formData = new FormData(videoForm);

            var beforeSubmit = Promise.resolve();

            if (selectedFile) {
                if (videoUploadFeedback) {
                    videoUploadFeedback.classList.remove('d-none', 'text-danger');
                    videoUploadFeedback.classList.add('text-muted');
                    videoUploadFeedback.textContent = 'Đang tải video...';
                }
                if (videoProgressWrap) {
                    videoProgressWrap.classList.remove('d-none');
                }
                updateProgressBar(videoProgressBar, 0);

                beforeSubmit = uploadFileInChunks(selectedFile, chunkUrl, completeUrl, csrf ? csrf.getAttribute('content') : '', videoUploadFeedback, videoProgressBar)
                    .then(function (uploadResult) {
                        if (videoUploadedMediaPathInput) {
                            videoUploadedMediaPathInput.value = uploadResult.media_path || '';
                        }
                        if (videoUploadedMediaTypeInput) {
                            videoUploadedMediaTypeInput.value = uploadResult.media_type || '';
                        }
                        if (videoMediaInput) {
                            videoMediaInput.value = '';
                        }
                        formData = new FormData(videoForm);
                    });
            }

            beforeSubmit
                .then(function () {
                    return fetch(videoForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : ''
                        },
                        body: formData
                    });
                })
                .then(function (response) {
                    return response.json().then(function (json) {
                        return { ok: response.ok, json: json };
                    }).catch(function () {
                        return { ok: response.ok, json: {} };
                    });
                })
                .then(function (result) {
                    if (!result.ok) {
                        var msg = (result.json && result.json.message) ? result.json.message : 'Dang video that bai.';
                        if (result.json && result.json.errors) {
                            var firstKey = Object.keys(result.json.errors)[0];
                            if (firstKey && result.json.errors[firstKey] && result.json.errors[firstKey][0]) {
                                msg = result.json.errors[firstKey][0];
                            }
                        }
                        throw new Error(msg);
                    }

                    showAlert('success', 'Đang video thành công. Đang tải lại feed...');
                    if (videoProgressWrap) {
                        videoProgressWrap.classList.add('d-none');
                    }
                    window.setTimeout(function () {
                        window.location.reload();
                    }, 500);
                })
                .catch(function (error) {
                    showAlert('danger', error && error.message ? error.message : 'Có lỗi xảy ra khi đăng video.');
                    if (videoProgressWrap) {
                        videoProgressWrap.classList.add('d-none');
                    }
                })
                .finally(function () {
                    if (videoSubmitBtn) {
                        videoSubmitBtn.disabled = false;
                        videoSubmitBtn.textContent = 'Đăng video';
                    }
                });
        });
    }

    if (videoModalEl && !videoModalEl.dataset.bound) {
        videoModalEl.dataset.bound = '1';
        videoModalEl.addEventListener('hidden.bs.modal', clearVideoMedia);
        videoModalEl.addEventListener('hidden.bs.modal', function () {
            var liveRecorderPanel = document.getElementById('liveRecorderPanel');
            var liveEditorPanel = document.getElementById('liveEditorPanel');
            if (liveRecorderPanel) {
                liveRecorderPanel.classList.add('d-none');
            }
            if (liveEditorPanel) {
                liveEditorPanel.classList.add('d-none');
            }
            if (typeof stopLivePreview === 'function') {
                stopLivePreview();
            }
        });
    }

    var coverChooseBtn = document.getElementById('profileCoverChooseBtn');

    function setCoverSourceFromUrl(sourceUrl) {
        if (!coverCropImage || !sourceUrl) {
            return;
        }

        coverCurrentSource = sourceUrl;
        coverCropImage.src = sourceUrl;

        if (coverCropper) {
            coverCropper.destroy();
            coverCropper = null;
        }

        coverCropper = new Cropper(coverCropImage, {
            aspectRatio: 16 / 9,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            background: false,
            responsive: true,
            guides: false,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });
    }

    function handleFileSelect(file) {
        if (!file || !file.type || !file.type.startsWith('image/')) {
            window.alert('Vui long chon tep anh hop le.');
            return;
        }

        clearCoverObjectUrl();
        coverCurrentObjectUrl = URL.createObjectURL(file);
        setCoverSourceFromUrl(coverCurrentObjectUrl);
    }

    function clearCoverObjectUrl() {
        if (coverCurrentObjectUrl) {
            URL.revokeObjectURL(coverCurrentObjectUrl);
            coverCurrentObjectUrl = null;
        }
    }

    if (coverDropzone && coverInput) {
        coverDropzone.addEventListener('click', function () {
            coverInput.click();
        });

        if (coverChooseBtn) {
            coverChooseBtn.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                coverInput.click();
            });
        }

        coverDropzone.addEventListener('dragover', function (event) {
            event.preventDefault();
            coverDropzone.classList.add('dragover');
        });

        coverDropzone.addEventListener('dragleave', function () {
            coverDropzone.classList.remove('dragover');
        });

        coverDropzone.addEventListener('drop', function (event) {
            event.preventDefault();
            coverDropzone.classList.remove('dragover');
            var file = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files[0] : null;
            handleFileSelect(file);
        });

        coverInput.addEventListener('change', function () {
            var file = coverInput.files && coverInput.files[0] ? coverInput.files[0] : null;
            handleFileSelect(file);
        });
    }

    if (coverGallery && !coverGallery.dataset.bound) {
        coverGallery.dataset.bound = '1';
        coverGallery.addEventListener('click', function (event) {
            var item = event.target.closest('[data-cover-gallery-item]');
            if (!item) {
                return;
            }

            coverGallery.querySelectorAll('[data-cover-gallery-item]').forEach(function (node) {
                node.classList.remove('active');
            });
            item.classList.add('active');

            clearCoverObjectUrl();
            setCoverSourceFromUrl(item.getAttribute('data-cover-url') || '');
        });
    }

    if (coverModalEl) {
        coverModalEl.addEventListener('hidden.bs.modal', function () {
            if (coverCropper) {
                coverCropper.destroy();
                coverCropper = null;
            }
            if (coverCropImage) {
                coverCropImage.removeAttribute('src');
            }
            clearCoverObjectUrl();
            coverCurrentSource = '';
            if (coverInput) {
                coverInput.value = '';
            }
        });
    }

    if (coverSaveBtn) {
        coverSaveBtn.addEventListener('click', function () {
            if (!coverCropper || !coverPreview) {
                return;
            }

            coverSaveBtn.disabled = true;
            coverSaveBtn.textContent = 'Dang luu...';
            var canvas = coverCropper.getCroppedCanvas({
                width: 1600,
                height: 900,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            if (!canvas) {
                coverSaveBtn.disabled = false;
                coverSaveBtn.textContent = 'Lưu';
                return;
            }

            canvas.toBlob(function (blob) {
                if (!blob) {
                    coverSaveBtn.disabled = false;
                    coverSaveBtn.textContent = 'Lưu';
                    window.alert('Không thể tạo dữ liệu ảnh bìa.');
                    return;
                }

                var file = new File([blob], 'cover.jpg', { type: 'image/jpeg' });
                uploadProfileImages(null, file)
                    .then(function () {
                        if (coverModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            bootstrap.Modal.getOrCreateInstance(coverModalEl).hide();
                        }
                    })
                    .catch(function (error) {
                        var message = 'Không thể cập nhật ảnh bìa.';
                        if (error && error.response && error.response.data && error.response.data.message) {
                            message = error.response.data.message;
                        } else if (error && error.message) {
                            message = error.message;
                        }
                        window.alert(message);
                    })
                    .finally(function () {
                        coverSaveBtn.disabled = false;
                        coverSaveBtn.textContent = 'Lưu';
                    });
            }, 'image/jpeg', 0.92);
        });
    }

    function setAvatarSource(sourceUrl) {
        if (!avatarCropImage || !sourceUrl) {
            return;
        }

        avatarCropImage.src = sourceUrl;

        if (avatarCropper) {
            avatarCropper.destroy();
            avatarCropper = null;
        }

        avatarCropper = new Cropper(avatarCropImage, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            background: false,
            responsive: true,
            guides: false,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            zoomOnWheel: true,
            zoomOnTouch: true,
            wheelZoomRatio: 0.08,
            ready: function () {
                avatarZoomValue = 1;
                if (avatarZoomInput) {
                    avatarZoomInput.value = '1';
                }
            }
        });
    }

    function openAvatarModalWithSource(sourceUrl) {
        if (!sourceUrl) {
            return;
        }

        if (avatarModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(avatarModalEl).show();
        }

        window.setTimeout(function () {
            setAvatarSource(sourceUrl);
        }, 150);
    }

    function clearAvatarObjectUrl() {
        if (avatarCurrentObjectUrl) {
            URL.revokeObjectURL(avatarCurrentObjectUrl);
            avatarCurrentObjectUrl = null;
        }
    }

    if (avatarDropzone && avatarInput) {
        avatarDropzone.addEventListener('click', function () {
            avatarInput.click();
        });

        avatarDropzone.addEventListener('dragover', function (event) {
            event.preventDefault();
            avatarDropzone.classList.add('dragover');
        });

        avatarDropzone.addEventListener('dragleave', function () {
            avatarDropzone.classList.remove('dragover');
        });

        avatarDropzone.addEventListener('drop', function (event) {
            event.preventDefault();
            avatarDropzone.classList.remove('dragover');

            var file = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files[0] : null;
            if (!file || !file.type.startsWith('image/')) {
                return;
            }

            clearAvatarObjectUrl();
            avatarCurrentObjectUrl = URL.createObjectURL(file);
            openAvatarModalWithSource(avatarCurrentObjectUrl);
        });

        avatarInput.addEventListener('change', function () {
            var file = avatarInput.files && avatarInput.files[0] ? avatarInput.files[0] : null;
            if (!file || !file.type.startsWith('image/')) {
                return;
            }

            clearAvatarObjectUrl();
            avatarCurrentObjectUrl = URL.createObjectURL(file);
            openAvatarModalWithSource(avatarCurrentObjectUrl);
        });
    }

    if (avatarGallery && !avatarGallery.dataset.bound) {
        avatarGallery.dataset.bound = '1';
        avatarGallery.addEventListener('click', function (event) {
            var item = event.target.closest('[data-avatar-gallery-item]');
            if (!item) {
                return;
            }

            avatarGallery.querySelectorAll('[data-avatar-gallery-item]').forEach(function (node) {
                node.classList.remove('active');
            });
            item.classList.add('active');

            openAvatarModalWithSource(item.getAttribute('data-avatar-url') || '');
        });
    }

    if (avatarZoomInput && !avatarZoomInput.dataset.bound) {
        avatarZoomInput.dataset.bound = '1';
        avatarZoomInput.addEventListener('input', function () {
            if (!avatarCropper) {
                return;
            }

            var nextZoom = parseFloat(avatarZoomInput.value || '1');
            if (!Number.isFinite(nextZoom) || nextZoom <= 0) {
                return;
            }

            var delta = nextZoom - avatarZoomValue;
            if (delta !== 0) {
                avatarCropper.zoom(delta);
                avatarZoomValue = nextZoom;
            }
        });
    }

    if (avatarResetBtn && !avatarResetBtn.dataset.bound) {
        avatarResetBtn.dataset.bound = '1';
        avatarResetBtn.addEventListener('click', function () {
            if (!avatarCropper) {
                return;
            }

            avatarCropper.reset();
            avatarCropper.zoomTo(1);
            avatarZoomValue = 1;
            if (avatarZoomInput) {
                avatarZoomInput.value = '1';
            }
        });
    }

    if (avatarModalEl) {
        avatarModalEl.addEventListener('hidden.bs.modal', function () {
            if (avatarCropper) {
                avatarCropper.destroy();
                avatarCropper = null;
            }
            if (avatarCropImage) {
                avatarCropImage.removeAttribute('src');
            }
            clearAvatarObjectUrl();
            avatarZoomValue = 1;
            if (avatarInput) {
                avatarInput.value = '';
            }
            if (avatarZoomInput) {
                avatarZoomInput.value = '1';
            }
        });
    }

    if (avatarSaveBtn) {
        avatarSaveBtn.addEventListener('click', function () {
            if (!avatarCropper || !avatarPreview) {
                return;
            }

            var canvas = avatarCropper.getCroppedCanvas({
                width: 800,
                height: 800,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            if (!canvas) {
                return;
            }

            avatarSaveBtn.disabled = true;
            avatarSaveBtn.textContent = 'Đang lưu...';
            canvas.toBlob(function (blob) {
                if (!blob) {
                    avatarSaveBtn.disabled = false;
                    avatarSaveBtn.textContent = 'Lưu';
                    window.alert('Không thể tạo dữ liệu ảnh đại diện.');
                    return;
                }

                var file = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
                uploadProfileImages(file, null)
                    .then(function () {
                        if (avatarModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            bootstrap.Modal.getOrCreateInstance(avatarModalEl).hide();
                        }
                    })
                .catch(function (error) {
                    window.alert(error && error.message ? error.message : 'Không thể cập nhật ảnh đại diện.');
                })
                .finally(function () {
                    avatarSaveBtn.disabled = false;
                    avatarSaveBtn.textContent = 'Lưu';
                });
            }, 'image/jpeg', 0.95);
        });
    }
});
</script>
@endpush
