<x-guest-layout>
    <div class="auth-register-page">
    <x-auth-card>

        <x-slot name="logo">
            <a href="/">
                <img src="{{ asset('storage/bìa.png') }}" alt="Social Mini Logo" class="login-logo-image">
            </a>
        </x-slot>

        <x-auth-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}" class="auth-register-form">
            @csrf

            <!-- Name -->
            <div>
                <x-label for="Name" value="Tên tài khoản" />
                <x-input id="Name" class="block mt-1 w-full" type="text" name="Name" required />
            </div>

            <!-- Email -->
            <div class="mt-4">
                <x-label for="Email" value="Email" />
                <x-input id="Email" class="block mt-1 w-full" type="email" name="Email" required />
            </div>

            <!-- Phone -->
            <div class="mt-4">
                <x-label for="Phone" value="Số điện thoại" />
                <x-input id="Phone" class="block mt-1 w-full" type="text" name="Phone" />
            </div>

            <!-- BirthDate -->
            <div class="mt-4">
                <x-label for="BirthDate" value="Ngày sinh" />
                <x-input id="BirthDate" class="block mt-1 w-full auth-register-input auth-register-date" type="date" name="BirthDate" required />
            </div>

            <!-- Gender -->
            <div class="mt-4">
                <x-label for="Gender" value="Giới tính" />
                <select name="Gender" id="Gender" class="block mt-1 w-full auth-register-input auth-register-select">
                    <option value="">-- Chọn --</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                    <option value="Tùy chỉnh">Khác</option>
                </select>
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-label for="Password" value="Mật khẩu" />
                <div class="password-toggle-wrap mt-1">
                    <x-input id="Password" class="block w-full pr-11" type="password" name="Password" required />
                    <button type="button" class="password-toggle-btn" data-password-toggle data-target="Password" aria-label="Hiện mật khẩu" title="Hiện mật khẩu">👁</button>
                </div>
            </div>

            <!-- Confirm -->
            <div class="mt-4">
                <x-label for="Password_confirmation" value="Xác nhận mật khẩu" />
                <x-input id="Password_confirmation" class="block mt-1 w-full" type="password" name="Password_confirmation" required />
            </div>

            <div class="flex items-center justify-end mt-4">
                <a href="{{ route('login') }}" class="underline text-sm">Đã có tài khoản?</a>

                <x-button class="ml-4">
                    Đăng ký
                </x-button>
            </div>
        </form>

    </x-auth-card>
    </div>
</x-guest-layout>

<style>
    .auth-register-page .login-logo-image {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        object-fit: cover;
        box-shadow: 0 8px 16px rgba(124, 77, 255, 0.18);
        display: block;
    }

    .auth-register-page .auth-register-form > div {
        margin-top: 16px;
    }

    .auth-register-page .auth-register-form > div:first-child {
        margin-top: 0;
    }

    .auth-register-page input[type='text'],
    .auth-register-page input[type='email'],
    .auth-register-page input[type='password'],
    .auth-register-page input[type='date'],
    .auth-register-page select,
    .auth-register-page .auth-register-input {
        display: block;
        width: 100% !important; 
        height: 50px !important; /* Cố định chiều cao cho đều nhau */
        border-radius: 30px !important;
        border: none !important;
        padding: 0 20px !important;
        box-sizing: border-box; /* Giúp padding không làm phình ô nhập */
        max-width: 100%;
    }

    .auth-register-page .auth-register-date {
        display: block;
    }

    .auth-register-page .auth-register-select {
        display: block;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: linear-gradient(45deg, transparent 50%, #6b7280 50%), linear-gradient(135deg, #6b7280 50%, transparent 50%);
        background-position: calc(100% - 24px) calc(50% - 2px), calc(100% - 18px) calc(50% - 2px);
        background-size: 6px 6px, 6px 6px;
        background-repeat: no-repeat;
        padding-right: 44px;
    }

    .auth-register-page .auth-register-input:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgba(255, 174, 216, 0.26), 0 10px 24px rgba(16, 24, 40, 0.08);
    }


    .auth-register-page .password-toggle-wrap {
        position: relative;
    }

    .auth-register-page .password-toggle-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border: 0;
        background: transparent;
        color: #4b5563;
        width: 28px;
        height: 28px;
        border-radius: 999px;
        cursor: pointer;
        font-size: 15px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .auth-register-page .password-toggle-btn:hover {
        background: rgba(99, 102, 241, 0.12);
    }

    .auth-register-page .w-full.sm\:max-w-md {
        background: rgba(255, 255, 255, 0.45);
        backdrop-filter: blur(15px);
        border-radius: 40px;
        padding: 40px 30px;
        width: 100%;
        max-width: 450px; 
        box-sizing: border-box; /* Bắt buộc có dòng này để không tràn */
    }
</style>

<script>
    (function() {
        document.querySelectorAll('[data-password-toggle]').forEach(function(button) {
            button.addEventListener('click', function() {
                const targetId = button.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (!input) return;

                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                button.textContent = showing ? '👁' : '🙈';
                button.setAttribute('aria-label', showing ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
                button.setAttribute('title', showing ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
            });
        });
    })();
</script>