    const MAX_MEDIA_SIZE_MB = 2048;
    const CHUNK_SIZE_BYTES = 2 * 1024 * 1024;
    const COMPLETE_RETRY_LIMIT = 12;
    const CHUNK_TIMEOUT_MS = 180000;
    const COMPLETE_TIMEOUT_MS = 300000;

    function initVideoComposer() {
        const editorEl = document.getElementById('videoEditor');
        if (!editorEl || typeof Quill === 'undefined') {
            return;
        }

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
        const videoModal = document.getElementById('videoModal');

        // Open live recorder panel
        if (liveRecordOpenBtn && !liveRecordOpenBtn.dataset.bound) {
            liveRecordOpenBtn.dataset.bound = '1';
            liveRecordOpenBtn.addEventListener('click', function () {
                if (liveRecorderPanel) {
                    liveRecorderPanel.classList.remove('d-none');
                }
            });
        }

        // Trim event listeners
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
                if (fileSizeMb > MAX_MEDIA_SIZE_MB) {
                    const overMb = fileSizeMb - MAX_MEDIA_SIZE_MB;
                    uploadFeedback.classList.remove('d-none', 'text-muted');
                    uploadFeedback.classList.add('text-danger');
                    uploadFeedback.textContent = 'Video vượt giới hạn 2GB (vượt ' + overMb.toFixed(1) + 'MB).';
                    clearVideoMedia();
                    return;
                }

                uploadFeedback.classList.remove('d-none', 'text-danger');
                uploadFeedback.classList.add('text-muted');
                uploadFeedback.textContent = 'Dung lượng tệp: ' + fileSizeMb.toFixed(1) + ' MB.';
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
                const uploadedMediaPathInput =
                    document.getElementById('videoUploadedMediaPathInput') ||
                    document.getElementById('postUploadedMediaPathInput') ||
                    document.getElementById('uploadedMediaPathInput');
                const uploadedMediaTypeInput =
                    document.getElementById('videoUploadedMediaTypeInput') ||
                    document.getElementById('postUploadedMediaTypeInput') ||
                    document.getElementById('uploadedMediaTypeInput');

                try {
                    if (selectedFile) {
                        if (uploadFeedback) {
                            uploadFeedback.classList.remove('d-none', 'text-danger');
                            uploadFeedback.classList.add('text-muted');
                            uploadFeedback.textContent = 'Đang tải video...';
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
                        uploadFeedback.textContent = 'Đang tạo bài viết video...';
                    }

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Đang đăng...';
                    }

                    form.dataset.processingUpload = '0';
                    form.submit();
                } catch (error) {
                    form.dataset.processingUpload = '0';

                    if (uploadFeedback) {
                        uploadFeedback.classList.remove('d-none', 'text-muted');
                        uploadFeedback.classList.add('text-danger');
                        uploadFeedback.textContent = error && error.message ? error.message : 'Tải video thất bại.';
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Đăng video';
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
    }

    function bindTrimListeners() {
        const liveTrimStart = document.getElementById('liveTrimStart');
        const liveTrimEnd = document.getElementById('liveTrimEnd');
        const liveTrimStartLabel = document.getElementById('liveTrimStartLabel');
        const liveTrimEndLabel = document.getElementById('liveTrimEndLabel');
        const liveEditDuration = document.getElementById('liveEditDuration');

        if (liveTrimStart && !liveTrimStart.dataset.bound) {
            liveTrimStart.dataset.bound = '1';
            liveTrimStart.addEventListener('input', function () {
                const startValue = parseInt(liveTrimStart.value || '0', 10);
                const endValue = parseInt((liveTrimEnd && liveTrimEnd.value) || '0', 10);
                if (startValue >= endValue && liveTrimEnd) {
                    liveTrimEnd.value = String(Math.min(liveRecordingDuration, startValue + 1));
                }
                setTrimLabel(startValue, liveTrimStartLabel);
                if (liveEditDuration) {
                    liveEditDuration.textContent = 'Cắt: ' + startValue + 's → ' + (liveTrimEnd ? liveTrimEnd.value : endValue) + 's';
                }
                updateTrimSummary();
                updateTrimTrack();
            });
        }

        if (liveTrimEnd && !liveTrimEnd.dataset.bound) {
            liveTrimEnd.dataset.bound = '1';
            liveTrimEnd.addEventListener('input', function () {
                const endValue = parseInt(liveTrimEnd.value || '0', 10);
                const startValue = parseInt((liveTrimStart && liveTrimStart.value) || '0', 10);
                if (endValue <= startValue && liveTrimStart) {
                    liveTrimStart.value = String(Math.max(0, endValue - 1));
                }
                setTrimLabel(endValue, liveTrimEndLabel);
                if (liveEditDuration) {
                    liveEditDuration.textContent = 'Cắt: ' + (liveTrimStart ? liveTrimStart.value : startValue) + 's → ' + endValue + 's';
                }
                updateTrimSummary();
                updateTrimTrack();
            });
        }
    }

    function bindRecorderButtonListeners() {
        const liveStartBtn = document.getElementById('liveStartBtn');
        const liveStopBtn = document.getElementById('liveStopBtn');
        const liveUseBtn = document.getElementById('liveUseBtn');
        const liveRetakeBtn = document.getElementById('liveRetakeBtn');
        const liveCloseBtn = document.getElementById('liveCloseBtn');
        const liveApplyEditBtn = document.getElementById('liveApplyEditBtn');
        const liveBackToRecordBtn = document.getElementById('liveBackToRecordBtn');
        const liveUseEditedBtn = document.getElementById('liveUseEditedBtn');
        const uploadFeedback = document.getElementById('postUploadFeedback');
        const mediaInput = document.getElementById('videoMedia');

        // Edit buttons
        if (liveEditPreviewVideo && !liveEditPreviewVideo.dataset.bound) {
            liveEditPreviewVideo.dataset.bound = '1';
            liveEditPreviewVideo.addEventListener('loadedmetadata', function () {
                const duration = Number.isFinite(liveEditPreviewVideo.duration) ? liveEditPreviewVideo.duration : liveRecordingDuration;
                liveRecordingDuration = Math.max(1, Math.ceil(duration || liveRecordingDuration || 1));
                updateTrimUI(liveRecordingDuration);
            });
        }

        if (liveStartBtn && !liveStartBtn.dataset.bound) {
            liveStartBtn.dataset.bound = '1';
            liveStartBtn.addEventListener('click', function () {
                startLiveRecording().catch(function (error) {
                    if (uploadFeedback) {
                        uploadFeedback.classList.remove('d-none', 'text-muted');
                        uploadFeedback.classList.add('text-danger');
                        uploadFeedback.textContent = error && error.message ? error.message : 'Không thể bắt đầu quay.';
                    }
                });
            });
        }

        if (liveStopBtn && !liveStopBtn.dataset.bound) {
            liveStopBtn.dataset.bound = '1';
            liveStopBtn.addEventListener('click', stopLiveRecordingManually);
        }

        if (liveUseBtn && !liveUseBtn.dataset.bound) {
            liveUseBtn.dataset.bound = '1';
            liveUseBtn.addEventListener('click', function () {
                useRecordedLiveVideo(uploadFeedback, mediaInput);
            });
        }

        if (liveApplyEditBtn && !liveApplyEditBtn.dataset.bound) {
            liveApplyEditBtn.dataset.bound = '1';
            liveApplyEditBtn.addEventListener('click', async function () {
                if (!liveRecordedBlob) {
                    return;
                }

                liveApplyEditBtn.disabled = true;
                liveApplyEditBtn.textContent = 'Đang xử lý...';

                try {
                    liveEditedBlob = await renderEditedVideoFromRecordedBlob();
                    const liveUseEditedBtn = document.getElementById('liveUseEditedBtn');
                    const liveEditPreviewVideo = document.getElementById('liveEditPreviewVideo');
                    if (liveUseEditedBtn) {
                        liveUseEditedBtn.disabled = false;
                    }
                    if (liveEditPreviewVideo) {
                        liveEditPreviewVideo.pause();
                    }
                } catch (error) {
                    if (uploadFeedback) {
                        uploadFeedback.classList.remove('d-none', 'text-muted');
                        uploadFeedback.classList.add('text-danger');
                        uploadFeedback.textContent = error && error.message ? error.message : 'Không thể áp dụng chỉnh sửa.';
                    }
                } finally {
                    liveApplyEditBtn.disabled = false;
                    liveApplyEditBtn.textContent = 'Áp dụng chỉnh sửa';
                }
            });
        }

        if (liveBackToRecordBtn && !liveBackToRecordBtn.dataset.bound) {
            liveBackToRecordBtn.dataset.bound = '1';
            liveBackToRecordBtn.addEventListener('click', function () {
                const liveEditorPanel = document.getElementById('liveEditorPanel');
                const liveRecorderPanel = document.getElementById('liveRecorderPanel');
                if (liveEditorPanel) {
                    liveEditorPanel.classList.add('d-none');
                }
                if (liveRecorderPanel) {
                    liveRecorderPanel.classList.remove('d-none');
                }
            });
        }

        if (liveRetakeBtn && !liveRetakeBtn.dataset.bound) {
            liveRetakeBtn.dataset.bound = '1';
            liveRetakeBtn.addEventListener('click', retakeLiveVideo);
        }

        if (liveUseEditedBtn && !liveUseEditedBtn.dataset.bound) {
            liveUseEditedBtn.dataset.bound = '1';
            liveUseEditedBtn.addEventListener('click', function () {
                if (!liveEditedBlob) {
                    return;
                }
                useRecordedLiveVideo(uploadFeedback, mediaInput);
            });
        }

        if (liveCloseBtn && !liveCloseBtn.dataset.bound) {
            liveCloseBtn.dataset.bound = '1';
            liveCloseBtn.addEventListener('click', function () {
                const liveRecorderPanel = document.getElementById('liveRecorderPanel');
                if (liveRecorderPanel) {
                    liveRecorderPanel.classList.add('d-none');
                }
                stopLivePreview();
            });
        }
    }

    function bindMusicListeners() {
        const liveMusicVolume = document.getElementById('liveMusicVolume');
        const liveMusicSelect = document.getElementById('liveMusicSelect');
        const liveMusicPlayer = document.getElementById('liveMusicPlayer');

        if (liveMusicVolume && !liveMusicVolume.dataset.bound) {
            liveMusicVolume.dataset.bound = '1';
            liveMusicVolume.addEventListener('input', function () {
                if (liveMusicPlayer) {
                    liveMusicPlayer.volume = parseFloat(liveMusicVolume.value || '0.35');
                }
            });
        }

        if (liveMusicSelect && !liveMusicSelect.dataset.bound) {
            liveMusicSelect.dataset.bound = '1';
            liveMusicSelect.addEventListener('change', function () {
                playSelectedMusicPreview();
            });
        }

        if (liveMusicPlayer && !liveMusicPlayer.dataset.bound) {
            liveMusicPlayer.dataset.bound = '1';
            liveMusicPlayer.addEventListener('loadedmetadata', updateMusicTimeUI);
            liveMusicPlayer.addEventListener('timeupdate', updateMusicTimeUI);
            liveMusicPlayer.addEventListener('ended', updateMusicTimeUI);
            updateMusicTimeUI();
        }
    }

    function bindEditorButtonListeners() {
        const liveEditPreviewVideo = document.getElementById('liveEditPreviewVideo');
        
        if (liveEditPreviewVideo && !liveEditPreviewVideo.dataset.bound) {
            liveEditPreviewVideo.dataset.bound = '1';
            liveEditPreviewVideo.addEventListener('loadedmetadata', function () {
                const duration = Number.isFinite(liveEditPreviewVideo.duration) ? liveEditPreviewVideo.duration : liveRecordingDuration;
                liveRecordingDuration = Math.max(1, Math.ceil(duration || liveRecordingDuration || 1));
                updateTrimUI(liveRecordingDuration);
            });
        }
    }

    function previewVideoMedia(input) {
        const container = document.getElementById('mediaPreviewContainer');
        const video = document.getElementById('videoPreview');

        if (!container || !video || !input.files || !input.files[0]) {
            return;
        }

        const file = input.files[0];
        const objectUrl = URL.createObjectURL(file);
        container.classList.remove('d-none');

        if (video.dataset.objectUrl) {
            URL.revokeObjectURL(video.dataset.objectUrl);
        }
        video.dataset.objectUrl = objectUrl;
        video.src = objectUrl;
        video.style.display = 'block';
    }

    function clearVideoMedia() {
        const mediaInput = document.getElementById('videoMedia');
        const container = document.getElementById('mediaPreviewContainer');
        const video = document.getElementById('videoPreview');
        const uploadFeedback = document.getElementById('postUploadFeedback');

        if (mediaInput) {
            mediaInput.value = '';
        }
        if (container) {
            container.classList.add('d-none');
        }
        if (uploadFeedback) {
            uploadFeedback.classList.add('d-none');
        }
        if (video) {
            if (video.dataset.objectUrl) {
                URL.revokeObjectURL(video.dataset.objectUrl);
                delete video.dataset.objectUrl;
            }
            video.src = '';
            video.style.display = 'none';
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
            throw new Error('Thiếu cấu hình upload.');
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
                        throw new Error('Không thể tải dữ liệu lên.');
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
                        throw new Error('Server chưa xác nhận đủ dữ liệu.');
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
                    uploadFeedback.textContent = 'Đang tải lên: ' + percent + '%';
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
                uploadFeedback.textContent = 'Đang ghép file...';
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
                        message: 'Server đang xử lý, đang thử lại...'
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
                    uploadFeedback.textContent = 'Đã tải lên: 100%';
                }
                return completeData;
            }

            const missingChunks = Array.isArray(completeData.missing_chunks)
                ? completeData.missing_chunks
                : (typeof completeData.missing_chunk === 'number' ? [completeData.missing_chunk] : []);

            if (missingChunks.length === 0) {
                await new Promise(function (resolve) {
                    setTimeout(resolve, 500 * completeTry);
                });
                continue;
            }

            if (uploadFeedback) {
                uploadFeedback.classList.remove('d-none', 'text-danger');
                uploadFeedback.classList.add('text-muted');
                uploadFeedback.textContent = 'Đang tải bù ' + missingChunks.length + ' phần...';
            }

            for (let i = 0; i < missingChunks.length; i++) {
                await uploadOneChunk(missingChunks[i]);
            }

            if (completeTry === COMPLETE_RETRY_LIMIT - 1) {
                if (uploadFeedback) {
                    uploadFeedback.classList.remove('d-none', 'text-danger');
                    uploadFeedback.classList.add('text-muted');
                    uploadFeedback.textContent = 'Đang đồng bộ lại dữ liệu...';
                }
                await reuploadAllChunksOnce();
            }
        }

        throw new Error('Không thể hoàn tất upload.');
    }