<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <img src="{{ asset('storage/bìa.png') }}" alt="Social Mini Logo" class="login-logo-image">
            </a>
        </x-slot>

        <div class="auth-login-hero">
            <h1>Xin chào</h1>
            <p>Chào mừng bạn quay lại, chúng tôi nhớ bạn!</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-label for="email" :value="__('Email')" />

                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-label for="password" :value="__('Mật khẩu')" />
                <div class="password-toggle-wrap mt-1">
                    <x-input id="password" class="block w-full pr-11"
                        type="password"
                        name="password"
                        required autocomplete="current-password" />
                    <button type="button" class="password-toggle-btn" data-password-toggle data-target="password" aria-label="Hiện mật khẩu" title="Hiện mật khẩu">👁</button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="remember">
                    <span class="ml-2 text-sm text-gray-600">{{ __('Ghi nhớ đăng nhập') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                    {{ __('Bạn quên mật khẩu?') }}
                </a>
                @endif

                <x-button class="ml-3">
                    {{ __('Đăng nhập') }}
                </x-button>
            </div>

            @if (Route::has('register'))
            <p class="mt-4 text-sm text-gray-600">
                {{ __('Bạn chưa có tài khoản?') }}
                <a class="underline hover:text-gray-900" href="{{ route('register') }}">{{ __('Đăng ký') }}</a>
            </p>
            @endif
        </form>
    </x-auth-card>
</x-guest-layout>

<style>
    .auth-login-hero {
        text-align: center;
        margin-bottom: 18px;
    }

    .auth-login-hero h1 {
        margin: 0;
        font-size: clamp(26px, 4vw, 34px);
        line-height: 1.05;
        font-weight: 900;
        color: #1f3a8a;
        letter-spacing: -0.03em;
    }

    .auth-login-hero p {
        margin: 8px auto 0;
        max-width: 220px;
        font-size: 15px;
        line-height: 1.35;
        color: rgba(57, 71, 122, 0.78);
        font-weight: 700;
    }

    .auth-screen-panel>form>div {
        margin-top: 16px;
    }

    .auth-screen-panel>form>div:first-child {
        margin-top: 0;
    }

    .auth-screen-panel .flex.items-center.justify-end.mt-4 {
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .auth-screen-panel .flex.items-center.justify-end.mt-4 .ml-3 {
        margin-left: 0;
    }

    .auth-screen-panel .block.mt-4+.flex.items-center.justify-end.mt-4 {
        margin-top: 22px;
    }

    .auth-screen-panel .mt-4.text-sm.text-gray-600 {
        margin-top: 18px;
        text-align: center;
    }

    .auth-screen-panel label[for='remember_me'] {
        gap: 10px;
    }

    .auth-screen-panel label[for='remember_me'] span {
        font-size: 14px;
        font-weight: 700;
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