@extends('layouts.app')

@section('title', 'Tìm kiếm người dùng')

@section('content')
@php
    $displayName = function ($user) {
        $fullName = trim(implode(' ', array_filter([
            $user->first_name ?? null,
            $user->First_name ?? null,
            $user->last_name ?? null,
            $user->Last_name ?? null,
        ])));

        return $user->name
            ?? $user->Name
            ?? ($fullName !== '' ? $fullName : ('User #' . $user->id));
    };
@endphp

<div class="card border-0 shadow-sm" style="border-radius: 18px;">
    <div class="card-body p-4">
        <div class="mb-4">
            <h3 class="fw-bold mb-1" style="color: #1f1c1c;">Tìm kiếm người dùng</h3>
            <p class="text-muted mb-0">Kết quả cho từ khóa: <strong>{{ $keyword }}</strong></p>
        </div>

        <form action="{{ route('social.search.index') }}" method="GET" class="mb-4">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="search" name="q" value="{{ $keyword }}" class="form-control border-start-0" placeholder="Nhập tên người dùng...">
                <button class="btn btn-dark px-4" type="submit">Tìm</button>
            </div>
        </form>

        @if($keyword === '')
            <div class="text-center py-5 text-muted">
                Nhập tên người dùng để bắt đầu tìm kiếm.
            </div>
        @elseif($users->isEmpty())
            <div class="alert alert-light border mb-0">
                Không tìm thấy người dùng nào khớp với "{{ $keyword }}".
            </div>
        @else
            <div class="row g-3">
                @foreach($users as $user)
                    <div class="col-12 col-md-6">
                        <a href="{{ route('friends', ['target_id' => $user->id]) }}" class="text-decoration-none">
                            <div class="border rounded-4 p-3 d-flex align-items-center justify-content-between shadow-sm bg-white h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="https://i.pravatar.cc/72?u={{ $user->id }}" alt="{{ $displayName($user) }}" class="rounded-circle" width="56" height="56" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $displayName($user) }}</div>
                                        <div class="text-muted small">Bấm để xem và kết bạn</div>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection