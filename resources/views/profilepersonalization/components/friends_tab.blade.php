<div class="pp-bento-card p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Ban be</h5>
        <span class="small text-muted">{{ ($friendsData ?? collect())->count() }} ban</span>
    </div>

    <div class="row g-3">
        @forelse(($friendsData ?? collect()) as $friend)
            @php
                $friendAvatar = method_exists($friend, 'getAvatarUrlAttribute')
                    ? $friend->avatar_url
                    : ((\Illuminate\Support\Str::startsWith((string) ($friend->avatar ?? $friend->avatar_path ?? ''), ['http://', 'https://']))
                        ? (string) ($friend->avatar ?? $friend->avatar_path)
                        : asset('storage/' . ltrim((string) ($friend->avatar ?? $friend->avatar_path ?? ''), '/')));
                if (empty($friendAvatar)) {
                    $friendAvatar = 'https://i.pravatar.cc/120?u=' . urlencode((string) ($friend->id ?? 'friend'));
                }
            @endphp
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <img src="{{ $friendAvatar }}" class="rounded-circle mb-2" width="72" height="72" style="object-fit: cover;" alt="friend-{{ $friend->id }}">
                        <div class="fw-semibold text-truncate">{{ $friend->name ?? ('User #' . $friend->id) }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border mb-0">Chua co du lieu ban be tu bang friendships.</div>
            </div>
        @endforelse
    </div>
</div>
