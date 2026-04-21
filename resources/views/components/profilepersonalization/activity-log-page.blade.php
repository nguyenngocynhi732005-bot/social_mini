@props([
    'activityGroups' => collect(),
    'selectedYear' => now()->year,
    'selectedType' => 'all',
    'years' => collect(),
    'profileId' => null,
    'typeOptions' => [],
])

@php
    $activityTypeMeta = [
        'like' => ['icon' => 'fas fa-heart', 'class' => 'text-danger', 'bg' => '#ffeef0'],
        'comment' => ['icon' => 'fas fa-comment-dots', 'class' => 'text-primary', 'bg' => '#eef5ff'],
        'post' => ['icon' => 'fas fa-pen', 'class' => 'text-success', 'bg' => '#ebfff1'],
        'story' => ['icon' => 'fas fa-book-open', 'class' => 'text-warning', 'bg' => '#fff8e6'],
    ];
@endphp

<style>
    .activity-badge-like { background: #ffeef0; }
    .activity-badge-comment { background: #eef5ff; }
    .activity-badge-post { background: #ebfff1; }
    .activity-badge-story { background: #fff8e6; }
</style>

<div style="max-width: 860px; margin: 0 auto;">
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <h4 class="fw-bold mb-0">Nhật ký hoạt động</h4>

                <form method="GET" action="{{ route('profile.personalization.activity-log') }}" class="d-flex flex-wrap gap-2">
                    @if(!empty($profileId))
                        <input type="hidden" name="id" value="{{ (int) $profileId }}">
                    @endif

                    <select name="year" class="form-select form-select-sm" style="min-width: 120px;">
                        @foreach(($years ?? collect()) as $year)
                            <option value="{{ $year }}" @selected((int) $selectedYear === (int) $year)>{{ $year }}</option>
                        @endforeach
                    </select>

                    <select name="type" class="form-select form-select-sm" style="min-width: 140px;">
                        @foreach(($typeOptions ?? []) as $typeValue => $typeLabel)
                            <option value="{{ $typeValue }}" @selected($selectedType === $typeValue)>{{ $typeLabel }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-sm btn-primary">Lọc</button>
                </form>
            </div>

            @forelse(($activityGroups ?? collect()) as $groupTitle => $activities)
                <div class="mb-3">
                    <div class="small fw-bold text-uppercase text-muted mb-2">{{ $groupTitle }}</div>

                    <div class="list-group" style="border-radius: 12px; overflow: hidden;">
                        @foreach($activities as $activity)
                            @php
                                $meta = $activityTypeMeta[$activity['type']] ?? ['icon' => 'fas fa-circle', 'class' => 'text-secondary', 'bg' => '#f2f4f7'];
                            @endphp
                            <div class="list-group-item border-0 border-bottom">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center activity-badge activity-badge-{{ $activity['type'] }}" style="width: 34px; height: 34px;">
                                            <i class="{{ $meta['icon'] }} {{ $meta['class'] }}" style="font-size: 0.9rem;"></i>
                                        </div>
                                        <div>
                                            <div class="small text-dark">{{ $activity['description'] }}</div>
                                            <div class="small text-muted">{{ optional($activity['at'])->format('H:i:s d/m/Y') }}</div>
                                        </div>
                                    </div>

                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><button type="button" class="dropdown-item text-danger">Xóa bản ghi nhật ký này</button></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">Không có bản ghi hoạt động nào phù hợp với bộ lọc.</div>
            @endforelse
        </div>
    </div>
</div>
