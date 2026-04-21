<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <img src="{{ asset('storage/bìa.png') }}" alt="Social Mini Logo" class="login-logo-image">
            </a>
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Vui lòng kiểm tra email của bạn để xác nhận địa chỉ email.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ __('Đã gửi liên kết xác nhận mới đến email của bạn.') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <div>
                    <x-button>
                        {{ __('Gửi lại email xác nhận') }}
                    </x-button>
                </div>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
                    {{ __('Đăng xuất') }}
                </button>
            </form>
        </div>
    </x-auth-card>
</x-guest-layout>
