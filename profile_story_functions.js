// Story Functions for Profile Page
window.previewProfileStory = function(input) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var preview = document.getElementById('profileStoryPreview');
        if (preview) preview.src = e.target.result;
        var modal = document.getElementById('profileStoryModal');
        if (modal && typeof bootstrap !== 'undefined') {
            new bootstrap.Modal(modal).show();
        }
    };
    reader.readAsDataURL(input.files[0]);
};

window.changeProfileStoryTextColor = function(color) {
    var caption = document.getElementById('profileStoryCaption');
    if (caption) caption.style.color = color;
    var input = document.getElementById('profileTextColorInput');
    if (input) input.value = color;
};

window.toggleProfileMusicList = function() {
    var list = document.getElementById('profileMusicList');
    if (list) list.classList.toggle('d-none');
};

window.selectProfileStoryMusic = function(path, name) {
    document.getElementById('profileMusicPathInput').value = path;
    document.getElementById('profileMusicNameInput').value = name;
    document.getElementById('profileSelectedMusic').textContent = name.substring(0, 15) + '...';
    document.getElementById('profileMusicList').classList.add('d-none');
};

window.increaseProfileStoryScale = function() {
    var input = document.getElementById('profileImageScaleInput');
    var current = parseFloat(input.value || '1');
    var newVal = Math.min(2, current + 0.1);
    input.value = newVal.toFixed(1);
    var preview = document.getElementById('profileStoryPreview');
    if (preview) preview.style.transform = 'scale(' + newVal + ')';
};

window.decreaseProfileStoryScale = function() {
    var input = document.getElementById('profileImageScaleInput');
    var current = parseFloat(input.value || '1');
    var newVal = Math.max(0.5, current - 0.1);
    input.value = newVal.toFixed(1);
    var preview = document.getElementById('profileStoryPreview');
    if (preview) preview.style.transform = 'scale(' + newVal + ')';
};
