@props(['errors' => null])

@php
    $errors = $errors ?: session('errors', new \Illuminate\Support\ViewErrorBag());
@endphp

@if ($errors->any())
    <div {{ $attributes }}>
        <div class="font-medium text-red-600">
            {{ __('Có lỗi xảy ra!') }}
        </div>

        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
