<x-guest-layout>
    <x-auth-card>

        <x-slot name="logo">
            <a href="/">
                <img src="{{ asset('storage/bìa.png') }}" alt="Social Mini Logo" class="login-logo-image">
            </a>
        </x-slot>

        <x-auth-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
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
                <x-input id="BirthDate" class="block mt-1 w-full" type="date" name="BirthDate" required />
            </div>

            <!-- Gender -->
            <div class="mt-4">
                <x-label for="Gender" value="Giới tính" />
                <select name="Gender" class="block mt-1 w-full border-gray-300 rounded">
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
</x-guest-layout>

<style>
    .login-logo-image {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        object-fit: cover;
        box-shadow: 0 8px 16px rgba(124, 77, 255, 0.18);
        display: block;
    }

    .password-toggle-wrap {
        position: relative;
    }

    .password-toggle-btn {
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

    .password-toggle-btn:hover {
        background: rgba(99, 102, 241, 0.12);
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