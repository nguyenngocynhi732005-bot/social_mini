document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function uploadGroupImage(fileInput) {
        if (!fileInput || !fileInput.files || !fileInput.files[0]) {
            return;
        }

        const groupHeader = document.getElementById('group-header');
        if (!groupHeader) {
            return;
        }

        const groupId = groupHeader.dataset.groupId;
        const uploadField = fileInput.dataset.uploadField;
        const file = fileInput.files[0];
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append(uploadField, file);

        fetch(`/social/groups/${groupId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (!data.group) {
                alert(data.message || 'Không thể cập nhật ảnh nhóm.');
                return;
            }

            const coverImg = document.querySelector('.js-group-cover');
            const avatarImg = document.querySelector('.js-group-avatar');

            if (uploadField === 'cover_image' && coverImg && data.group.cover_image) {
                coverImg.src = data.group.cover_image;
            }

            if (uploadField === 'avatar_image' && avatarImg && data.group.avatar_image) {
                avatarImg.src = data.group.avatar_image;
            }
        })
        .catch(err => {
            alert('Lỗi cập nhật ảnh nhóm: ' + err);
        })
        .finally(() => {
            fileInput.value = '';
        });
    }

    const pickCoverBtn = document.querySelector('.js-pick-group-cover-btn');
    const coverInput = document.querySelector('.js-group-cover-input');
    if (pickCoverBtn && coverInput) {
        pickCoverBtn.addEventListener('click', function() {
            coverInput.click();
        });

        coverInput.addEventListener('change', function() {
            uploadGroupImage(this);
        });
    }

    const pickAvatarBtn = document.querySelector('.js-pick-group-avatar-btn');
    const avatarInput = document.querySelector('.js-group-avatar-input');
    if (pickAvatarBtn && avatarInput) {
        pickAvatarBtn.addEventListener('click', function() {
            avatarInput.click();
        });

        avatarInput.addEventListener('change', function() {
            uploadGroupImage(this);
        });
    }

    // --- 1. XỬ LÝ ĐĂNG BÀI TRONG NHÓM ---
    const postForm = document.getElementById('form-create-group-post');
    if (postForm) {
        postForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = this.querySelector('.js-submit-post-btn');
            const formData = new FormData(this);

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Đang đăng...';
            }

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    const firstError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
                    throw new Error(firstError || data.message || 'Không thể đăng bài.');
                }
                return data;
            })
            .then(data => {
                if (data.success && data.post) {
                    const feed = document.getElementById('group-posts-feed');
                    const emptyState = document.querySelector('.js-empty-posts');

                    if (!feed) {
                        return;
                    }

                    const imageHtml = data.post.image_url
                        ? `<img src="${data.post.image_url}" class="img-fluid rounded mt-2 shadow-sm" style="max-height: 400px; object-fit: cover;">`
                        : '';

                    const newPostHtml = `
                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px; animation: fadeIn 0.5s;">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="${data.post.avatar_url}" class="rounded-circle me-2" width="38" height="38" alt="${data.post.user_name}">
                                    <h6 class="mb-0 fw-bold text-dark">${data.post.user_name}</h6>
                                </div>
                                <p class="mb-1 text-dark" style="white-space: pre-wrap;">${data.post.content}</p>
                                ${imageHtml}
                            </div>
                        </div>
                    `;

                    if (emptyState) emptyState.remove();
                    feed.insertAdjacentHTML('afterbegin', newPostHtml);
                    
                    postForm.reset();
                    
                    const preview = document.getElementById('post-image-preview');
                    if (preview) {
                        preview.classList.add('d-none');
                    }
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            })
            .catch(err => {
                alert('Lỗi đăng bài: ' + (err.message || err));
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Đăng ngay';
                }
            });
        });
    }

    // --- 2. THAM GIA, KÍCH, PHONG ADMIN ---
    // Xử lý Kích thành viên
    document.querySelectorAll('.kick-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!confirm('Xác nhận mời thành viên này rời nhóm?')) return;

            const item = this.closest('.group-member-item');
            fetch(`/social/groups/${document.getElementById('group-header').dataset.groupId}/members/${this.dataset.userId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(() => {
                if (!item) {
                    return;
                }

                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '0';
                item.style.transform = 'translateX(20px)';
                setTimeout(() => item.remove(), 300);
            });
        });
    });

    // Các phần Join và Promote của Anh đã ổn, có thể giữ nguyên!
});