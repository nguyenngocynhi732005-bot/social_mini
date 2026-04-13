@extends('layouts.app')

@section('content')
<div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 400px;">
    <!-- Icon bạn bè lớn màu Pastel -->
    <i class="fas fa-users fa-5x mb-3" style="color: #ffecd2; opacity: 0.8;"></i>
    <h3 style="font-family: 'Quicksand'; color: #1f1c1c; font-weight: 700;">Danh sách bạn bè</h3>
    <p class="text-muted">Kết nối với mọi người trong nhóm Social Mini.</p>

    <!-- Thêm một vài ô bạn bè giả cho đẹp mắt -->
    <div class="d-flex gap-3 mt-3">
        @for($i=1; $i<=3; $i++)
            <img src="https://i.pravatar.cc/60?u={{$i}}" class="rounded-circle border border-white border-3 shadow-sm">
        @endfor
    </div>
</div>
@endsection