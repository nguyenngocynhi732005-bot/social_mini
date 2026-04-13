<div class="d-flex gap-2 overflow-auto no-scrollbar mb-4 pb-2">
    
    <!-- Ô TẠO TIN CỦA BẠN -->
    <div onclick="document.getElementById('storyInput').click()" 
         style="cursor: pointer; min-width: 115px; width: 115px; height: 200px; border-radius: 15px; overflow: hidden; border: 2px solid #1f1c1c; display: flex; flex-direction: column;" 
         class="bg-white shadow-sm flex-shrink-0">
        <div style="height: 70%; background: #ffc0cb; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-camera fa-2x text-white"></i>
        </div>
        <div class="text-center position-relative bg-white" style="height: 30%;">
            <div class="bg-primary text-white rounded-circle position-absolute top-0 start-50 translate-middle border border-white border-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="fas fa-plus fa-sm"></i>
            </div>
            <p class="fw-bold m-0 mt-3" style="color: #1f1c1c; font-size: 0.8rem;">Tạo tin</p>
        </div>
    </div>


<!-- DÃY STORY BẠN BÈ -->
@foreach($stories as $story)
    @php
        $storyUser = optional($story->user);
        $storyFullName = trim((string) ($storyUser->First_name ?? '') . ' ' . (string) ($storyUser->Last_name ?? ''));
        $storyDisplayName = $storyFullName !== ''
            ? $storyFullName
            : ((string) ($storyUser->name ?? $storyUser->Email ?? $storyUser->email ?? 'Bạn'));
    @endphp
    <!-- HIỂN THỊ STORY THẬT TỪ DATABASE -->
    @php($storyImageUrl = asset('storage/' . $story->image_path))
    <div class="story-card story-item shadow-sm flex-shrink-0"
         data-story-image="{{ $storyImageUrl }}"
         data-story-user="{{ $storyDisplayName }}"
         data-story-caption="{{ $story->caption ?? '' }}"
            data-story-text-color="{{ $story->text_color ?? '#ffffff' }}"
         data-story-music="{{ $story->music_name ?? '' }}"
            data-story-music-path="{{ $story->music_path ?? '' }}"
            data-story-scale="{{ $story->image_scale ?? 1 }}"
            data-story-created-at="{{ optional($story->created_at)->toIso8601String() }}"
                data-story-delete-url="{{ route('story.destroy', $story->id) }}"
         style="min-width: 115px; width: 115px; height: 200px; border-radius: 15px; overflow: hidden; position: relative; 
                background-image: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.6)), url('{{ $storyImageUrl }}'); 
                background-size: cover; background-position: center; border: 1px solid #1f1c1c; cursor: pointer;">
        
        <!-- Avatar người đăng -->
        <div class="p-2">
            <img src="https://i.pravatar.cc/40?u={{ $story->user_id }}" 
                 class="rounded-circle border border-white border-2 shadow-sm" 
                 style="width: 35px; height: 35px; object-fit: cover;">
        </div>

        <form method="POST" action="{{ route('story.destroy', $story->id) }}" style="position:absolute; top: 8px; right: 8px; z-index: 5;">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="event.stopPropagation(); return confirm('Xóa story này?')" title="Xóa story" class="story-delete-btn" style="border:0; width:26px; height:26px; border-radius:999px; background:rgba(0,0,0,0.56); color:#fff; font-size:16px; line-height:1; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(0,0,0,0.35); opacity:0; pointer-events:none; transition:opacity .2s ease;">
                ×
            </button>
        </form>
        
        <!-- Hiển thị nội dung chữ đã nhập -->
        <div class="position-absolute top-50 start-50 translate-middle w-100 text-center px-2">
            <p class="m-0 fw-bold story-caption" 
               data-text-color="{{ $story->text_color ?? '#ffffff' }}"
               style="font-size: 0.8rem; 
                      text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
                      word-wrap: break-word;">
                {{ $story->caption }}
            </p>
        </div>

        <!-- Tên người đăng & Nhạc -->
        <div class="position-absolute bottom-0 p-2 w-100">
            @if($story->music_name)
                <div class="text-white mb-1" style="font-size: 0.6rem; opacity: 0.9;">
                    <i class="fas fa-music fa-xs me-1"></i> {{ \Illuminate\Support\Str::limit($story->music_name, 12) }}
                </div>
            @endif
            <small class="text-white fw-600 d-block" style="font-size: 0.75rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                {{ $storyDisplayName }}
            </small>
        </div>
    </div>

@endforeach

<!-- DỮ LIỆU GIẢ (Luôn hiện ở cuối để demo giao diện) -->
@for($i=1; $i<=2; $i++)
    @php($mockImageUrl = "https://picsum.photos/200/300?random={$i}")
    <div class="story-card story-item shadow-sm flex-shrink-0"
         data-story-image="{{ $mockImageUrl }}"
         data-story-user="Bạn bè {{$i}}"
         data-story-caption="Story mẫu {{$i}}"
            data-story-text-color="#ffffff"
         data-story-music=""
            data-story-music-path=""
            data-story-scale="1"
                data-story-created-at=""
                data-story-delete-url=""
         style="min-width: 115px; width: 115px; height: 200px; border-radius: 15px; overflow: hidden; position: relative; 
                background-image: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.6)), url('{{ $mockImageUrl }}'); 
                background-size: cover; background-position: center; border: 1px solid #1f1c1c; cursor: pointer;">
        <div class="p-2">
            <img src="https://i.pravatar.cc/40?u={{$i}}" class="rounded-circle border border-primary border-3 shadow-sm" style="width: 35px; height: 35px; object-fit: cover;">
        </div>
        <small class="position-absolute bottom-0 text-white p-2 fw-600 w-100" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8); font-size: 0.75rem;">
            Bạn bè {{$i}}
        </small>
    </div>
    @endfor
</div>

<style>
    .story-item:hover .story-delete-btn,
    .story-item:focus-within .story-delete-btn {
        opacity: 1;
        pointer-events: auto;
    }
</style>

<!-- STORY VIEWER -->
<div id="storyViewer" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.92); z-index: 11000;">
    <div id="storyViewerContent" style="position: relative; width: min(420px, 96vw); height: min(88vh, 760px); margin: 4vh auto; border-radius: 16px; overflow: hidden; background: #111;">
        <div style="position: absolute; top: 12px; left: 12px; right: 12px; z-index: 3;">
            <div style="height: 4px; background: rgba(255,255,255,0.35); border-radius: 999px; overflow: hidden;">
                <div id="storyProgressBar" style="height: 100%; width: 0%; background: #ffffff;"></div>
            </div>
        </div>

        <button type="button" id="deleteStoryViewerBtn" title="Xóa story" style="position: absolute; top: 22px; right: 60px; z-index: 4; border: 0; background: transparent; color: #fff; font-size: 18px; display: none; padding: 0; line-height: 1;">
            <i class="fas fa-trash-alt"></i>
        </button>
        <button type="button" id="closeStoryViewer" style="position: absolute; top: 18px; right: 16px; z-index: 4; border: 0; width: 34px; height: 34px; border-radius: 50%; background: rgba(0,0,0,0.45); color: #fff; font-size: 18px;">×</button>

        <div id="storyViewerCanvas" style="position: absolute; inset: 0;">
            <img id="storyViewerImage" src="" alt="Story image" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; object-position: center; background: #000; filter: brightness(1.08) saturate(1.03); transform: scale(1); transform-origin: center center; transition: transform 0.15s ease;">
            <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.08), rgba(0,0,0,0.3));"></div>

            <div style="position: absolute; top: 18px; left: 16px; z-index: 4; color: #fff; text-shadow: 0 1px 3px rgba(0,0,0,0.6);">
                <div id="storyViewerUser" style="font-weight: 700; font-size: 0.95rem;"></div>
                <div id="storyViewerTime" style="font-size: 0.75rem; opacity: 0.88;"></div>
                <div id="storyViewerMusic" style="font-size: 0.8rem; opacity: 0.9;"></div>
            </div>

            <audio id="storyViewerAudio" preload="auto" style="display:none;" playsinline></audio>

            <div id="storyViewerCaption" style="position: absolute; left: 16px; right: 16px; bottom: 20px; z-index: 4; color: #fff; text-align: center; font-weight: 700; font-size: 1.1rem; text-shadow: 0 2px 5px rgba(0,0,0,0.8);"></div>
        </div>

        <button type="button" id="prevStoryBtn" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); z-index: 4; border: 0; width: 34px; height: 34px; border-radius: 50%; background: rgba(0,0,0,0.4); color: #fff;">‹</button>
        <button type="button" id="nextStoryBtn" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); z-index: 4; border: 0; width: 34px; height: 34px; border-radius: 50%; background: rgba(0,0,0,0.4); color: #fff;">›</button>
    </div>
</div>

<form id="storyViewerDeleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<!-- Input file ẩn (gắn với form để gửi được lên server) -->
<input type="file" id="storyInput" name="image" form="storyForm" accept="image/*" style="display: none;" onchange="previewStory(this)">

<!-- MODAL CHỈNH SỬA TIN (Phóng to khi chọn ảnh) -->
<div id="storyModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
        <div class="modal-content" style="border-radius: 20px; border: 2px solid #1f1c1c; overflow: hidden;">
            <form id="storyForm" action="{{ route('story.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
<!-- Trong Modal Body -->
<div class="modal-body p-0 position-relative bg-dark" style="min-height: 500px; display: flex; align-items: center; justify-content: center;">
        <div id="storyCanvas" style="width: min(100%, 300px); aspect-ratio: 9 / 16; overflow: hidden; position: relative; background: #000; transform: scale(1); transform-origin: center center; transition: transform 0.15s ease;">
            <img id="storyPreview" src="" style="width: 100%; height: 100%; object-fit: contain; object-position: center; background: #000; transform: scale(1); transform-origin: center center; transition: transform 0.15s ease;">
        </div>
    
    <!-- Nội dung chữ có thể đổi màu -->
    <textarea name="caption" id="storyCaption" placeholder="Nhập nội dung tin..." 
    style="position: absolute; top: 100px; left: 50px; width: auto; min-width: 150px; 
           background: transparent; color: white; border: 1px dashed rgba(255,255,255,0.5); 
           text-align: center; font-size: 1.5rem; font-weight: bold; 
           text-shadow: 2px 2px 4px rgba(0,0,0,0.5); resize: none; cursor: move;
           padding: 10px; overflow: hidden; white-space: nowrap;"></textarea>

    <!-- Thêm 2 input ẩn để lưu tọa độ vào Database (Nhi nhắc nhóm Backend thêm 2 cột này nhé) -->
    <input type="hidden" name="pos_top" id="posTopInput" value="100">
    <input type="hidden" name="pos_left" id="posLeftInput" value="50">

    <!-- Thanh chọn màu sắc -->
    <div class="position-absolute end-0 top-50 translate-middle-y d-flex flex-column gap-2 p-3">
        <div onclick="changeTextColor('#ffffff')" style="width: 25px; height: 25px; border-radius: 50%; background: white; border: 2px solid #ddd; cursor: pointer;"></div>
        <div onclick="changeTextColor('#ff85a2')" style="width: 25px; height: 25px; border-radius: 50%; background: #ff85a2; cursor: pointer;"></div>
        <div onclick="changeTextColor('#84fab0')" style="width: 25px; height: 25px; border-radius: 50%; background: #84fab0; cursor: pointer;"></div>
        <div onclick="changeTextColor('#ffd166')" style="width: 25px; height: 25px; border-radius: 50%; background: #ffd166; cursor: pointer;"></div>
        <input type="hidden" name="text_color" id="textColorInput" value="#ffffff">
        <input type="hidden" name="image_scale" id="imageScaleInput" value="1">
    </div>

    <!-- Nút chèn nhạc giả -->
    <div class="position-absolute top-0 start-0 m-3">
        <button type="button" class="btn btn-sm btn-glass text-white rounded-pill" onclick="toggleMusicList()">
            <i class="fas fa-music me-1"></i> <span id="selectedMusic">Thêm nhạc</span>
        </button>
        <div id="musicList" class="bg-white rounded shadow-lg p-2 mt-2 d-none" style="width: 200px; z-index: 10000;">
            @forelse(($hotSongs ?? collect()) as $song)
                <div class="p-2 border-bottom song-item"
                     data-song-id="{{ $song->id }}"
                     data-song-name="{{ $song->title }}"
                     data-song-path="{{ $song->playable_url }}"
                     onclick="selectMusicFromElement(this)">
                    <strong>{{ $song->hot_rank }}.</strong> {{ $song->title }}
                    @if($song->artist)
                        <div style="font-size: 0.75rem; color: #666;">{{ $song->artist }}</div>
                    @endif
                </div>
            @empty
                <div class="p-2 text-muted">Chưa có bài hát hot trong database.</div>
            @endforelse
            <input type="hidden" name="music_id" id="musicIdInput">
            <audio id="musicPreviewAudio" preload="auto" style="display:none;" playsinline></audio>
        </div>
    </div>

    <div class="position-absolute bottom-0 start-50 translate-middle-x p-3" style="width: min(280px, 82%);">
        <div class="bg-white bg-opacity-10 rounded-3 px-3 py-2" style="backdrop-filter: blur(8px);">
            <input type="range" id="storyZoomRange" min="0.5" max="2" step="0.05" value="1" class="form-range" oninput="setStoryZoom(this.value)">
        </div>
    </div>
</div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                <div class="modal-footer bg-white d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" onclick="document.getElementById('storyInput').click()">Đổi ảnh</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Đăng ngay</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function applyStoryCaptionColors() {
    document.querySelectorAll('.story-caption[data-text-color]').forEach(function (el) {
        el.style.color = el.getAttribute('data-text-color') || '#ffffff';
    });
}

document.addEventListener('DOMContentLoaded', applyStoryCaptionColors);
applyStoryCaptionColors();

function previewStory(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            // 1. Đưa ảnh vào thẻ img xem trước
            const preview = document.getElementById('storyPreview');
            preview.src = e.target.result;
            resetStoryZoom();

            // 2. Ép Modal hiển thị (Dùng cách này để tránh lỗi thư viện không nhận diện)
            var storyModalEl = document.getElementById('storyModal');
            var modal = bootstrap.Modal.getOrCreateInstance(storyModalEl); 
            modal.show();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function changeTextColor(color) {
    var caption = document.getElementById('storyCaption');
    var colorInput = document.getElementById('textColorInput');
    if (caption) {
        caption.style.color = color;
    }
    if (colorInput) {
        colorInput.value = color;
    }
}

function toggleMusicList() {
    var list = document.getElementById('musicList');
    if (list) {
        list.classList.toggle('d-none');
    }
}

function selectMusic(songId, songName, songPath) {
    var selectedMusic = document.getElementById('selectedMusic');
    var musicIdInput = document.getElementById('musicIdInput');
    var list = document.getElementById('musicList');
    var previewAudio = document.getElementById('musicPreviewAudio');

    if (selectedMusic) {
        selectedMusic.textContent = songName;
    }
    if (musicIdInput) {
        musicIdInput.value = songId || '';
    }
    if (previewAudio) {
        previewAudio.pause();
        previewAudio.currentTime = 0;
        previewAudio.src = songPath || '';
        if (songPath) {
            previewAudio.load();
            previewAudio.play().catch(function () {});
            setTimeout(function () {
                previewAudio.pause();
            }, 20000);
        }
    }
    if (list) {
        list.classList.add('d-none');
    }
}

function selectMusicFromElement(el) {
    if (!el) {
        return;
    }
    var songId = el.getAttribute('data-song-id') || '';
    var songName = el.getAttribute('data-song-name') || '';
    var songPath = el.getAttribute('data-song-path') || '';
    selectMusic(songId, songName, songPath);
}

const viewer = document.getElementById('storyViewer');
const viewerImage = document.getElementById('storyViewerImage');
const viewerUser = document.getElementById('storyViewerUser');
const viewerTime = document.getElementById('storyViewerTime');
const viewerMusic = document.getElementById('storyViewerMusic');
const viewerCaption = document.getElementById('storyViewerCaption');
const progressBar = document.getElementById('storyProgressBar');
const viewerAudio = document.getElementById('storyViewerAudio');
const viewerDeleteBtn = document.getElementById('deleteStoryViewerBtn');
const viewerDeleteForm = document.getElementById('storyViewerDeleteForm');
let viewerStoryItems = [];
let viewerIndex = -1;
let viewerTimer = null;
let viewerTimeTicker = null;

function formatStoryTime(createdAt) {
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
        return diffMinutes + ' phút';
    }

    var diffHours = Math.floor(diffMinutes / 60);
    return diffHours + ' giờ';
}

function updateViewerTime(createdAt) {
    if (!viewerTime) {
        return;
    }

    viewerTime.textContent = formatStoryTime(createdAt);
}

function collectStoryItems() {
    viewerStoryItems = Array.from(document.querySelectorAll('.story-item'));
}

function renderStoryAt(index) {
    if (!viewerStoryItems.length || index < 0 || index >= viewerStoryItems.length) {
        closeStoryViewer();
        return;
    }

    viewerIndex = index;
    const item = viewerStoryItems[index];
    const image = item.getAttribute('data-story-image') || '';
    const user = item.getAttribute('data-story-user') || 'Bạn';
    const music = item.getAttribute('data-story-music') || '';
    const musicPath = item.getAttribute('data-story-music-path') || '';
    const imageScale = parseFloat(item.getAttribute('data-story-scale') || '1');
    const captionText = item.getAttribute('data-story-caption') || '';
    const captionColor = item.getAttribute('data-story-text-color') || '#ffffff';
    const createdAt = item.getAttribute('data-story-created-at') || '';
    const deleteUrl = item.getAttribute('data-story-delete-url') || '';

    const normalizedImage = (image || '').replace(/\\/g, '/');
    viewerImage.src = normalizedImage;
    viewerImage.style.transform = 'scale(' + (isNaN(imageScale) ? 1 : imageScale) + ')';
    viewerUser.textContent = user;
    updateViewerTime(createdAt);
    if (viewerTimeTicker) {
        clearInterval(viewerTimeTicker);
        viewerTimeTicker = null;
    }
    if (createdAt) {
        viewerTimeTicker = setInterval(function () {
            updateViewerTime(createdAt);
        }, 60000);
    }
    viewerMusic.textContent = music ? ('♫ ' + music) : '';
    viewerCaption.textContent = captionText;
    viewerCaption.style.color = captionColor;
    if (viewerDeleteBtn) {
        if (deleteUrl) {
            viewerDeleteBtn.style.display = 'inline-flex';
            viewerDeleteBtn.setAttribute('data-delete-url', deleteUrl);
        } else {
            viewerDeleteBtn.style.display = 'none';
            viewerDeleteBtn.removeAttribute('data-delete-url');
        }
    }

    // Chỉ phát được khi có URL audio thật; nếu chỉ là tên bài thì sẽ im lặng.
    if (musicPath) {
        viewerAudio.src = musicPath;
        viewerAudio.currentTime = 0;
        viewerAudio.load();
        viewerAudio.play().catch(function () {});
    } else {
        viewerAudio.pause();
        viewerAudio.removeAttribute('src');
    }

    if (viewerTimer) {
        clearTimeout(viewerTimer);
    }

    progressBar.style.transition = 'none';
    progressBar.style.width = '0%';
    requestAnimationFrame(function () {
        progressBar.style.transition = 'width 15s linear';
        progressBar.style.width = '100%';
    });

    viewerTimer = setTimeout(function () {
        nextStory(true);
    }, 20000);
}

function openStoryViewer(index) {
    collectStoryItems();
    if (!viewerStoryItems.length) {
        return;
    }
    viewer.style.display = 'block';
    document.body.style.overflow = 'hidden';
    renderStoryAt(index);
}

function closeStoryViewer() {
    viewer.style.display = 'none';
    document.body.style.overflow = '';
    if (viewerTimer) {
        clearTimeout(viewerTimer);
        viewerTimer = null;
    }
    if (viewerTimeTicker) {
        clearInterval(viewerTimeTicker);
        viewerTimeTicker = null;
    }
    viewerAudio.pause();
    viewerAudio.removeAttribute('src');
    if (viewerDeleteBtn) {
        viewerDeleteBtn.style.display = 'none';
        viewerDeleteBtn.removeAttribute('data-delete-url');
    }
}

function deleteCurrentStoryInViewer() {
    if (!viewerDeleteBtn || !viewerDeleteForm) {
        return;
    }

    const deleteUrl = viewerDeleteBtn.getAttribute('data-delete-url') || '';
    if (!deleteUrl) {
        return;
    }

    if (!confirm('Xóa story này ngay bây giờ?')) {
        return;
    }

    closeStoryViewer();
    viewerDeleteForm.setAttribute('action', deleteUrl);
    viewerDeleteForm.submit();
}

function setStoryZoom(value) {
    var previewImage = document.getElementById('storyPreview');
    var zoomRange = document.getElementById('storyZoomRange');
    var imageScaleInput = document.getElementById('imageScaleInput');

    if (!previewImage) {
        return;
    }

    var nextValue = Math.max(0.5, Math.min(2, value));
    previewImage.style.transform = 'scale(' + nextValue + ')';

    if (zoomRange) {
        zoomRange.value = nextValue;
    }
    if (imageScaleInput) {
        imageScaleInput.value = nextValue;
    }
}

function resetStoryZoom() {
    setStoryZoom(1);
}

function nextStory(autoAdvance) {
    const nextIndex = viewerIndex + 1;
    if (nextIndex >= viewerStoryItems.length) {
        closeStoryViewer();
        return;
    }
    renderStoryAt(nextIndex);
}

function prevStory() {
    const prevIndex = viewerIndex - 1;
    if (prevIndex < 0) {
        return;
    }
    renderStoryAt(prevIndex);
}

document.addEventListener('DOMContentLoaded', function () {
    collectStoryItems();
    viewerStoryItems.forEach(function (item, index) {
        item.addEventListener('click', function () {
            openStoryViewer(index);
        });
    });

    document.getElementById('closeStoryViewer').addEventListener('click', closeStoryViewer);
    document.getElementById('nextStoryBtn').addEventListener('click', function () { nextStory(false); });
    document.getElementById('prevStoryBtn').addEventListener('click', prevStory);
    if (viewerDeleteBtn) {
        viewerDeleteBtn.addEventListener('click', deleteCurrentStoryInViewer);
    }

    var zoomRange = document.getElementById('storyZoomRange');
    if (zoomRange) {
        zoomRange.addEventListener('input', function () {
            setStoryZoom(parseFloat(this.value || '1'));
        });
    }

    viewer.addEventListener('click', function (e) {
        if (e.target === viewer) {
            closeStoryViewer();
        }
    });
});
</script>
<script>
    const caption = document.getElementById('storyCaption');
    let isDragging = false;
    let offset = { x: 0, y: 0 };

    // Bắt đầu kéo
    caption.addEventListener('mousedown', startDragging);
    caption.addEventListener('touchstart', startDragging, { passive: false });

    function startDragging(e) {
        isDragging = true;
        const clientX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
        const clientY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;
        
        offset.x = clientX - caption.offsetLeft;
        offset.y = clientY - caption.offsetTop;
        caption.style.border = "1px solid white"; // Hiện khung để biết đang kéo
    }

    // Đang di chuyển
    document.addEventListener('mousemove', drag);
    document.addEventListener('touchmove', drag, { passive: false });

    function drag(e) {
        if (!isDragging) return;
        if (e.type === 'touchmove') e.preventDefault();

        const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
        const clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;

        let newX = clientX - offset.x;
        let newY = clientY - offset.y;

        // Cập nhật vị trí trên màn hình
        caption.style.left = newX + 'px';
        caption.style.top = newY + 'px';

        // Lưu vào input ẩn để gửi lên server
        document.getElementById('posTopInput').value = newX;
        document.getElementById('posLeftInput').value = newY;
    }

    // Dừng kéo
    document.addEventListener('mouseup', stopDragging);
    document.addEventListener('touchend', stopDragging);

    function stopDragging() {
        isDragging = false;
        caption.style.border = "1px dashed rgba(255,255,255,0.5)";
    }
</script>