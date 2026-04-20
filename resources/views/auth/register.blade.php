<x-guest-layout>
    <x-auth-card>

        <x-slot name="logo">
            <a href="/">
                <img src="{{ asset('storage/bìa.png') }}" alt="Social Mini Logo" class="login-logo-image">
            </a>
        </x-slot>

        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div>
                <x-label for="Name" value="Name" />
                <x-input id="Name" class="block mt-1 w-full" type="text" name="Name" required />
            </div>

            <!-- Email -->
            <div class="mt-4">
                <x-label for="Email" value="Email" />
                <x-input id="Email" class="block mt-1 w-full" type="email" name="Email" required />
            </div>

            <!-- Phone -->
            <div class="mt-4">
                <x-label for="Phone" value="Phone" />
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
                <x-label for="Password" value="Password" />
                <x-input id="Password" class="block mt-1 w-full" type="password" name="Password" required />
            </div>

            <!-- Confirm -->
            <div class="mt-4">
                <x-label for="Password_confirmation" value="Confirm Password" />
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
        width: 84px;
        height: 84px;
        border-radius: 22px;
        object-fit: cover;
        box-shadow: 0 10px 20px rgba(124, 77, 255, 0.24);
        display: block;
    }
</style>