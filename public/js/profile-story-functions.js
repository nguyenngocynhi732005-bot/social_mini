(function () {
    function getEl(id) {
        return document.getElementById(id);
    }

    function showProfileStoryModal() {
        var modalEl = getEl('profileStoryModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    window.setProfileStoryZoom = function (value) {
        var preview = getEl('profileStoryPreview');
        var zoomRange = getEl('profileStoryZoomRange');
        var imageScaleInput = getEl('profileImageScaleInput');

        if (!preview) {
            return;
        }

        var nextValue = Math.max(0.5, Math.min(2, parseFloat(value || '1')));
        preview.style.transform = 'scale(' + nextValue + ')';

        if (zoomRange) {
            zoomRange.value = String(nextValue);
        }
        if (imageScaleInput) {
            imageScaleInput.value = String(nextValue);
        }
    };

    window.resetProfileStoryZoom = function () {
        window.setProfileStoryZoom(1);
    };

    window.previewProfileStory = function (input) {
        if (!input || !input.files || !input.files[0]) {
            return;
        }

        var reader = new FileReader();
        reader.onload = function (event) {
            var preview = getEl('profileStoryPreview');
            if (preview) {
                preview.src = event.target.result;
            }
            window.resetProfileStoryZoom();
            showProfileStoryModal();
        };
        reader.readAsDataURL(input.files[0]);
    };

    window.changeProfileStoryTextColor = function (color) {
        var caption = getEl('profileStoryCaption');
        var colorInput = getEl('profileTextColorInput');

        if (caption) {
            caption.style.color = color;
        }
        if (colorInput) {
            colorInput.value = color;
        }
    };

    window.toggleProfileMusicList = function () {
        var list = getEl('profileMusicList');
        if (list) {
            list.classList.toggle('d-none');
        }
    };

    window.selectProfileStoryMusic = function (songId, songName, songPath) {
        var selectedMusic = getEl('profileSelectedMusic');
        var musicIdInput = getEl('profileMusicIdInput');
        var musicNameInput = getEl('profileMusicNameInput');
        var musicPathInput = getEl('profileMusicPathInput');
        var list = getEl('profileMusicList');
        var previewAudio = getEl('profileMusicPreviewAudio');

        if (selectedMusic) {
            selectedMusic.textContent = songName || 'Them nhac';
        }
        if (musicIdInput) {
            musicIdInput.value = songId || '';
        }
        if (musicNameInput) {
            musicNameInput.value = songName || '';
        }
        if (musicPathInput) {
            musicPathInput.value = songPath || '';
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
    };

    window.selectProfileMusicFromElement = function (el) {
        if (!el) {
            return;
        }
        var songId = el.getAttribute('data-song-id') || '';
        var songName = el.getAttribute('data-song-name') || '';
        var songPath = el.getAttribute('data-song-path') || '';
        window.selectProfileStoryMusic(songId, songName, songPath);
    };

    function bindProfileStoryCaptionDrag() {
        var caption = getEl('profileStoryCaption');
        var posTopInput = getEl('profilePosTopInput');
        var posLeftInput = getEl('profilePosLeftInput');
        if (!caption || caption.dataset.dragBound === '1') {
            return;
        }

        var isDragging = false;
        var offset = { x: 0, y: 0 };

        function startDragging(event) {
            isDragging = true;
            var clientX = event.type === 'touchstart' ? event.touches[0].clientX : event.clientX;
            var clientY = event.type === 'touchstart' ? event.touches[0].clientY : event.clientY;

            offset.x = clientX - caption.offsetLeft;
            offset.y = clientY - caption.offsetTop;
            caption.style.border = '1px solid white';
        }

        function drag(event) {
            if (!isDragging) {
                return;
            }
            if (event.type === 'touchmove') {
                event.preventDefault();
            }

            var clientX = event.type === 'touchmove' ? event.touches[0].clientX : event.clientX;
            var clientY = event.type === 'touchmove' ? event.touches[0].clientY : event.clientY;

            var newX = clientX - offset.x;
            var newY = clientY - offset.y;

            caption.style.left = newX + 'px';
            caption.style.top = newY + 'px';

            if (posTopInput) {
                posTopInput.value = String(newY);
            }
            if (posLeftInput) {
                posLeftInput.value = String(newX);
            }
        }

        function stopDragging() {
            isDragging = false;
            caption.style.border = '1px dashed rgba(255,255,255,0.5)';
        }

        caption.addEventListener('mousedown', startDragging);
        caption.addEventListener('touchstart', startDragging, { passive: false });
        document.addEventListener('mousemove', drag);
        document.addEventListener('touchmove', drag, { passive: false });
        document.addEventListener('mouseup', stopDragging);
        document.addEventListener('touchend', stopDragging);

        caption.dataset.dragBound = '1';
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindProfileStoryCaptionDrag();

        var zoomRange = getEl('profileStoryZoomRange');
        if (zoomRange && zoomRange.dataset.bound !== '1') {
            zoomRange.dataset.bound = '1';
            zoomRange.addEventListener('input', function () {
                window.setProfileStoryZoom(parseFloat(this.value || '1'));
            });
        }

        document.addEventListener('click', function (event) {
            var list = getEl('profileMusicList');
            if (!list || list.classList.contains('d-none')) {
                return;
            }
            var trigger = event.target.closest('#profileMusicList, button[onclick="toggleProfileMusicList()"]');
            if (!trigger) {
                list.classList.add('d-none');
            }
        });
    });
})();
