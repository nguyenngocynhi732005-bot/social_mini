<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <img src="{{ asset('storage/bìa.png') }}" alt="Social Mini Logo" class="login-logo-image">
            </a>
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Quên mật khẩu? Không sao cả. Chỉ cần cho chúng tôi biết địa chỉ email của bạn, chúng tôi sẽ gửi cho bạn một liên kết đặt lại mật khẩu qua email để bạn có thể chọn mật khẩu mới.') }}
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-label for="email" :value="__('Email')" />

                <!-- FIX NHẸ: thêm old + trim -->
                <x-input id="email"
                         class="block mt-1 w-full"
                         type="email"
                         name="email"
                         :value="old('email')"
                         required
                         autofocus />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('Gửi liên kết đặt lại mật khẩu qua email') }}
                </x-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>