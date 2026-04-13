// ============================================================================
// VIDEO RECORDER MODULE
// Extracted from newsfeed.blade.php for modularity and reusability
// ============================================================================

// Global state variables
let liveStream = null;
let liveRecorder = null;
let liveRecordedBlob = null;
let liveRecordChunks = [];
let liveRecordTimer = null;
let liveRecordElapsed = 0;
let liveAutoStopTimer = null;
let liveAudioContext = null;
let liveMusicSourceNode = null;
let liveRecordingMusicPlayer = null;
let liveRecordingMusicSourceNode = null;
let liveRecordingMusicCaptureStream = null;
let liveMusicTimeTicker = null;
let liveEditedBlob = null;
let liveRecordingDuration = 0;

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

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

function resetLiveTimer() {
    liveRecordElapsed = 0;
    const timerEl = document.getElementById('liveRecordTimer');
    if (timerEl) {
        timerEl.textContent = '00:00';
        timerEl.classList.remove('bg-danger');
        timerEl.classList.add('bg-secondary');
    }
}

function stopLiveTimers() {
    if (liveRecordTimer) {
        clearInterval(liveRecordTimer);
        liveRecordTimer = null;
    }
    if (liveAutoStopTimer) {
        clearTimeout(liveAutoStopTimer);
        liveAutoStopTimer = null;
    }
}

function clearLiveUploadStatusUI() {
    const uploadFeedback = document.getElementById('videoUploadFeedback') || document.getElementById('postUploadFeedback');
    const progressWrap = document.getElementById('videoUploadProgressWrap') || document.getElementById('postUploadProgressWrap');
    const progressBar = document.getElementById('videoUploadProgressBar') || document.getElementById('postUploadProgressBar');

    if (uploadFeedback) {
        uploadFeedback.classList.add('d-none');
        uploadFeedback.classList.remove('text-danger', 'text-success');
        uploadFeedback.classList.add('text-muted');
        uploadFeedback.textContent = '';
    }

    if (progressWrap) {
        progressWrap.classList.add('d-none');
    }

    if (progressBar) {
        progressBar.style.width = '0%';
        progressBar.setAttribute('aria-valuenow', '0');
        progressBar.textContent = '0%';
    }
}

// ============================================================================
// TRIM UI FUNCTIONS
// ============================================================================

function updateTrimUI(durationSeconds) {
    const total = Math.max(0, Math.floor(durationSeconds || 0));
    const liveTrimStart = document.getElementById('liveTrimStart');
    const liveTrimEnd = document.getElementById('liveTrimEnd');
    const liveEditDuration = document.getElementById('liveEditDuration');
    const liveTrimSummary = document.getElementById('liveTrimSummary');
    const liveTrimStartLabel = document.getElementById('liveTrimStartLabel');
    const liveTrimEndLabel = document.getElementById('liveTrimEndLabel');

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
        liveEditDuration.textContent = 'Cắt: 0s → ' + total + 's';
    }
    if (liveTrimSummary) {
        liveTrimSummary.textContent = 'Độ dài đã cắt: ' + total + 's';
    }
    setTrimLabel(0, liveTrimStartLabel);
    setTrimLabel(total, liveTrimEndLabel);
    updateTrimTrack();
}

function updateTrimSummary() {
    const liveTrimSummary = document.getElementById('liveTrimSummary');
    const liveTrimStart = document.getElementById('liveTrimStart');
    const liveTrimEnd = document.getElementById('liveTrimEnd');

    if (!liveTrimSummary || !liveTrimStart || !liveTrimEnd) {
        return;
    }

    const startValue = parseInt(liveTrimStart.value || '0', 10);
    const endValue = parseInt(liveTrimEnd.value || '0', 10);
    const trimmedLength = Math.max(0, endValue - startValue);
    liveTrimSummary.textContent = 'Độ dài đã cắt: ' + trimmedLength + 's';
}

function updateTrimTrack() {
    const liveTrimTrackSelected = document.getElementById('liveTrimTrackSelected');
    const liveTrimTrackStartMarker = document.getElementById('liveTrimTrackStartMarker');
    const liveTrimTrackEndMarker = document.getElementById('liveTrimTrackEndMarker');
    const liveTrimStart = document.getElementById('liveTrimStart');
    const liveTrimEnd = document.getElementById('liveTrimEnd');

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

// ============================================================================
// AUDIO/MUSIC FUNCTIONS
// ============================================================================

function stopMusicTicker() {
    if (liveMusicTimeTicker) {
        clearInterval(liveMusicTimeTicker);
        liveMusicTimeTicker = null;
    }
}

function stopRecordingMusicPlayback() {
    if (liveRecordingMusicPlayer) {
        liveRecordingMusicPlayer.pause();
        liveRecordingMusicPlayer.currentTime = 0;
    }

    if (liveRecordingMusicSourceNode && typeof liveRecordingMusicSourceNode.stop === 'function') {
        try {
            liveRecordingMusicSourceNode.stop();
        } catch (error) {
            // Ignore cleanup errors.
        }
    }

    if (liveRecordingMusicPlayer) {
        liveRecordingMusicPlayer.src = '';
        liveRecordingMusicPlayer = null;
    }

    if (liveRecordingMusicCaptureStream) {
        liveRecordingMusicCaptureStream.getTracks().forEach(function (track) {
            track.stop();
        });
        liveRecordingMusicCaptureStream = null;
    }

    if (liveRecordingMusicSourceNode && typeof liveRecordingMusicSourceNode.disconnect === 'function') {
        try {
            liveRecordingMusicSourceNode.disconnect();
        } catch (error) {
            // Ignore cleanup errors.
        }
    }

    liveRecordingMusicSourceNode = null;
}

function updateMusicTimeUI() {
    const liveMusicTime = document.getElementById('liveMusicTime');
    const liveMusicPlayer = document.getElementById('liveMusicPlayer');

    if (!liveMusicTime || !liveMusicPlayer) {
        return;
    }

    const current = liveMusicPlayer.currentTime || 0;
    const duration = Number.isFinite(liveMusicPlayer.duration) ? liveMusicPlayer.duration : 0;
    liveMusicTime.textContent = formatMusicTime(current) + ' / ' + formatMusicTime(duration);
}

function startMusicTicker() {
    stopMusicTicker();
    updateMusicTimeUI();
    liveMusicTimeTicker = setInterval(updateMusicTimeUI, 500);
}

async function playSelectedMusicPreview() {
    const liveMusicPlayer = document.getElementById('liveMusicPlayer');
    const liveMusicSelect = document.getElementById('liveMusicSelect');
    const liveMusicVolume = document.getElementById('liveMusicVolume');

    if (!liveMusicPlayer || !liveMusicSelect) {
        return;
    }

    const musicUrl = liveMusicSelect.value || '';
    if (!musicUrl) {
        stopRecordingMusicPlayback();
        liveMusicPlayer.pause();
        liveMusicPlayer.removeAttribute('src');
        liveMusicPlayer.load();
        stopMusicTicker();
        updateMusicTimeUI();
        return;
    }

    stopRecordingMusicPlayback();
    liveMusicPlayer.src = musicUrl;
    liveMusicPlayer.loop = true;
    liveMusicPlayer.currentTime = 0;
    liveMusicPlayer.volume = parseFloat((liveMusicVolume && liveMusicVolume.value) || '0.35');
    liveMusicPlayer.load();

    startMusicTicker();
    try {
        await liveMusicPlayer.play();
    } catch (error) {
        // Trình duyệt có thể chặn autoplay nếu chưa có tương tác người dùng.
    }
}

// ============================================================================
// VIDEO INITIALIZATION FUNCTIONS
// ============================================================================

async function initLivePreview() {
    if (liveStream) {
        return true;
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        throw new Error('Trình duyệt không hỗ trợ quay video trực tiếp.');
    }

    liveStream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user' },
        audio: true,
    });

    const livePreviewVideo = document.getElementById('livePreviewVideo');
    if (livePreviewVideo) {
        livePreviewVideo.srcObject = liveStream;
    }

    return true;
}

function stopLivePreview() {
    stopLiveTimers();
    clearLiveUploadStatusUI();

    if (liveRecorder && liveRecorder.state !== 'inactive') {
        liveRecorder.stop();
    }
    liveRecorder = null;

    if (liveStream) {
        liveStream.getTracks().forEach(function (track) {
            track.stop();
        });
        liveStream = null;
    }

    const livePreviewVideo = document.getElementById('livePreviewVideo');
    if (livePreviewVideo) {
        livePreviewVideo.srcObject = null;
    }

    const liveMusicPlayer = document.getElementById('liveMusicPlayer');
    if (liveMusicPlayer) {
        liveMusicPlayer.pause();
        liveMusicPlayer.currentTime = 0;
    }

    stopMusicTicker();
    updateMusicTimeUI();

    if (liveAudioContext) {
        liveAudioContext.close().catch(function () {});
        liveAudioContext = null;
        liveMusicSourceNode = null;
    }

    stopRecordingMusicPlayback();

    const liveStartBtn = document.getElementById('liveStartBtn');
    const liveStopBtn = document.getElementById('liveStopBtn');
    const liveUseBtn = document.getElementById('liveUseBtn');
    const liveRetakeBtn = document.getElementById('liveRetakeBtn');
    const liveUseEditedBtn = document.getElementById('liveUseEditedBtn');

    if (liveStartBtn) {
        liveStartBtn.disabled = false;
    }
    if (liveStopBtn) {
        liveStopBtn.disabled = true;
    }
    if (liveUseBtn) {
        liveUseBtn.disabled = !liveRecordedBlob;
    }
    if (liveRetakeBtn) {
        liveRetakeBtn.classList.toggle('d-none', !liveRecordedBlob);
    }

    if (liveUseEditedBtn) {
        liveUseEditedBtn.disabled = !liveEditedBlob;
    }

    resetLiveTimer();
}

function showLiveEditorWithBlob(blob) {
    const liveEditorPanel = document.getElementById('liveEditorPanel');
    const liveEditPreviewVideo = document.getElementById('liveEditPreviewVideo');
    const liveRecorderPanel = document.getElementById('liveRecorderPanel');
    const liveUseEditedBtn = document.getElementById('liveUseEditedBtn');

    if (!blob || !liveEditorPanel || !liveEditPreviewVideo) {
        return;
    }

    const editorUrl = URL.createObjectURL(blob);
    if (liveEditPreviewVideo.dataset.objectUrl) {
        URL.revokeObjectURL(liveEditPreviewVideo.dataset.objectUrl);
    }
    liveEditPreviewVideo.dataset.objectUrl = editorUrl;
    liveEditPreviewVideo.src = editorUrl;
    liveEditPreviewVideo.load();

    liveEditedBlob = null;
    if (liveUseEditedBtn) {
        liveUseEditedBtn.disabled = true;
    }
    liveEditorPanel.classList.remove('d-none');
    if (liveRecorderPanel) {
        liveRecorderPanel.classList.add('d-none');
    }
    liveEditPreviewVideo.currentTime = 0;
    liveEditPreviewVideo.play().catch(function () {});
}

function retakeLiveVideo() {
    const liveUseBtn = document.getElementById('liveUseBtn');
    const liveUseEditedBtn = document.getElementById('liveUseEditedBtn');
    const liveRetakeBtn = document.getElementById('liveRetakeBtn');
    const liveEditPreviewVideo = document.getElementById('liveEditPreviewVideo');
    const liveEditorPanel = document.getElementById('liveEditorPanel');
    const liveRecorderPanel = document.getElementById('liveRecorderPanel');

    liveRecordedBlob = null;
    liveEditedBlob = null;
    liveRecordingDuration = 0;
    liveRecordChunks = [];

    if (liveUseBtn) {
        liveUseBtn.disabled = true;
    }
    if (liveUseEditedBtn) {
        liveUseEditedBtn.disabled = true;
    }
    if (liveRetakeBtn) {
        liveRetakeBtn.classList.add('d-none');
    }

    if (liveEditPreviewVideo) {
        liveEditPreviewVideo.pause();
        if (liveEditPreviewVideo.dataset.objectUrl) {
            URL.revokeObjectURL(liveEditPreviewVideo.dataset.objectUrl);
            delete liveEditPreviewVideo.dataset.objectUrl;
        }
        liveEditPreviewVideo.removeAttribute('src');
        liveEditPreviewVideo.load();
    }

    if (liveEditorPanel) {
        liveEditorPanel.classList.add('d-none');
    }
    if (liveRecorderPanel) {
        liveRecorderPanel.classList.remove('d-none');
    }

    resetLiveTimer();
    stopRecordingMusicPlayback();

    clearLiveUploadStatusUI();
}

// ============================================================================
// VIDEO RENDERING FUNCTIONS
// ============================================================================

async function renderEditedVideoFromRecordedBlob() {
    if (!liveRecordedBlob) {
        throw new Error('Chưa có video để chỉnh sửa.');
    }

    const liveTrimStart = document.getElementById('liveTrimStart');
    const liveTrimEnd = document.getElementById('liveTrimEnd');
    const liveEditPreviewVideo = document.getElementById('liveEditPreviewVideo');

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
            reject(new Error('Không đọc được video đã quay để chỉnh sửa.'));
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
            reject(new Error('Không thể xuất video đã chỉnh.'));
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

    const sourceUrl = URL.createObjectURL(videoBlob);
    const sourceVideo = document.createElement('video');
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const audioDestination = audioContext.createMediaStreamDestination();

    sourceVideo.src = sourceUrl;
    sourceVideo.muted = true;
    sourceVideo.playsInline = true;
    sourceVideo.preload = 'auto';

    await new Promise(function (resolve, reject) {
        sourceVideo.onloadedmetadata = function () {
            canvas.width = Math.max(2, sourceVideo.videoWidth || 720);
            canvas.height = Math.max(2, sourceVideo.videoHeight || 1280);
            resolve();
        };
        sourceVideo.onerror = function () {
            reject(new Error('Không thể đọc video vừa quay để ghép nhạc.'));
        };
    });

    const musicResponse = await fetch(musicUrl, { cache: 'no-store' });
    if (!musicResponse.ok) {
        URL.revokeObjectURL(sourceUrl);
        audioContext.close().catch(function () {});
        throw new Error('Không thể tải nhạc để ghép vào video.');
    }

    const musicArrayBuffer = await musicResponse.arrayBuffer();
    const musicBuffer = await audioContext.decodeAudioData(musicArrayBuffer.slice(0));
    const musicSource = audioContext.createBufferSource();
    const musicGain = audioContext.createGain();
    musicSource.buffer = musicBuffer;
    musicSource.loop = true;
    musicGain.gain.value = Math.max(0, Math.min(1, parseFloat(musicVolume || '0.35')));
    musicSource.connect(musicGain).connect(audioDestination);

    const outStream = canvas.captureStream(30);
    const audioTrack = audioDestination.stream.getAudioTracks()[0];
    if (audioTrack) {
        outStream.addTrack(audioTrack);
    }

    const mimeType = MediaRecorder.isTypeSupported('video/webm;codecs=vp8,opus')
        ? 'video/webm;codecs=vp8,opus'
        : 'video/webm';
    const recorder = new MediaRecorder(outStream, {
        mimeType: mimeType,
        videoBitsPerSecond: 2500000,
        audioBitsPerSecond: 128000,
    });
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
            if (typeof onProgress === 'function') {
                onProgress(100);
            }
            URL.revokeObjectURL(sourceUrl);
            audioContext.close().catch(function () {});
            resolve(new Blob(chunks, { type: 'video/webm' }));
        };
        recorder.onerror = function () {
            if (rafId) {
                cancelAnimationFrame(rafId);
            }
            URL.revokeObjectURL(sourceUrl);
            audioContext.close().catch(function () {});
            reject(new Error('Không thể xuất video đã ghép nhạc.'));
        };
    });

    function drawFrame() {
        if (sourceVideo.ended) {
            if (recorder.state !== 'inactive') {
                recorder.stop();
            }
            return;
        }

        if (typeof onProgress === 'function') {
            const duration = Number.isFinite(sourceVideo.duration) && sourceVideo.duration > 0
                ? sourceVideo.duration
                : 0;
            const current = Math.max(0, sourceVideo.currentTime || 0);
            const percent = duration > 0 ? Math.min(99, Math.floor((current / duration) * 100)) : 1;
            onProgress(Math.max(1, percent));
        }

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(sourceVideo, 0, 0, canvas.width, canvas.height);
        rafId = requestAnimationFrame(drawFrame);
    }

    await audioContext.resume().catch(function () {});
    if (typeof onProgress === 'function') {
        onProgress(1);
    }
    recorder.start(1000);
    musicSource.start(0);
    await sourceVideo.play().catch(function () {
        throw new Error('Không thể phát video để ghép nhạc.');
    });
    drawFrame();

    return await finished;
}

// ============================================================================
// RECORDING CONTROL FUNCTIONS
// ============================================================================

async function startLiveRecording() {
    const liveMusicSelect = document.getElementById('liveMusicSelect');
    const liveMusicVolume = document.getElementById('liveMusicVolume');
    const liveMusicPlayer = document.getElementById('liveMusicPlayer');
    const liveDurationSelect = document.getElementById('liveDurationSelect');
    const liveStartBtn = document.getElementById('liveStartBtn');
    const liveStopBtn = document.getElementById('liveStopBtn');
    const liveUseBtn = document.getElementById('liveUseBtn');
    const liveRetakeBtn = document.getElementById('liveRetakeBtn');
    const liveUseEditedBtn = document.getElementById('liveUseEditedBtn');
    const liveRecordTimerEl = document.getElementById('liveRecordTimer');
    const uploadFeedback = document.getElementById('videoUploadFeedback') || document.getElementById('postUploadFeedback');

    await initLivePreview();

    liveRecordedBlob = null;
    liveEditedBlob = null;
    liveRecordChunks = [];
    if (liveUseBtn) {
        liveUseBtn.disabled = true;
    }
    if (liveUseEditedBtn) {
        liveUseEditedBtn.disabled = true;
    }
    if (liveRetakeBtn) {
        liveRetakeBtn.classList.add('d-none');
    }

    const rawDurationValue = (liveDurationSelect && liveDurationSelect.value) || '180';
    let maxDurationSec = parseInt(rawDurationValue, 10);
    // Hard guard: tránh trường hợp value bị parse nhầm thành 5 giây (ví dụ "5 phút").
    if (!Number.isFinite(maxDurationSec) || maxDurationSec < 30) {
        maxDurationSec = 180;
    }
    const videoTrack = liveStream ? liveStream.getVideoTracks()[0] : null;
    const audioTrack = liveStream ? liveStream.getAudioTracks()[0] : null;

    if (!videoTrack) {
        throw new Error('Không tìm thấy camera để quay video.');
    }

    const outStream = new MediaStream();
    outStream.addTrack(videoTrack);

    if (liveMusicPlayer && liveMusicSelect && liveMusicSelect.value) {
        liveMusicPlayer.src = liveMusicSelect.value;
        liveMusicPlayer.loop = true;
        liveMusicPlayer.volume = parseFloat((liveMusicVolume && liveMusicVolume.value) || '0.35');
        startMusicTicker();

        try {
            await liveMusicPlayer.play();
        } catch (error) {
            // If the browser still blocks autoplay, continue recording silently.
        }
    }

    if (audioTrack || (liveMusicPlayer && liveMusicSelect && liveMusicSelect.value)) {
        liveAudioContext = new (window.AudioContext || window.webkitAudioContext)();
        const destination = liveAudioContext.createMediaStreamDestination();
        await liveAudioContext.resume().catch(function () {});

        stopRecordingMusicPlayback();

        if (audioTrack) {
            const micSource = liveAudioContext.createMediaStreamSource(new MediaStream([audioTrack]));
            const micGain = liveAudioContext.createGain();
            micGain.gain.value = 1;
            micSource.connect(micGain).connect(destination);
        }

        if (liveMusicPlayer && liveMusicSelect && liveMusicSelect.value) {
            const musicVolume = parseFloat((liveMusicVolume && liveMusicVolume.value) || '0.35');
            let musicConnected = false;

            const captureStreamFn = liveMusicPlayer.captureStream || liveMusicPlayer.mozCaptureStream;
            if (captureStreamFn) {
                try {
                    liveRecordingMusicCaptureStream = captureStreamFn.call(liveMusicPlayer);
                    const musicTrack = liveRecordingMusicCaptureStream.getAudioTracks()[0] || null;
                    if (musicTrack) {
                        const recordingMusicSource = liveAudioContext.createMediaStreamSource(liveRecordingMusicCaptureStream);
                        const recordingMusicGain = liveAudioContext.createGain();
                        recordingMusicGain.gain.value = musicVolume;
                        recordingMusicSource.connect(recordingMusicGain).connect(destination);
                        liveRecordingMusicSourceNode = recordingMusicSource;
                        musicConnected = true;
                    }
                } catch (error) {
                    musicConnected = false;
                }
            }

            try {
                if (!musicConnected) {
                    const musicResponse = await fetch(liveMusicSelect.value, { cache: 'no-store' });
                    if (!musicResponse.ok) {
                        throw new Error('Không tải được nhạc nền.');
                    }

                    const musicArrayBuffer = await musicResponse.arrayBuffer();
                    const musicBuffer = await liveAudioContext.decodeAudioData(musicArrayBuffer.slice(0));
                    const recordingMusicSource = liveAudioContext.createBufferSource();
                    const recordingMusicGain = liveAudioContext.createGain();

                    recordingMusicSource.buffer = musicBuffer;
                    recordingMusicSource.loop = true;
                    recordingMusicGain.gain.value = musicVolume;
                    recordingMusicSource.connect(recordingMusicGain).connect(destination);
                    recordingMusicSource.start(0);
                    liveRecordingMusicSourceNode = recordingMusicSource;
                    musicConnected = true;
                }
            } catch (error) {
                // fallback below
            }

            if (!musicConnected) {
                liveRecordingMusicPlayer = new Audio(liveMusicSelect.value);
                liveRecordingMusicPlayer.loop = true;
                liveRecordingMusicPlayer.crossOrigin = 'anonymous';
                liveRecordingMusicPlayer.muted = false;
                liveRecordingMusicPlayer.volume = musicVolume;

                liveRecordingMusicSourceNode = liveAudioContext.createMediaElementSource(liveRecordingMusicPlayer);
                const recordingMusicGain = liveAudioContext.createGain();
                recordingMusicGain.gain.value = musicVolume;
                liveRecordingMusicSourceNode.connect(recordingMusicGain).connect(destination);

                try {
                    await liveRecordingMusicPlayer.play();
                } catch (error) {
                    if (uploadFeedback) {
                        uploadFeedback.classList.remove('d-none', 'text-muted');
                        uploadFeedback.classList.add('text-danger');
                        uploadFeedback.textContent = 'Không thể phát nhạc nền để ghi vào video.';
                    }
                }
            }
        }

        const mixedAudioTrack = destination.stream.getAudioTracks()[0];
        if (mixedAudioTrack) {
            outStream.addTrack(mixedAudioTrack);
        }
    }

    const mimeType = MediaRecorder.isTypeSupported('video/webm;codecs=vp8,opus')
        ? 'video/webm;codecs=vp8,opus'
        : 'video/webm';

    liveRecorder = new MediaRecorder(outStream, {
        mimeType: mimeType,
        videoBitsPerSecond: 2200000,
        audioBitsPerSecond: 128000,
    });

    liveRecorder.ondataavailable = function (event) {
        if (event.data && event.data.size > 0) {
            liveRecordChunks.push(event.data);
        }
    };

    liveRecorder.onstop = async function () {
        stopLiveTimers();

        if (liveRecordChunks.length > 0) {
            const rawBlob = new Blob(liveRecordChunks, { type: 'video/webm' });
            const selectedMusicUrl = (liveMusicSelect && liveMusicSelect.value) || '';

            if (selectedMusicUrl && uploadFeedback) {
                uploadFeedback.classList.remove('d-none', 'text-danger', 'text-success');
                uploadFeedback.classList.add('text-muted');
                uploadFeedback.textContent = 'Đang ghép nhạc vào video...';
                const progressWrap = document.getElementById('videoUploadProgressWrap') || document.getElementById('postUploadProgressWrap');
                const progressBar = document.getElementById('videoUploadProgressBar') || document.getElementById('postUploadProgressBar');
                if (progressWrap && progressBar) {
                    progressWrap.classList.remove('d-none');
                    updateProgressBar(progressBar, 0);
                }
            }

            try {
                liveRecordedBlob = selectedMusicUrl
                    ? await mergeSelectedMusicIntoRecordedBlob(
                        rawBlob,
                        selectedMusicUrl,
                        (liveMusicVolume && liveMusicVolume.value) || '0.35',
                        function (percent) {
                            const progressWrap = document.getElementById('videoUploadProgressWrap') || document.getElementById('postUploadProgressWrap');
                            const progressBar = document.getElementById('videoUploadProgressBar') || document.getElementById('postUploadProgressBar');
                            if (progressWrap && progressBar) {
                                progressWrap.classList.remove('d-none');
                                updateProgressBar(progressBar, percent);
                            }
                            if (uploadFeedback) {
                                uploadFeedback.classList.remove('d-none', 'text-danger', 'text-success');
                                uploadFeedback.classList.add('text-muted');
                                uploadFeedback.textContent = 'Đang ghép nhạc vào video... ' + Math.min(100, Math.max(0, Math.floor(percent))) + '%';
                            }
                        }
                    )
                    : rawBlob;

                if (selectedMusicUrl && uploadFeedback) {
                    uploadFeedback.classList.remove('d-none', 'text-danger');
                    uploadFeedback.classList.add('text-success');
                    uploadFeedback.textContent = 'Đã ghép nhạc vào video: 100%';
                }

                const progressWrap = document.getElementById('videoUploadProgressWrap') || document.getElementById('postUploadProgressWrap');
                if (progressWrap) {
                    setTimeout(function () {
                        progressWrap.classList.add('d-none');
                    }, 700);
                }
            } catch (error) {
                liveRecordedBlob = rawBlob;
                if (uploadFeedback) {
                    uploadFeedback.classList.remove('d-none', 'text-muted', 'text-success');
                    uploadFeedback.classList.add('text-danger');
                    uploadFeedback.textContent = error && error.message
                        ? error.message
                        : 'Ghép nhạc thất bại, dùng video gốc không nhạc.';
                }
            }

            if (liveUseBtn) {
                liveUseBtn.disabled = false;
            }
            if (liveRetakeBtn) {
                liveRetakeBtn.classList.remove('d-none');
            }
            liveRecordingDuration = Math.max(1, liveRecordElapsed || Math.ceil(maxDurationSec) || 1);
            showLiveEditorWithBlob(liveRecordedBlob);
        }

        if (liveMusicPlayer) {
            liveMusicPlayer.pause();
            liveMusicPlayer.currentTime = 0;
        }

        stopRecordingMusicPlayback();

        if (liveAudioContext) {
            liveAudioContext.close().catch(function () {});
            liveAudioContext = null;
            liveMusicSourceNode = null;
        }

        if (liveStartBtn) {
            liveStartBtn.disabled = false;
        }
        if (liveStopBtn) {
            liveStopBtn.disabled = true;
        }
        if (liveRecordTimerEl) {
            liveRecordTimerEl.classList.remove('bg-danger');
            liveRecordTimerEl.classList.add('bg-secondary');
        }
    };

    liveRecorder.start(1000);

    if (liveStartBtn) {
        liveStartBtn.disabled = true;
    }
    if (liveStopBtn) {
        liveStopBtn.disabled = false;
    }
    if (liveRecordTimerEl) {
        liveRecordTimerEl.classList.remove('bg-secondary');
        liveRecordTimerEl.classList.add('bg-danger');
    }

    resetLiveTimer();
    liveRecordTimer = setInterval(function () {
        liveRecordElapsed++;
        if (liveRecordTimerEl) {
            liveRecordTimerEl.textContent = formatLiveTime(liveRecordElapsed);
        }
    }, 1000);

    liveAutoStopTimer = setTimeout(function () {
        if (liveRecorder && liveRecorder.state !== 'inactive') {
            liveRecorder.stop();
        }
    }, maxDurationSec * 1000);
}

function stopLiveRecordingManually() {
    const liveMusicPlayer = document.getElementById('liveMusicPlayer');

    if (liveMusicPlayer) {
        liveMusicPlayer.pause();
        liveMusicPlayer.currentTime = 0;
    }
    stopMusicTicker();
    updateMusicTimeUI();
    stopRecordingMusicPlayback();

    if (liveRecorder && liveRecorder.state !== 'inactive') {
        liveRecorder.stop();
    }
}

function useRecordedLiveVideo(uploadFeedback, mediaInput) {
    const blobToUse = liveEditedBlob || liveRecordedBlob;
    if (!blobToUse || !mediaInput) {
        return;
    }

    const liveFile = new File([blobToUse], 'live-' + Date.now() + '.webm', {
        type: 'video/webm'
    });

    const dt = new DataTransfer();
    dt.items.add(liveFile);
    mediaInput.files = dt.files;

    const composerContainer = document.getElementById('videoMediaPreviewContainer') || document.getElementById('mediaPreviewContainer');
    const composerVideo = document.getElementById('videoComposerPreviewVideo') || document.getElementById('videoPreview');
    const composerImage = document.getElementById('imgPreview');
    const objectUrl = URL.createObjectURL(blobToUse);

    if (composerContainer) {
        composerContainer.classList.remove('d-none');
    }
    if (composerVideo) {
        if (composerVideo.dataset.objectUrl) {
            URL.revokeObjectURL(composerVideo.dataset.objectUrl);
        }
        composerVideo.dataset.objectUrl = objectUrl;
        composerVideo.src = objectUrl;
        composerVideo.style.display = 'block';
        if (typeof composerVideo.load === 'function') {
            composerVideo.load();
        }
    }
    if (composerImage) {
        composerImage.style.display = 'none';
    }

    const liveRecorderPanel = document.getElementById('liveRecorderPanel');
    const liveEditorPanel = document.getElementById('liveEditorPanel');

    if (liveRecorderPanel) {
        liveRecorderPanel.classList.add('d-none');
    }
    if (liveEditorPanel) {
        liveEditorPanel.classList.add('d-none');
    }
    stopLivePreview();

    if (uploadFeedback) {
        uploadFeedback.classList.remove('d-none', 'text-danger');
        uploadFeedback.classList.add('text-muted');
        uploadFeedback.textContent = 'Đã thêm video vừa quay vào bài viết.';
    }
}

// ============================================================================
// UI BINDING FUNCTIONS
// ============================================================================

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
                liveEditDuration.textContent = 'Cắt: ' + startValue + 's -> ' + (liveTrimEnd ? liveTrimEnd.value : endValue) + 's';
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
                liveEditDuration.textContent = 'Cắt: ' + (liveTrimStart ? liveTrimStart.value : startValue) + 's -> ' + endValue + 's';
            }
            updateTrimSummary();
            updateTrimTrack();
        });
    }
}

function bindRecorderButtonListeners() {
    const liveEditPreviewVideo = document.getElementById('liveEditPreviewVideo');
    const liveStartBtn = document.getElementById('liveStartBtn');
    const liveStopBtn = document.getElementById('liveStopBtn');
    const liveUseBtn = document.getElementById('liveUseBtn');
    const liveRetakeBtn = document.getElementById('liveRetakeBtn');
    const liveCloseBtn = document.getElementById('liveCloseBtn');
    const liveApplyEditBtn = document.getElementById('liveApplyEditBtn');
    const liveBackToRecordBtn = document.getElementById('liveBackToRecordBtn');
    const liveUseEditedBtn = document.getElementById('liveUseEditedBtn');
    const uploadFeedback = document.getElementById('videoUploadFeedback') || document.getElementById('postUploadFeedback');
    const mediaInput = document.getElementById('videoMedia');

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
                    uploadFeedback.textContent = error && error.message ? error.message : 'Khong the bat dau quay.';
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
            liveApplyEditBtn.textContent = 'Dang xu ly...';

            try {
                liveEditedBlob = await renderEditedVideoFromRecordedBlob();
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
                    uploadFeedback.textContent = error && error.message ? error.message : 'Khong the ap dung chinh sua.';
                }
            } finally {
                liveApplyEditBtn.disabled = false;
                liveApplyEditBtn.textContent = 'Ap dung chinh sua';
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
