@extends('layouts.app')

@section('content')
<div id="storiesRealtimeMeta"
    data-latest-story-id="{{ $latestStoryId ?? 0 }}"
    data-story-count="{{ $storyCount ?? 0 }}"
    data-snapshot-url="{{ route('stories.snapshot') }}"></div>

<!-- DÃƒY STORY (REEFS) -->
@include('components.story_bar')

<div style="max-width: 920px; margin-left: auto; margin-right: auto;">
    <!-- 2. Ã” ÄÄ‚NG BÃ€I VIáº¾T -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
                <img src="https://i.pravatar.cc/45?u=nhi" class="rounded-circle">
                <button
                    id="openPostComposerBtn"
                    type="button"
                    class="btn btn-light rounded-pill flex-grow-1 text-start text-muted px-3"
                    style="background-color: #f0f2f5;"
                    data-bs-toggle="modal"
                    data-bs-target="#postModal">
                    Bạn đang nghĩ gì?
                </button>

            </div>

<div class="modal fade" id="postModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <!-- Header -->
            <div class="modal-header border-bottom justify-content-center position-relative">
                <h5 class="fw-bold m-0">Tạo bài viết</h5>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"></button>
            </div>

            <form id="createPostForm" action="{{ route('post.store') }}" method="POST" enctype="multipart/form-data" data-chunk-url="{{ route('post.upload.chunk') }}" data-complete-url="{{ route('post.upload.complete') }}">
                @csrf
                <div class="modal-body">
                    <!-- ThÃ´ng tin User -->
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://i.pravatar.cc/45?u=nhi" class="rounded-circle me-2 border" width="45" height="45">
                        <div>
                            <div class="fw-bold">Nhi Lê</div>
                            <div class="dropdown mt-1">
                                <button id="postPrivacyBtn" type="button" class="badge bg-light text-dark fw-normal border d-inline-flex align-items-center" style="font-size: 12px;" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i id="postPrivacyIcon" class="fas fa-globe-asia me-1"></i>
                                    <span id="postPrivacyLabel">Công khai</span>
                                </button>
                                <ul class="dropdown-menu shadow-sm">
                                    <li><button type="button" class="dropdown-item" data-privacy-option data-privacy-target="post" data-privacy-value="public">Công khai</button></li>
                                    <li><button type="button" class="dropdown-item" data-privacy-option data-privacy-target="post" data-privacy-value="friends">Bạn bè</button></li>
                                    <li><button type="button" class="dropdown-item" data-privacy-option data-privacy-target="post" data-privacy-value="private">Chỉ mình tôi</button></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- VÃ¹ng soáº¡n tháº£o Quill -->
                    <div id="editor" style="min-height: 150px;"></div>
                    <input type="hidden" name="content" id="postContentInput">
                    <input type="hidden" name="uploaded_media_path" id="postUploadedMediaPathInput">
                    <input type="hidden" name="uploaded_media_type" id="postUploadedMediaTypeInput">
                    <input type="hidden" name="privacy_status" id="postPrivacyStatusInput" value="public">
                    
                    <!-- VÃ¹ng hiá»ƒn thá»‹ áº£nh/video sau khi chá»n -->
                    <div id="postMediaPreviewContainer" class="mt-2 position-relative d-none">
                        <div class="border rounded p-1 bg-light">
                            <img id="imgPreview" src="#" class="img-fluid rounded d-block d-none mx-auto" style="max-height: 320px; max-width: 100%; width: auto; object-fit: contain;">
                            <video id="videoPreview" class="rounded d-block d-none mx-auto" style="max-height: 320px; max-width: 100%; width: auto; object-fit: contain;" controls preload="metadata" playsinline></video>
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 bg-white shadow" onclick="clearMedia()"></button>
                        </div>
                    </div>

                    <audio id="postLiveMusicPlayer" preload="auto" style="display:none;" playsinline></audio>

                    <div id="postUploadFeedback" class="small mt-2 text-muted d-none"></div>
                    <div id="postUploadProgressWrap" class="progress mt-2 d-none" style="height: 10px;">
                        <div id="postUploadProgressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">0%</div>
                    </div>

                    <!-- Thanh cÃ´ng cá»¥ -->
                    <div class="border rounded-3 p-2 mt-3 d-flex align-items-center justify-content-between">
                        <span class="small fw-bold ms-2">Thêm vào bài viết của bạn</span>
                        <div class="d-flex gap-1">
                            <!-- NÃºt chá»n áº£nh -->
                            <label for="postMedia" class="btn btn-light btn-sm rounded-circle p-2" title="áº¢nh/Video">
                                <i class="fas fa-images text-success fs-5"></i>
                            </label>
                            <input type="file" id="postMedia" name="media" class="d-none" accept="image/*,video/mp4,video/webm,video/ogg,video/quicktime" onchange="previewMedia(this)">
                            
                            <!-- NÃºt Emoji -->
                            <button type="button" id="emojiBtn" class="btn btn-light btn-sm rounded-circle p-2" title="Cáº£m xÃºc">
                                <i class="fas fa-smile text-warning fs-5"></i>
                            </button>
                        </div>
                    </div>

                    <div id="emojiFallbackPanel" class="border rounded-3 mt-2 p-2 d-none" style="max-height: 130px; overflow-y: auto;">
                        <div class="d-flex flex-wrap gap-1" id="emojiFallbackList">
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ˜€">ðŸ˜€</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ˜">ðŸ˜</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ˜‚">ðŸ˜‚</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ¤£">ðŸ¤£</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ˜Š">ðŸ˜Š</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ˜">ðŸ˜</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ˜˜">ðŸ˜˜</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ¤—">ðŸ¤—</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ¤©">ðŸ¤©</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ˜Ž">ðŸ˜Ž</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ¥°">ðŸ¥°</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ˜¢">ðŸ˜¢</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ˜­">ðŸ˜­</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ˜¡">ðŸ˜¡</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ‘">ðŸ‘</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ‘">ðŸ‘</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ™">ðŸ™</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸ”¥">ðŸ”¥</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="â¤ï¸">â¤ï¸</button>
                            <button type="button" class="btn btn-light btn-sm" data-emoji="ðŸŽ‰">ðŸŽ‰</button>
                        </div>
                    </div>

                    <div id="feelingQuickBar" class="border rounded-3 mt-2 p-2 d-none">
                        <div class="small text-muted mb-2">Bạn đang cảm thấy thế nào?</div>
                        <div class="d-flex flex-wrap gap-2" id="feelingQuickList">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-feeling="ðŸ˜Š vui váº»">ðŸ˜Š Vui váº»</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-feeling="ðŸ¥° háº¡nh phÃºc">ðŸ¥° Háº¡nh phÃºc</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-feeling="ðŸ˜Œ bÃ¬nh yÃªn">ðŸ˜Œ BÃ¬nh yÃªn</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-feeling="ðŸ¤© hÃ o há»©ng">ðŸ¤© HÃ o há»©ng</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-feeling="ðŸ˜¢ buá»“n">ðŸ˜¢ Buá»“n</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-feeling="ðŸ˜¡ bá»±c mÃ¬nh">ðŸ˜¡ Bá»±c mÃ¬nh</button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-3">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Há»§y</button>
                    <button id="postSubmitBtn" type="submit" class="btn fw-bold text-white py-2" 
                            style="background: linear-gradient(45deg, #ff85a2, #ba62ff); border-radius: 8px;">
                        ÄÄƒng bÃ i viáº¿t
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header border-bottom justify-content-center position-relative">
                    <h5 class="fw-bold m-0">Quay hoáº·c táº£i video</h5>
                    <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"></button>
                </div>

                <form id="createVideoForm" action="{{ route('post.store') }}" method="POST" enctype="multipart/form-data" data-chunk-url="{{ route('post.upload.chunk') }}" data-complete-url="{{ route('post.upload.complete') }}">
                    @csrf
                    <input type="hidden" name="post_type" value="video">

                    <div class="modal-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://i.pravatar.cc/45?u=nhi" class="rounded-circle me-2 border" width="45" height="45">
                            <div>
                                <div class="fw-bold">Nhi Lê</div>
                                <div class="dropdown mt-1">
                                    <button id="videoPrivacyBtn" type="button" class="badge bg-light text-dark fw-normal border d-inline-flex align-items-center" style="font-size: 12px;" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i id="videoPrivacyIcon" class="fas fa-globe-asia me-1"></i>
                                        <span id="videoPrivacyLabel">Công khai</span>
                                    </button>
                                    <ul class="dropdown-menu shadow-sm">
                                        <li><button type="button" class="dropdown-item" data-privacy-option data-privacy-target="video" data-privacy-value="public">Công khai</button></li>
                                        <li><button type="button" class="dropdown-item" data-privacy-option data-privacy-target="video" data-privacy-value="friends">Bạn bè</button></li>
                                        <li><button type="button" class="dropdown-item" data-privacy-option data-privacy-target="video" data-privacy-value="private">Chỉ mình tôi</button></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div id="videoEditor" style="min-height: 100px;" class="mb-3 border rounded p-2 bg-light"></div>
                        <input type="hidden" name="content" id="videoContentInput">
                        <input type="hidden" name="uploaded_media_path" id="videoUploadedMediaPathInput">
                        <input type="hidden" name="uploaded_media_type" id="videoUploadedMediaTypeInput">
                        <input type="hidden" name="privacy_status" id="videoPrivacyStatusInput" value="public">

                        <div id="videoMediaPreviewContainer" class="mt-2 position-relative d-none">
                            <div class="border rounded p-1 bg-light">
                                <video id="videoComposerPreviewVideo" class="rounded d-block mx-auto" style="max-height: 320px; max-width: 100%; width: auto; object-fit: contain; display: none;" controls preload="metadata" playsinline></video>
                                <button type="button" id="videoPreviewClearBtn" class="btn-close position-absolute top-0 end-0 m-2 bg-white shadow"></button>
                            </div>
                        </div>

                        <div id="liveRecorderPanel" class="border rounded-3 mt-2 p-2 d-none bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small">Quay video</span>
                                <span id="liveRecordTimer" class="badge bg-secondary">00:00</span>
                            </div>

                            <video id="livePreviewVideo" class="w-100 rounded" style="max-height: 280px; object-fit: cover; background:#111;" autoplay muted playsinline></video>

                            <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
                                <label for="liveDurationSelect" class="small text-muted mb-0">Thời gian tối đa</label>
                                <select id="liveDurationSelect" class="form-select form-select-sm" style="width: 120px;">
                                    <option value="120">2 phÃºt</option>
                                    <option value="180" selected>3 phÃºt</option>
                                    <option value="300">5 phÃºt</option>
                                </select>

                                <select id="liveMusicSelect" class="form-select form-select-sm" style="min-width: 180px; max-width: 220px;">
                                    <option value="">KhÃ´ng dÃ¹ng nháº¡c</option>
                                    @foreach(($hotSongs ?? collect()) as $song)
                                        <option value="{{ $song->playable_url }}">{{ $song->title }}</option>
                                    @endforeach
                                </select>

                                <label for="liveMusicVolume" class="small text-muted mb-0">Nháº¡c</label>
                                <input id="liveMusicVolume" type="range" min="0" max="1" step="0.05" value="0.35" style="width: 100px;">
                                <span id="liveMusicTime" class="small text-muted">00:00 / 00:00</span>
                            </div>

                            <div class="d-flex gap-2 mt-2">
                                <button type="button" id="liveStartBtn" class="btn btn-danger btn-sm">Báº¯t Ä‘áº§u quay</button>
                                <button type="button" id="liveStopBtn" class="btn btn-secondary btn-sm" disabled>Dá»«ng</button>
                                <button type="button" id="liveRetakeBtn" class="btn btn-warning btn-sm d-none">Ghi hÃ¬nh láº¡i</button>
                                <button type="button" id="liveUseBtn" class="btn btn-primary btn-sm" disabled>DÃ¹ng video nÃ y</button>
                                <button type="button" id="liveCloseBtn" class="btn btn-light btn-sm">ÄÃ³ng</button>
                            </div>

                            <audio id="liveMusicPlayer" preload="auto" style="display:none;" playsinline></audio>
                        </div>

                        <div id="liveEditorPanel" class="border rounded-3 mt-2 p-2 d-none bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small">Chá»‰nh video trÆ°á»›c khi Ä‘Äƒng</span>
                                <span id="liveEditDuration" class="badge bg-primary">Cáº¯t: 0s â†’ 0s</span>
                            </div>

                            <video id="liveEditPreviewVideo" class="w-100 rounded" style="max-height: 280px; object-fit: cover; background:#111;" controls playsinline></video>

                            <div class="row g-2 mt-2">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-dark mb-1">Báº¯t Ä‘áº§u cáº¯t</label>
                                    <input id="liveTrimStart" type="range" min="0" max="0" value="0" class="form-range" style="accent-color:#0d6efd;">
                                    <div id="liveTrimStartLabel" class="small fw-semibold text-primary">0s</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-dark mb-1">Káº¿t thÃºc cáº¯t</label>
                                    <input id="liveTrimEnd" type="range" min="0" max="0" value="0" class="form-range" style="accent-color:#198754;">
                                    <div id="liveTrimEndLabel" class="small fw-semibold text-success">0s</div>
                                </div>
                            </div>

                            <div class="mt-2">
                                <div class="small text-muted mb-1">VÃ¹ng Ä‘Ã£ cáº¯t</div>
                                <div id="liveTrimTrack" class="position-relative rounded-3 overflow-hidden" style="height: 14px; background: linear-gradient(90deg, #dee2e6 0%, #dee2e6 100%);">
                                    <div id="liveTrimTrackSelected" class="position-absolute top-0 bottom-0" style="left: 0%; width: 100%; background: linear-gradient(90deg, rgba(13,110,253,.9), rgba(25,135,84,.9));"></div>
                                    <div id="liveTrimTrackStartMarker" class="position-absolute top-0 bottom-0" style="width: 2px; left: 0%; background: #0d6efd;"></div>
                                    <div id="liveTrimTrackEndMarker" class="position-absolute top-0 bottom-0" style="width: 2px; left: 100%; background: #198754;"></div>
                                </div>
                            </div>

                            <div id="liveTrimSummary" class="small text-muted mt-1">Äá»™ dÃ i Ä‘Ã£ cáº¯t: 0s</div>

                            <div class="d-flex gap-2 mt-2 flex-wrap">
                                <button type="button" id="liveApplyEditBtn" class="btn btn-primary btn-sm">Ãp dá»¥ng chá»‰nh sá»­a</button>
                                <button type="button" id="liveBackToRecordBtn" class="btn btn-light btn-sm">Quay láº¡i</button>
                                <button type="button" id="liveUseEditedBtn" class="btn btn-success btn-sm" disabled>DÃ¹ng video Ä‘Ã£ chá»‰nh</button>
                            </div>
                        </div>

                        <div id="videoUploadFeedback" class="small mt-2 text-muted d-none"></div>
                        <div id="videoUploadProgressWrap" class="progress mt-2 d-none" style="height: 10px;">
                            <div id="videoUploadProgressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">0%</div>
                        </div>

                        <div class="border rounded-3 p-2 mt-3 d-flex align-items-center justify-content-between">
                            <span class="small fw-bold ms-2">ThÃªm video</span>
                            <div class="d-flex gap-1">
                                <label for="videoMedia" class="btn btn-light btn-sm rounded-circle p-2" title="Táº£i video">
                                    <i class="fas fa-video text-danger fs-5"></i>
                                </label>
                                <input type="file" id="videoMedia" name="media" class="d-none" accept="video/mp4,video/webm,video/ogg,video/quicktime">
                                <button type="button" id="liveRecordOpenBtn" class="btn btn-light btn-sm rounded-circle p-2" title="Quay video trá»±c tiáº¿p">
                                    <i class="fas fa-circle text-danger fs-5"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-3">
                        <button id="videoSubmitBtn" type="submit" class="btn w-100 fw-bold text-white py-2" style="background: linear-gradient(45deg, #ff85a2, #ba62ff); border-radius: 8px;">ÄÄƒng video</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<hr>
            <div class="d-flex justify-content-around">
                <button type="button" class="btn btn-light text-muted fw-bold border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#videoModal"><i class="fas fa-video text-danger me-2"></i>Quay video</button>
                <button id="quickMediaBtn" type="button" class="btn btn-light text-muted fw-bold border-0 bg-transparent"><i class="fas fa-images text-success me-2"></i>áº¢nh/video</button>
                <button id="quickFeelingBtn" type="button" class="btn btn-light text-muted fw-bold border-0 bg-transparent"><i class="fas fa-smile text-warning me-2"></i>Cáº£m xÃºc</button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div id="postSuccessAlert" class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif

    @php
        $viewErrors = (isset($errors) && $errors instanceof \Illuminate\Support\ViewErrorBag)
            ? $errors
            : new \Illuminate\Support\ViewErrorBag();
    @endphp

    @if($viewErrors->any())
        <div class="mb-2">
            @foreach($viewErrors->all() as $error)
                <div class="alert alert-danger shadow-sm mb-2 {{ $error === 'BÃ i viáº¿t cáº§n ná»™i dung hoáº·c áº£nh/video.' ? 'post-empty-media-alert' : '' }}">
                    {{ $error }}
                </div>
            @endforeach
        </div>
    @endif

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
        @endphp
        <div class="card mb-2 shadow-sm">
            <div class="card-body position-relative">
            <div class="d-flex align-items-start mb-1 pe-5">
                    <img src="https://i.pravatar.cc/45?u={{ $post->user_id }}" class="rounded-circle me-2" width="42" height="42" alt="avatar">
                    <div>
                        @php
                            $postUser = optional($post->user);
                            $fullName = trim((string) ($postUser->First_name ?? '') . ' ' . (string) ($postUser->Last_name ?? ''));
                            $displayName = $fullName !== ''
                                ? $fullName
                                : ((string) ($postUser->name ?? $postUser->Email ?? $postUser->email ?? 'NgÆ°á»i dÃ¹ng'));
                        @endphp
                        <div class="fw-bold">{{ $displayName }}</div>
                        <small class="text-muted d-inline-flex align-items-center gap-2">
                            <span>{{ optional($post->created_at)->diffForHumans() }}</span>
                            <i class="fas {{ $postPrivacyIcon }}" title="{{ $postPrivacyLabel }}" aria-label="{{ $postPrivacyLabel }}"></i>
                        </small>
                    </div>

                    <div class="dropdown ms-auto position-absolute top-0 end-0 mt-2 me-2">
                        <button class="btn btn-light btn-sm rounded-circle border-0 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="TÃ¹y chá»n bÃ i viáº¿t">
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
                                    Chinh sua quyen rieng tu
                                </button>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('post.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Báº¡n cÃ³ muá»‘n xÃ³a bÃ i viáº¿t nÃ y khÃ´ng?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">XÃ³a video / bÃ i viáº¿t</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                @if($hasPostContent)
                    <div class="mb-2 post-content">{!! $rawPostContent !!}</div>
                @endif

                @php
                    $postMediaType = $post->media_type ?? $post->post_type ?? null;
                    $postMediaPath = $post->media_path ?? $post->image_url ?? null;
                    $postMediaUrl = $postMediaPath
                        ? (\Illuminate\Support\Str::startsWith((string) $postMediaPath, ['http://', 'https://'])
                            ? $postMediaPath
                            : asset('storage/' . ltrim((string) $postMediaPath, '/')))
                        : null;
                @endphp

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
        </div>
    @empty
        <div class="text-center text-muted py-2">ChÆ°a cÃ³ bÃ i viáº¿t nÃ o.</div>
    @endforelse

    <div class="modal fade" id="postPrivacyEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm" style="border-radius: 14px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Chinh sua quyen rieng tu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column gap-2" id="postPrivacyEditOptions">
                        <button type="button" class="btn btn-outline-secondary text-start" data-privacy-edit-option data-privacy-value="public">ðŸŒ Cong khai</button>
                        <button type="button" class="btn btn-outline-secondary text-start" data-privacy-edit-option data-privacy-value="friends">ðŸ‘¥ Ban be</button>
                        <button type="button" class="btn btn-outline-secondary text-start" data-privacy-edit-option data-privacy-value="private">ðŸ”’ Chi minh toi</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Huy</button>
                    <button type="button" class="btn btn-primary" id="postPrivacySaveBtn">Luu thay doi</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    #editor .ql-editor,
    .post-content {
        font-family: 'Quicksand', 'Segoe UI Emoji', 'Apple Color Emoji', 'Noto Color Emoji', sans-serif;
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var meta = document.getElementById('storiesRealtimeMeta');
    if (!meta) {
        return;
    }

    var snapshotUrl = meta.getAttribute('data-snapshot-url') || '';
    if (!snapshotUrl) {
        return;
    }

    var latestStoryId = parseInt(meta.getAttribute('data-latest-story-id') || '0', 10);
    var storyCount = parseInt(meta.getAttribute('data-story-count') || '0', 10);

    setInterval(function () {
        if (document.hidden) {
            return;
        }

        fetch(snapshotUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            var nextLatestStoryId = parseInt((data && data.latestStoryId) || '0', 10);
            var nextStoryCount = parseInt((data && data.storyCount) || '0', 10);

            if (nextLatestStoryId !== latestStoryId || nextStoryCount !== storyCount) {
                window.location.reload();
            }
        })
        .catch(function () {
            // KhÃ´ng cháº·n tráº£i nghiá»‡m náº¿u endpoint snapshot lá»—i táº¡m thá»i.
        });
    }, 10000);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var successAlert = document.getElementById('postSuccessAlert');
    if (!successAlert) {
        successAlert = null;
    }

    setTimeout(function () {
        successAlert.style.transition = 'opacity 0.3s ease';
        successAlert.style.opacity = '0';

        setTimeout(function () {
            if (successAlert && successAlert.parentNode) {
                successAlert.parentNode.removeChild(successAlert);
            }
        }, 300);
    }, 2000);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var emptyMediaAlert = document.querySelector('.post-empty-media-alert');
    if (!emptyMediaAlert) {
        return;
    }

    setTimeout(function () {
        emptyMediaAlert.style.transition = 'opacity 0.3s ease';
        emptyMediaAlert.style.opacity = '0';

        setTimeout(function () {
            if (emptyMediaAlert && emptyMediaAlert.parentNode) {
                emptyMediaAlert.parentNode.removeChild(emptyMediaAlert);
            }
        }, 300);
    }, 2000);
});
</script>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
    let quill = null;
    const MAX_MEDIA_SIZE_MB = 2048;
    const CHUNK_SIZE_BYTES = 2 * 1024 * 1024;
    const COMPLETE_RETRY_LIMIT = 12;
    const CHUNK_TIMEOUT_MS = 180000;
    const COMPLETE_TIMEOUT_MS = 300000;

    function initComposer() {
        const editorEl = document.getElementById('editor');
        if (!editorEl || typeof Quill === 'undefined') {
            return;
        }

        if (!quill) {
            quill = new Quill('#editor', {
                theme: 'snow',
                placeholder: 'Báº¡n Ä‘ang nghÄ© gÃ¬ tháº¿?',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'color': ['#000000', '#e91e63', '#9c27b0', '#3f51b5', '#00bcd4', '#4caf50', '#ffeb3b', '#ff9800'] }],
                    ]
                }
            });

            quill.root.style.fontFamily = "'Quicksand', 'Segoe UI Emoji', 'Apple Color Emoji', 'Noto Color Emoji', sans-serif";
        }

        const emojiBtn = document.querySelector('#emojiBtn');
        const emojiFallbackPanel = document.getElementById('emojiFallbackPanel');
        const emojiFallbackList = document.getElementById('emojiFallbackList');

        function insertEmojiToEditor(emojiChar) {
            if (!emojiChar || !quill) {
                return;
            }

            quill.focus();
            const range = quill.getSelection(true) || { index: quill.getLength() };
            quill.insertText(range.index, emojiChar, 'user');
            quill.setSelection(range.index + emojiChar.length, 0, 'user');
        }

        if (emojiFallbackList && !emojiFallbackList.dataset.bound) {
            emojiFallbackList.dataset.bound = '1';
            emojiFallbackList.addEventListener('click', function (event) {
                const target = event.target.closest('[data-emoji]');
                if (!target) {
                    return;
                }
                insertEmojiToEditor(target.getAttribute('data-emoji') || '');
            });
        }

        if (emojiBtn && !emojiBtn.dataset.bound) {
            emojiBtn.dataset.bound = '1';

            if (typeof EmojiButton !== 'undefined') {
                const picker = new EmojiButton({
                    position: 'top-start',
                    autoHide: true
                });

                emojiBtn.addEventListener('click', function () {
                    picker.togglePicker(emojiBtn);
                });

                picker.on('emoji', function (selection) {
                    const emojiChar = typeof selection === 'string'
                        ? selection
                        : ((selection && (selection.emoji || selection.unicode)) || '');
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

        const form = document.getElementById('createPostForm');
        const hiddenInput = document.getElementById('postContentInput');
        const submitBtn = document.getElementById('postSubmitBtn');
        const mediaInput = document.getElementById('postMedia');
        const uploadFeedback = document.getElementById('postUploadFeedback');
        const progressWrap = document.getElementById('postUploadProgressWrap');
        const progressBar = document.getElementById('postUploadProgressBar');
        const quickMediaBtn = document.getElementById('quickMediaBtn');
        const quickFeelingBtn = document.getElementById('quickFeelingBtn');
        const feelingQuickBar = document.getElementById('feelingQuickBar');
        const feelingQuickList = document.getElementById('feelingQuickList');
        const postModalEl = document.getElementById('postModal');

        function openComposer(afterOpen) {
            if (!postModalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                if (typeof afterOpen === 'function') {
                    afterOpen();
                }
                return;
            }

            const modal = bootstrap.Modal.getOrCreateInstance(postModalEl);
            modal.show();

            if (typeof afterOpen === 'function') {
                setTimeout(afterOpen, 180);
            }
        }

        function formatLiveTime(totalSeconds) {
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        }

        function formatMusicTime(value) {
            const safe = Math.max(0, Math.floor(value || 0));
            const minutes = Math.floor(safe / 60);
            const seconds = safe % 60;
            return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        }

        function setTrimLabel(value, labelEl) {
            if (labelEl) {
                labelEl.textContent = Math.max(0, Math.floor(value || 0)) + 's';
            }
        }

        function updateTrimUI(durationSeconds) {
            const total = Math.max(0, Math.floor(durationSeconds || 0));
            if (liveTrimStart) {
                liveTrimStart.min = '0';
                liveTrimStart.max = String(total);
                liveTrimStart.value = '0';
            }
            if (liveTrimEnd) {
                liveTrimEnd.min = '0';
                liveTrimEnd.max = String(total);
                liveTrimEnd.value = String(total);
            }
            if (liveEditDuration) {
                liveEditDuration.textContent = 'Cáº¯t: 0s â†’ ' + total + 's';
            }
            if (liveTrimSummary) {
                liveTrimSummary.textContent = 'Äá»™ dÃ i Ä‘Ã£ cáº¯t: ' + total + 's';
            }
            setTrimLabel(0, liveTrimStartLabel);
            setTrimLabel(total, liveTrimEndLabel);
            updateTrimTrack();
        }

        function updateTrimSummary() {
            if (!liveTrimSummary || !liveTrimStart || !liveTrimEnd) {
                return;
            }

            const startValue = parseInt(liveTrimStart.value || '0', 10);
            const endValue = parseInt(liveTrimEnd.value || '0', 10);
            const trimmedLength = Math.max(0, endValue - startValue);
            liveTrimSummary.textContent = 'Äá»™ dÃ i Ä‘Ã£ cáº¯t: ' + trimmedLength + 's';
        }

        function updateTrimTrack() {
            if (!liveTrimTrackSelected || !liveTrimTrackStartMarker || !liveTrimTrackEndMarker || !liveTrimStart || !liveTrimEnd) {
                return;
            }

            const total = Math.max(1, liveRecordingDuration || parseInt(liveTrimEnd.max || '1', 10) || 1);
            const startValue = Math.max(0, parseInt(liveTrimStart.value || '0', 10));
            const endValue = Math.max(startValue + 1, parseInt(liveTrimEnd.value || String(total), 10));
            const startPercent = Math.min(100, (startValue / total) * 100);
            const endPercent = Math.min(100, (endValue / total) * 100);

            liveTrimTrackSelected.style.left = startPercent + '%';
            liveTrimTrackSelected.style.width = Math.max(0, endPercent - startPercent) + '%';
            liveTrimTrackStartMarker.style.left = startPercent + '%';
            liveTrimTrackEndMarker.style.left = endPercent + '%';
        }

        async function renderEditedVideoFromRecordedBlob() {
            if (!liveRecordedBlob) {
                throw new Error('ChÆ°a cÃ³ video Ä‘á»ƒ chá»‰nh sá»­a.');
            }

            const startSec = Math.max(0, parseInt((liveTrimStart && liveTrimStart.value) || '0', 10));
            const endSec = Math.max(startSec + 1, parseInt((liveTrimEnd && liveTrimEnd.value) || String(liveRecordingDuration || startSec + 1), 10));
            const sourceUrl = URL.createObjectURL(liveRecordedBlob);
            const sourceVideo = document.createElement('video');
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const audioDestination = audioContext.createMediaStreamDestination();
            const audioSourceNode = audioContext.createMediaElementSource(sourceVideo);
            audioSourceNode.connect(audioDestination);

            sourceVideo.src = sourceUrl;
            sourceVideo.crossOrigin = 'anonymous';
            sourceVideo.muted = false;
            sourceVideo.volume = 1;
            sourceVideo.playsInline = true;
            sourceVideo.preload = 'auto';

            await new Promise(function (resolve, reject) {
                sourceVideo.onloadedmetadata = function () {
                    canvas.width = Math.max(2, sourceVideo.videoWidth || 720);
                    canvas.height = Math.max(2, sourceVideo.videoHeight || 1280);
                    resolve();
                };
                sourceVideo.onerror = function () {
                    reject(new Error('KhÃ´ng Ä‘á»c Ä‘Æ°á»£c video Ä‘Ã£ quay Ä‘á»ƒ chá»‰nh sá»­a.'));
                };
            });

            const outStream = canvas.captureStream(30);
            const editedAudioTrack = audioDestination.stream.getAudioTracks()[0];
            if (editedAudioTrack) {
                outStream.addTrack(editedAudioTrack);
            }
            const mimeType = MediaRecorder.isTypeSupported('video/webm;codecs=vp8,opus')
                ? 'video/webm;codecs=vp8,opus'
                : 'video/webm';
            const recorder = new MediaRecorder(outStream, { mimeType: mimeType, videoBitsPerSecond: 2500000 });
            const chunks = [];
            let rafId = null;

            recorder.ondataavailable = function (event) {
                if (event.data && event.data.size > 0) {
                    chunks.push(event.data);
                }
            };

            const finished = new Promise(function (resolve, reject) {
                recorder.onstop = function () {
                    if (rafId) {
                        cancelAnimationFrame(rafId);
                    }
                    URL.revokeObjectURL(sourceUrl);
                    audioContext.close().catch(function () {});
                    const blob = new Blob(chunks, { type: 'video/webm' });
                    resolve(blob);
                };
                recorder.onerror = function () {
                    if (rafId) {
                        cancelAnimationFrame(rafId);
                    }
                    URL.revokeObjectURL(sourceUrl);
                    audioContext.close().catch(function () {});
                    reject(new Error('KhÃ´ng thá»ƒ xuáº¥t video Ä‘Ã£ chá»‰nh.'));
                };
            });

            function drawFrame() {
                if (sourceVideo.ended || sourceVideo.currentTime >= endSec) {
                    if (recorder.state !== 'inactive') {
                        recorder.stop();
                    }
                    return;
                }

                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(sourceVideo, 0, 0, canvas.width, canvas.height);

                rafId = requestAnimationFrame(drawFrame);
            }

            await new Promise(function (resolve) {
                sourceVideo.onseeked = function () {
                    resolve();
                };
                sourceVideo.currentTime = startSec;
            });

            audioContext.resume().catch(function () {});
            sourceVideo.play().catch(function () {});
            recorder.start(1000);
            drawFrame();

            const blob = await finished;
            return blob;
        }

        async function mergeSelectedMusicIntoRecordedBlob(videoBlob, musicUrl, musicVolume, onProgress) {
            if (!videoBlob || !musicUrl) {
                return videoBlob;
            }
            return videoBlob;
        }

        function stopMusicTicker() {}

        function stopRecordingMusicPlayback() {}

        function retakeLiveVideo() {}

        function updateMusicTimeUI() {}

        function startMusicTicker() {}

        async function playSelectedMusicPreview() {}

        function resetLiveTimer() {}

        function stopLiveTimers() {}

        async function initLivePreview() {}

        function stopLivePreview() {}

        function showLiveEditorWithBlob(blob) {}

        async function startLiveRecording() {}

        function stopLiveRecordingManually() {}

        function useRecordedLiveVideo() {}

        // Quick Actions for Newsfeed (keep these!)
        if (quickMediaBtn && !quickMediaBtn.dataset.bound) {
            quickMediaBtn.dataset.bound = '1';
            quickMediaBtn.addEventListener('click', function () {
                openComposer(function () {
                    if (mediaInput) {
                        mediaInput.click();
                    }
                });
            });
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

        if (feelingQuickList && !feelingQuickList.dataset.bound) {
            feelingQuickList.dataset.bound = '1';
            feelingQuickList.addEventListener('click', function (event) {
                const btn = event.target.closest('[data-feeling]');
                if (!btn || !quill) {
                    return;
                }

                const feelingText = btn.getAttribute('data-feeling') || '';
                const prefix = 'Äang cáº£m tháº¥y ' + feelingText + '. ';
                const currentText = (quill.getText() || '').trim();

                quill.focus();
                if (!currentText || currentText.indexOf('Äang cáº£m tháº¥y ') !== 0) {
                    quill.insertText(0, prefix, 'user');
                }

                const range = quill.getSelection(true) || { index: quill.getLength() };
                quill.setSelection(Math.max(range.index, prefix.length), 0, 'user');
            });
        }

        if (mediaInput && uploadFeedback && !mediaInput.dataset.bound) {
            mediaInput.dataset.bound = '1';
            mediaInput.addEventListener('change', function () {
                if (feelingQuickBar) {
                    feelingQuickBar.classList.add('d-none');
                }
                if (emojiFallbackPanel) {
                    emojiFallbackPanel.classList.add('d-none');
                }

                const file = mediaInput.files && mediaInput.files[0] ? mediaInput.files[0] : null;
                if (!file) {
                    clearMedia();
                    uploadFeedback.classList.add('d-none');
                    uploadFeedback.textContent = '';
                    return;
                }

                // LuÃ´n preview báº±ng listener JS Ä‘á»ƒ trÃ¡nh phá»¥ thuá»™c inline onchange.
                previewMedia(mediaInput);

                const fileSizeMb = file.size / (1024 * 1024);
                if (fileSizeMb > MAX_MEDIA_SIZE_MB) {
                    const overMb = fileSizeMb - MAX_MEDIA_SIZE_MB;
                    uploadFeedback.classList.remove('d-none', 'text-muted');
                    uploadFeedback.classList.add('text-danger');
                    uploadFeedback.textContent = 'Video cá»§a báº¡n vÆ°á»£t giá»›i háº¡n 2GB (vÆ°á»£t ' + overMb.toFixed(1) + 'MB) nÃªn khÃ´ng Ä‘Äƒng Ä‘Æ°á»£c.';
                    clearMedia();
                    return;
                }

                uploadFeedback.classList.remove('d-none', 'text-danger');
                uploadFeedback.classList.add('text-muted');
                uploadFeedback.textContent = 'Dung lÆ°á»£ng tá»‡p: ' + fileSizeMb.toFixed(1) + ' MB.';
            });
        }

        if (form && hiddenInput) {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                hiddenInput.value = quill ? quill.root.innerHTML : '';

                if (form.dataset.processingUpload === '1') {
                    return;
                }

                form.dataset.processingUpload = '1';

                const selectedFile = mediaInput && mediaInput.files && mediaInput.files[0] ? mediaInput.files[0] : null;
                const chunkUrl = form.getAttribute('data-chunk-url') || '';
                const completeUrl = form.getAttribute('data-complete-url') || '';
                const uploadedMediaPathInput = document.getElementById('postUploadedMediaPathInput');
                const uploadedMediaTypeInput = document.getElementById('postUploadedMediaTypeInput');

                try {
                    if (selectedFile) {
                        if (uploadFeedback) {
                            uploadFeedback.classList.remove('d-none', 'text-danger');
                            uploadFeedback.classList.add('text-muted');
                            uploadFeedback.textContent = 'Äang táº£i áº£nh/video theo tá»«ng pháº§n...';
                        }
                        if (progressWrap && progressBar) {
                            progressWrap.classList.remove('d-none');
                            updateProgressBar(progressBar, 0);
                        }

                        const uploadResult = await uploadFileInChunks(
                            selectedFile,
                            chunkUrl,
                            completeUrl,
                            '{{ csrf_token() }}',
                            progressBar,
                            uploadFeedback
                        );

                        if (uploadedMediaPathInput) {
                            uploadedMediaPathInput.value = uploadResult.media_path || '';
                        }
                        if (uploadedMediaTypeInput) {
                            uploadedMediaTypeInput.value = uploadResult.media_type || '';
                        }

                        // TrÃ¡nh submit láº¡i file lá»›n láº§n 2.
                        mediaInput.value = '';
                    }

                    if (uploadFeedback) {
                        uploadFeedback.classList.remove('d-none', 'text-danger');
                        uploadFeedback.classList.add('text-muted');
                        uploadFeedback.textContent = 'Äang táº¡o bÃ i viáº¿t...';
                    }

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Äang Ä‘Äƒng...';
                    }

                    form.dataset.processingUpload = '0';
                    form.submit();
                } catch (error) {
                    form.dataset.processingUpload = '0';

                    if (uploadFeedback) {
                        uploadFeedback.classList.remove('d-none', 'text-muted');
                        uploadFeedback.classList.add('text-danger');
                        uploadFeedback.textContent = error && error.message ? error.message : 'Táº£i video tháº¥t báº¡i. Vui lÃ²ng thá»­ láº¡i.';
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'ÄÄƒng bÃ i viáº¿t';
                    }
                    if (progressWrap) {
                        progressWrap.classList.add('d-none');
                    }
                }
            });
        }
    }

    function previewMedia(input) {
        const container = document.getElementById('postMediaPreviewContainer');
        const img = document.getElementById('imgPreview');
        const video = document.getElementById('videoPreview');

        if (!container || !img || !video || !input.files || !input.files[0]) {
            return;
        }

        const file = input.files[0];
        const objectUrl = URL.createObjectURL(file);
        container.classList.remove('d-none');

        // Reset hiá»ƒn thá»‹ Ä‘á»ƒ trÃ¡nh tráº¡ng thÃ¡i cÅ© bá»‹ káº¹t.
        img.classList.add('d-none');
        video.classList.add('d-none');

        if (file.type.startsWith('video/')) {
            if (video.dataset.objectUrl) {
                URL.revokeObjectURL(video.dataset.objectUrl);
            }
            video.dataset.objectUrl = objectUrl;
            video.src = objectUrl;
            video.classList.remove('d-none');
            img.classList.add('d-none');
            img.src = '#';
        } else {
            if (img.dataset.objectUrl) {
                URL.revokeObjectURL(img.dataset.objectUrl);
            }
            img.dataset.objectUrl = objectUrl;
            img.src = objectUrl;
            img.classList.remove('d-none');
            video.classList.add('d-none');
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
        const mediaInput = document.getElementById('postMedia');
        const container = document.getElementById('postMediaPreviewContainer');
        const img = document.getElementById('imgPreview');
        const video = document.getElementById('videoPreview');
        const uploadFeedback = document.getElementById('postUploadFeedback');
        const progressWrap = document.getElementById('postUploadProgressWrap');
        const progressBar = document.getElementById('postUploadProgressBar');

        if (mediaInput) {
            mediaInput.value = '';
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
        if (progressBar) {
            updateProgressBar(progressBar, 0);
        }
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

    function updateProgressBar(progressBar, percent) {
        const safePercent = Math.max(0, Math.min(100, Math.floor(percent)));
        progressBar.style.width = safePercent + '%';
        progressBar.setAttribute('aria-valuenow', String(safePercent));
        progressBar.textContent = safePercent + '%';
    }

    function smoothProgressTo(progressBar, targetPercent) {
        return new Promise(function (resolve) {
            const current = parseInt(progressBar.getAttribute('aria-valuenow') || '0', 10);
            const target = Math.max(current, Math.min(100, Math.floor(targetPercent)));

            if (target <= current) {
                resolve();
                return;
            }

            let value = current;
            const timer = setInterval(function () {
                value += 1;
                updateProgressBar(progressBar, value);
                if (value >= target) {
                    clearInterval(timer);
                    resolve();
                }
            }, 25);
        });
    }

    function getAdaptiveConcurrency() {
        // Safe mode: luÃ´n upload tuáº§n tá»± Ä‘á»ƒ trÃ¡nh missing chunk trÃªn mÃ´i trÆ°á»ng máº¡ng/host khÃ´ng á»•n Ä‘á»‹nh.
        return 1;
    }

    async function fetchWithTimeout(url, options, timeoutMs) {
        if (typeof AbortController === 'undefined') {
            return fetch(url, options);
        }

        const controller = new AbortController();
        const timer = setTimeout(function () {
            controller.abort();
        }, timeoutMs);

        try {
            const nextOptions = Object.assign({}, options, { signal: controller.signal });
            return await fetch(url, nextOptions);
        } finally {
            clearTimeout(timer);
        }
    }

    async function uploadFileInChunks(file, chunkUrl, completeUrl, csrfToken, progressBar, uploadFeedback) {
        if (!chunkUrl || !completeUrl) {
            throw new Error('Thiáº¿u cáº¥u hÃ¬nh upload chunk trÃªn form.');
        }

        const uploadId = Date.now().toString() + '_' + Math.random().toString(36).slice(2, 10);
        const totalChunks = Math.ceil(file.size / CHUNK_SIZE_BYTES);
        const concurrency = Math.max(1, Math.min(getAdaptiveConcurrency(), totalChunks));

        let nextChunkIndex = 0;
        const uploadedChunkIndexes = new Set();

        async function uploadOneChunk(chunkIndex) {
            const start = chunkIndex * CHUNK_SIZE_BYTES;
            const end = Math.min(start + CHUNK_SIZE_BYTES, file.size);
            const blobChunk = file.slice(start, end);

            for (let attempt = 1; attempt <= 6; attempt++) {
                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('upload_id', uploadId);
                formData.append('chunk_index', String(chunkIndex));
                formData.append('total_chunks', String(totalChunks));
                formData.append('filename', file.name);
                formData.append('mime_type', file.type || 'application/octet-stream');
                formData.append('chunk', blobChunk, file.name + '.part');

                let response = null;
                try {
                    response = await fetchWithTimeout(chunkUrl, {
                        method: 'POST',
                        body: formData,
                    }, CHUNK_TIMEOUT_MS);
                } catch (error) {
                    response = null;
                }

                if (!response) {
                    if (attempt === 6) {
                        throw new Error('KhÃ´ng thá»ƒ táº£i dá»¯ liá»‡u video lÃªn server. Vui lÃ²ng thá»­ láº¡i.');
                    }

                    await new Promise(function (resolve) {
                        setTimeout(resolve, 300 * attempt);
                    });
                    continue;
                }

                let responseData = null;
                try {
                    responseData = await response.json();
                } catch (error) {
                    responseData = null;
                }

                const ackOk = !!(response.ok && responseData && responseData.ok === true);
                const ackIndexMatches = responseData && typeof responseData.chunk_index === 'number'
                    ? responseData.chunk_index === chunkIndex
                    : true;

                if (!ackOk || !ackIndexMatches) {
                    if (attempt === 6) {
                        throw new Error('Server chÆ°a xÃ¡c nháº­n Ä‘á»§ dá»¯ liá»‡u video. Vui lÃ²ng thá»­ láº¡i.');
                    }

                    await new Promise(function (resolve) {
                        setTimeout(resolve, 300 * attempt);
                    });
                    continue;
                }

                if (!uploadedChunkIndexes.has(chunkIndex)) {
                    uploadedChunkIndexes.add(chunkIndex);
                }

                const rawPercent = (uploadedChunkIndexes.size / totalChunks) * 95;
                const percent = Math.min(95, Math.max(0, Math.floor(rawPercent)));
                if (progressBar) {
                    smoothProgressTo(progressBar, percent);
                }
                if (uploadFeedback) {
                    uploadFeedback.classList.remove('d-none', 'text-danger');
                    uploadFeedback.classList.add('text-muted');
                    uploadFeedback.textContent = 'Äang táº£i lÃªn: ' + percent + '%';
                }
                return;
            }
        }

        async function worker() {
            while (nextChunkIndex < totalChunks) {
                const currentIndex = nextChunkIndex;
                nextChunkIndex++;
                await uploadOneChunk(currentIndex);
            }
        }

        async function reuploadAllChunksOnce() {
            for (let i = 0; i < totalChunks; i++) {
                await uploadOneChunk(i);
            }
        }

        const workers = [];
        for (let i = 0; i < concurrency; i++) {
            workers.push(worker());
        }
        await Promise.all(workers);

        async function completeUploadRequest() {
            if (uploadFeedback) {
                uploadFeedback.classList.remove('d-none', 'text-danger');
                uploadFeedback.classList.add('text-muted');
                uploadFeedback.textContent = 'Äang ghÃ©p file trÃªn server...';
            }

            const completeForm = new FormData();
            completeForm.append('_token', csrfToken);
            completeForm.append('upload_id', uploadId);
            completeForm.append('total_chunks', String(totalChunks));
            completeForm.append('filename', file.name);
            completeForm.append('mime_type', file.type || 'application/octet-stream');

            let completeResponse = null;
            try {
                completeResponse = await fetchWithTimeout(completeUrl, {
                    method: 'POST',
                    body: completeForm,
                }, COMPLETE_TIMEOUT_MS);
            } catch (error) {
                completeResponse = null;
            }

            if (!completeResponse) {
                return {
                    ok: false,
                    data: {
                        message: 'Server Ä‘ang báº­n ghÃ©p video, Ä‘ang thá»­ láº¡i...'
                    }
                };
            }

            let completeData = null;
            try {
                completeData = await completeResponse.json();
            } catch (error) {
                completeData = null;
            }

            return {
                ok: completeResponse.ok,
                data: completeData,
            };
        }

        for (let completeTry = 1; completeTry <= COMPLETE_RETRY_LIMIT; completeTry++) {
            const completeResult = await completeUploadRequest();
            const completeData = completeResult.data || {};

            if (completeResult.ok && completeData.media_path) {
                if (progressBar) {
                    await smoothProgressTo(progressBar, 100);
                }
                if (uploadFeedback) {
                    uploadFeedback.classList.remove('d-none', 'text-danger');
                    uploadFeedback.classList.add('text-muted');
                    uploadFeedback.textContent = 'ÄÃ£ táº£i lÃªn: 100%';
                }
                return completeData;
            }

            const missingChunks = Array.isArray(completeData.missing_chunks)
                ? completeData.missing_chunks
                : (typeof completeData.missing_chunk === 'number' ? [completeData.missing_chunk] : []);

            if (missingChunks.length === 0) {
                // Náº¿u server chÆ°a tráº£ danh sÃ¡ch chunk thiáº¿u, thá»­ láº¡i complete nhiá»u láº§n trÆ°á»›c khi fail.
                await new Promise(function (resolve) {
                    setTimeout(resolve, 500 * completeTry);
                });
                continue;
            }

            if (uploadFeedback) {
                uploadFeedback.classList.remove('d-none', 'text-danger');
                uploadFeedback.classList.add('text-muted');
                uploadFeedback.textContent = 'Äang tá»± Ä‘á»™ng táº£i bÃ¹ ' + missingChunks.length + ' pháº§n cÃ²n thiáº¿u...';
            }

            for (let i = 0; i < missingChunks.length; i++) {
                await uploadOneChunk(missingChunks[i]);
            }

            // Láº§n gáº§n cuá»‘i váº«n cÃ²n thiáº¿u -> Ä‘á»“ng bá»™ láº¡i toÃ n bá»™ chunk 1 lÆ°á»£t Ä‘á»ƒ trÃ¡nh lá»—i máº¥t gÃ³i ngáº«u nhiÃªn.
            if (completeTry === COMPLETE_RETRY_LIMIT - 1) {
                if (uploadFeedback) {
                    uploadFeedback.classList.remove('d-none', 'text-danger');
                    uploadFeedback.classList.add('text-muted');
                    uploadFeedback.textContent = 'Äang Ä‘á»“ng bá»™ láº¡i toÃ n bá»™ dá»¯ liá»‡u video...';
                }
                await reuploadAllChunksOnce();
            }
        }

        throw new Error('KhÃ´ng thá»ƒ hoÃ n táº¥t upload video lÃºc nÃ y. Vui lÃ²ng thá»­ láº¡i sau Ã­t phÃºt.');
    }

    document.addEventListener('DOMContentLoaded', initComposer);
</script>
<script src="{{ asset('js/video-recorder.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const videoEditorEl = document.getElementById('videoEditor');
    if (!videoEditorEl || typeof Quill === 'undefined') {
        return;
    }

    const videoQuill = new Quill('#videoEditor', {
        theme: 'snow',
        placeholder: 'ThÃªm mÃ´ táº£ cho video cá»§a báº¡n...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'color': ['#000000', '#e91e63', '#9c27b0', '#3f51b5', '#00bcd4', '#4caf50', '#ffeb3b', '#ff9800'] }],
            ]
        }
    });
    videoQuill.root.style.fontFamily = "'Quicksand', 'Segoe UI Emoji', 'Apple Color Emoji', 'Noto Color Emoji', sans-serif";

    const form = document.getElementById('createVideoForm');
    const hiddenInput = document.getElementById('videoContentInput');
    const submitBtn = document.getElementById('videoSubmitBtn');
    const mediaInput = document.getElementById('videoMedia');
    const uploadFeedback = document.getElementById('videoUploadFeedback');
    const progressWrap = document.getElementById('videoUploadProgressWrap');
    const progressBar = document.getElementById('videoUploadProgressBar');
    const liveRecordOpenBtn = document.getElementById('liveRecordOpenBtn');
    const liveRecorderPanel = document.getElementById('liveRecorderPanel');
    const videoModal = document.getElementById('videoModal');
    const previewClearBtn = document.getElementById('videoPreviewClearBtn');
    const mediaPreviewContainer = document.getElementById('videoMediaPreviewContainer');
    const videoPreview = document.getElementById('videoComposerPreviewVideo');
    const uploadedMediaPathInput = document.getElementById('videoUploadedMediaPathInput');
    const uploadedMediaTypeInput = document.getElementById('videoUploadedMediaTypeInput');

    function previewVideoMedia(input) {
        if (!mediaPreviewContainer || !videoPreview || !input.files || !input.files[0]) {
            return;
        }

        const file = input.files[0];
        const objectUrl = URL.createObjectURL(file);
        mediaPreviewContainer.classList.remove('d-none');

        if (videoPreview.dataset.objectUrl) {
            URL.revokeObjectURL(videoPreview.dataset.objectUrl);
        }

        videoPreview.dataset.objectUrl = objectUrl;
        videoPreview.src = objectUrl;
        videoPreview.style.display = 'block';
    }

    function clearVideoMedia() {
        if (mediaInput) {
            mediaInput.value = '';
        }
        if (mediaPreviewContainer) {
            mediaPreviewContainer.classList.add('d-none');
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
        if (progressBar) {
            updateProgressBar(progressBar, 0);
        }
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

    if (previewClearBtn && !previewClearBtn.dataset.bound) {
        previewClearBtn.dataset.bound = '1';
        previewClearBtn.addEventListener('click', clearVideoMedia);
    }

    if (liveRecordOpenBtn && !liveRecordOpenBtn.dataset.bound) {
        liveRecordOpenBtn.dataset.bound = '1';
        liveRecordOpenBtn.addEventListener('click', function () {
            if (liveRecorderPanel) {
                liveRecorderPanel.classList.remove('d-none');
            }
            initLivePreview().catch(function (error) {
                if (uploadFeedback) {
                    uploadFeedback.classList.remove('d-none', 'text-muted');
                    uploadFeedback.classList.add('text-danger');
                    uploadFeedback.textContent = error && error.message ? error.message : 'Khong the mo camera.';
                }
            });
        });
    }

    bindTrimListeners();
    bindRecorderButtonListeners();
    bindMusicListeners();
    bindEditorButtonListeners();

    if (mediaInput && uploadFeedback && !mediaInput.dataset.bound) {
        mediaInput.dataset.bound = '1';
        mediaInput.addEventListener('change', function () {
            const file = mediaInput.files && mediaInput.files[0] ? mediaInput.files[0] : null;
            if (!file) {
                uploadFeedback.classList.add('d-none');
                uploadFeedback.textContent = '';
                return;
            }

            const fileSizeMb = file.size / (1024 * 1024);
            if (fileSizeMb > 2048) {
                const overMb = fileSizeMb - 2048;
                uploadFeedback.classList.remove('d-none', 'text-muted');
                uploadFeedback.classList.add('text-danger');
                uploadFeedback.textContent = 'Video vÆ°á»£t giá»›i háº¡n 2GB (vÆ°á»£t ' + overMb.toFixed(1) + 'MB).';
                clearVideoMedia();
                return;
            }

            uploadFeedback.classList.remove('d-none', 'text-danger');
            uploadFeedback.classList.add('text-muted');
            uploadFeedback.textContent = 'Dung lÆ°á»£ng tá»‡p: ' + fileSizeMb.toFixed(1) + ' MB.';
            previewVideoMedia(mediaInput);
        });
    }

    if (form && hiddenInput) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            hiddenInput.value = videoQuill ? videoQuill.root.innerHTML : '';

            if (form.dataset.processingUpload === '1') {
                return;
            }

            form.dataset.processingUpload = '1';

            const selectedFile = mediaInput && mediaInput.files && mediaInput.files[0] ? mediaInput.files[0] : null;
            const chunkUrl = form.getAttribute('data-chunk-url') || '';
            const completeUrl = form.getAttribute('data-complete-url') || '';
            try {
                if (selectedFile) {
                    if (uploadFeedback) {
                        uploadFeedback.classList.remove('d-none', 'text-danger');
                        uploadFeedback.classList.add('text-muted');
                        uploadFeedback.textContent = 'Äang táº£i video...';
                    }
                    if (progressWrap && progressBar) {
                        progressWrap.classList.remove('d-none');
                        updateProgressBar(progressBar, 0);
                    }

                    const uploadResult = await uploadFileInChunks(
                        selectedFile,
                        chunkUrl,
                        completeUrl,
                        '{{ csrf_token() }}',
                        progressBar,
                        uploadFeedback
                    );

                    if (uploadedMediaPathInput) {
                        uploadedMediaPathInput.value = uploadResult.media_path || '';
                    }
                    if (uploadedMediaTypeInput) {
                        uploadedMediaTypeInput.value = uploadResult.media_type || '';
                    }

                    mediaInput.value = '';
                }

                if (uploadFeedback) {
                    uploadFeedback.classList.remove('d-none', 'text-danger');
                    uploadFeedback.classList.add('text-muted');
                    uploadFeedback.textContent = 'Äang táº¡o bÃ i viáº¿t video...';
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Äang Ä‘Äƒng...';
                }

                form.dataset.processingUpload = '0';
                form.submit();
            } catch (error) {
                form.dataset.processingUpload = '0';

                if (uploadFeedback) {
                    uploadFeedback.classList.remove('d-none', 'text-muted');
                    uploadFeedback.classList.add('text-danger');
                    uploadFeedback.textContent = error && error.message ? error.message : 'Táº£i video tháº¥t báº¡i.';
                }
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'ÄÄƒng video';
                }
                if (progressWrap) {
                    progressWrap.classList.add('d-none');
                }
            }
        });
    }

    if (videoModal && !videoModal.dataset.videoBound) {
        videoModal.dataset.videoBound = '1';
        videoModal.addEventListener('hidden.bs.modal', function () {
            const liveRecorderPanel = document.getElementById('liveRecorderPanel');
            const liveEditorPanel = document.getElementById('liveEditorPanel');
            if (liveRecorderPanel) {
                liveRecorderPanel.classList.add('d-none');
            }
            if (liveEditorPanel) {
                liveEditorPanel.classList.add('d-none');
            }
            stopLivePreview();
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var privacyState = {
        post: 'public',
        video: 'public',
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
        if (optionBtn.dataset.bound === '1') {
            return;
        }

        optionBtn.dataset.bound = '1';
        optionBtn.addEventListener('click', function () {
            var target = optionBtn.getAttribute('data-privacy-target') || 'post';
            var value = optionBtn.getAttribute('data-privacy-value') || 'public';
            applyComposerPrivacy(target, value);
        });
    });

    applyComposerPrivacy('post', privacyState.post);
    applyComposerPrivacy('video', privacyState.video);

    var editModalEl = document.getElementById('postPrivacyEditModal');
    var editSaveBtn = document.getElementById('postPrivacySaveBtn');
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
        if (triggerBtn.dataset.bound === '1') {
            return;
        }

        triggerBtn.dataset.bound = '1';
        triggerBtn.addEventListener('click', function () {
            editingPostId = parseInt(triggerBtn.getAttribute('data-post-id') || '0', 10);
            var currentPrivacy = triggerBtn.getAttribute('data-current-privacy') || 'public';
            applyEditSelection(currentPrivacy);

            if (editModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(editModalEl).show();
            }
        });
    });

    Array.prototype.slice.call(document.querySelectorAll('[data-privacy-edit-option]')).forEach(function (btn) {
        if (btn.dataset.bound === '1') {
            return;
        }

        btn.dataset.bound = '1';
        btn.addEventListener('click', function () {
            applyEditSelection(btn.getAttribute('data-privacy-value') || 'public');
        });
    });

    if (editSaveBtn && !editSaveBtn.dataset.bound) {
        editSaveBtn.dataset.bound = '1';
        editSaveBtn.addEventListener('click', function () {
            if (editingPostId <= 0) {
                return;
            }

            var csrf = document.querySelector('meta[name="csrf-token"]');
            editSaveBtn.disabled = true;
            editSaveBtn.textContent = 'Dang luu...';

            fetch('/api/posts/' + editingPostId + '/privacy', {
                method: 'PATCH',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : ''
                },
                body: JSON.stringify({
                    privacy_status: privacyState.edit
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
                        var message = (result.json && result.json.message) ? result.json.message : 'Khong cap nhat duoc quyen rieng tu.';
                        throw new Error(message);
                    }

                    window.location.reload();
                })
                .catch(function (error) {
                    window.alert(error && error.message ? error.message : 'Co loi xay ra khi cap nhat quyen rieng tu.');
                })
                .finally(function () {
                    editSaveBtn.disabled = false;
                    editSaveBtn.textContent = 'Luu thay doi';
                });
        });
    }
});
</script>
@endsection
