@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 920px;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="fw-bold mb-1" style="font-family: 'Quicksand';">Video / Reels</h3>
            <div class="text-muted small">Quay hoặc tải video của bạn lên đây.</div>
        </div>
        <a href="{{ route('newsfeed') }}" class="btn btn-light btn-sm rounded-pill shadow-sm">Quay lại Newsfeed</a>
    </div>

    <!-- VIDEO LIST SECTION -->
    @forelse($videoPosts ?? [] as $post)
        @php
            $videoPath = $post->image_url ?? $post->media_path ?? null;
            $videoUrl = $videoPath ? asset('storage/' . ltrim($videoPath, '/')) : null;
            $videoExt = strtolower(pathinfo((string) $videoPath, PATHINFO_EXTENSION));
            $videoMimeType = $videoExt === 'webm' ? 'video/webm' : ($videoExt === 'ogg' ? 'video/ogg' : 'video/mp4');
            $rawPostContent = (string) ($post->content ?? '');
            $plainPostContent = trim(preg_replace('/\s+/u', ' ', strip_tags(str_replace('&nbsp;', ' ', $rawPostContent))));
            $hasPostContent = $plainPostContent !== '';
            $authorName = optional($post->user)->First_name
                ? trim(optional($post->user)->First_name . ' ' . optional($post->user)->Last_name)
                : (optional($post->user)->name ?? 'Người dùng');
        @endphp

        <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-radius: 18px;">
            <div class="card-body pb-2 position-relative">
                <div class="d-flex align-items-start pe-5">
                <div>
                    <div class="fw-bold">{{ $authorName }}</div>
                    <div class="text-muted small">{{ optional($post->created_at)->format('d/m/Y H:i') }}</div>
                </div>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-auto">Video</span>
                </div>

                <div class="dropdown position-absolute top-0 end-0 mt-2 me-2">
                    <button class="btn btn-light btn-sm rounded-circle border-0 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Tùy chọn video">
                        <span class="fw-bold" style="font-size: 20px; line-height: 1;">&hellip;</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <form action="{{ route('post.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Bạn có muốn xóa video này không?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger">Xóa video</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            @if($hasPostContent)
                <div class="px-3 pb-2 text-dark">
                    {!! $rawPostContent !!}
                </div>
            @endif

            @if($videoUrl)
                <div class="px-3 {{ $hasPostContent ? 'pb-3' : 'pb-2' }}">
                    <video class="w-100 rounded-4 bg-dark" controls playsinline preload="metadata" style="max-height: 620px; object-fit: cover;">
                        <source src="{{ $videoUrl }}" type="{{ $videoMimeType }}">
                        Trình duyệt của bạn không hỗ trợ video.
                    </video>
                </div>
            @else
                <div class="px-3 {{ $hasPostContent ? 'pb-3' : 'pb-2' }}">
                    <div class="alert alert-warning mb-0">Bài video này chưa có đường dẫn media hợp lệ.</div>
                </div>
            @endif
        </div>
    @empty
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="fas fa-play-circle fa-3x text-muted mb-3"></i>
                <div class="fw-bold">Chưa có video nào</div>
                <div class="text-muted small">Quay hoặc tải video của bạn để bắt đầu.</div>
            </div>
        </div>
    @endforelse
</div>
@endsection

@section('styles')
<style>
    #videoEditor {
        min-height: 80px;
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/video-recorder.js') }}"></script>
<!-- Nạp file logic chúng ta đã tách -->
<script src="{{ asset('js/video-composer.js') }}"></script>

<script>
    let quill = null;
    let previewEditor = null;

    function initVideoComposer() {
        const editorEl = document.getElementById('videoEditor');
        if (!editorEl || typeof Quill === 'undefined') return;

        if (!quill) {
            quill = new Quill('#videoEditor', {
                theme: 'snow',
                placeholder: 'Thêm mô tả cho video của bạn...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'color': ['#000000', '#e91e63', '#9c27b0', '#3f51b5', '#00bcd4', '#4caf50', '#ffeb3b', '#ff9800'] }],
                    ]
                }
            });
            quill.root.style.fontFamily = "'Quicksand', 'Segoe UI Emoji', 'Apple Color Emoji', 'Noto Color Emoji', sans-serif";
        }

        const form = document.getElementById('createVideoForm');
        const hiddenInput = document.getElementById('videoContentInput');
        const submitBtn = document.getElementById('videoSubmitBtn');
        const mediaInput = document.getElementById('videoMedia');
        const uploadFeedback = document.getElementById('postUploadFeedback');
        const progressWrap = document.getElementById('postUploadProgressWrap');
        const progressBar = document.getElementById('postUploadProgressBar');
        const liveRecordOpenBtn = document.getElementById('liveRecordOpenBtn');
        const liveRecorderPanel = document.getElementById('liveRecorderPanel');

        // Logic mở bảng quay video
        if (liveRecordOpenBtn && !liveRecordOpenBtn.dataset.bound) {
            liveRecordOpenBtn.dataset.bound = '1';
            liveRecordOpenBtn.addEventListener('click', function () {
                if (liveRecorderPanel) liveRecorderPanel.classList.remove('d-none');
            });
        }

        // Gọi các hàm xử lý nút bấm (phải giữ lại các hàm này ở dưới)
        bindTrimListeners();
        bindRecorderButtonListeners();
        bindMusicListeners();
        bindEditorButtonListeners();

        // Logic khi chọn file video
        if (mediaInput && uploadFeedback) {
            mediaInput.addEventListener('change', function () {
                const file = mediaInput.files[0];
                if (!file) return;
                const fileSizeMb = file.size / (1024 * 1024);
                if (fileSizeMb > MAX_MEDIA_SIZE_MB) {
                    alert('Video quá lớn!');
                    mediaInput.value = '';
                    return;
                }
                uploadFeedback.classList.remove('d-none');
                uploadFeedback.textContent = 'Dung lượng tệp: ' + fileSizeMb.toFixed(1) + ' MB.';
            });
        }

        // Logic khi bấm nút Đăng (Submit)
        if (form) {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                hiddenInput.value = quill.root.innerHTML;

                if (form.dataset.processingUpload === '1') return;
                form.dataset.processingUpload = '1';

                const selectedFile = mediaInput.files[0];
                try {
                    if (selectedFile) {
                        // Gọi hàm từ file video-composer.js
                        const uploadResult = await uploadFileInChunks(
                            selectedFile,
                            form.getAttribute('data-chunk-url'),
                            form.getAttribute('data-complete-url'),
                            '{{ csrf_token() }}',
                            progressBar,
                            uploadFeedback
                        );
                        document.getElementById('uploadedMediaPathInput').value = uploadResult.media_path;
                    }
                    form.submit();
                } catch (error) {
                    alert(error.message);
                    form.dataset.processingUpload = '0';
                }
            });
        }
    }

    // --- CÁC HÀM "TAY CHÂN" ĐIỀU KHIỂN GIAO DIỆN (BẮT BUỘC GIỮ LẠI) ---
    function bindTrimListeners() { /* ... Giữ nguyên code cũ của hàm này ... */ }
    function bindRecorderButtonListeners() { /* ... Giữ nguyên code cũ của hàm này ... */ }
    function bindMusicListeners() { /* ... Giữ nguyên code cũ của hàm này ... */ }
    function bindEditorButtonListeners() { /* ... Giữ nguyên code cũ của hàm này ... */ }
    function previewVideoMedia(input) { /* ... Giữ nguyên code cũ của hàm này ... */ }
    function clearVideoMedia() { /* ... Giữ nguyên code cũ của hàm này ... */ }

    document.addEventListener('DOMContentLoaded', initVideoComposer);
</script>
@endsection

