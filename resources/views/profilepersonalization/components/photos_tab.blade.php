<div class="pp-bento-card p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Ảnh</h5>
        <span class="small text-muted">{{ ($photosData ?? collect())->count() }} ảnh</span>
    </div>

    <div class="row g-2">
        @forelse(($photosData ?? collect()) as $photoPost)
            @php
                $photoPath = $photoPost->image_url ?? $photoPost->media_path ?? null;
                $photoUrl = $photoPath
                    ? (\Illuminate\Support\Str::startsWith((string) $photoPath, ['http://', 'https://'])
                        ? $photoPath
                        : asset('storage/' . ltrim((string) $photoPath, '/')))
                    : null;
            @endphp
            @if($photoUrl)
                <div class="col-4">
                    <img src="{{ $photoUrl }}" class="pp-photo-thumb" alt="photo-{{ $photoPost->id }}">
                </div>
            @endif
        @empty
            <div class="col-12">
                <div class="alert alert-light border mb-0">Chưa có ảnh nào (post_type = image).</div>
            </div>
        @endforelse
    </div>
</div>
