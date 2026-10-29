@php
    $approvedCount = (int) ($collection->studies_approved_count ?? 0);
    $pendingCount = (int) ($collection->studies_pending_count ?? 0);
    $inProgressCount = (int) ($collection->studies_in_progress_count ?? 0);
    $rejectedCount = (int) ($collection->studies_rejected_count ?? 0);
    $counted = $approvedCount + $pendingCount + $inProgressCount + $rejectedCount;
    $totalCount = (int) ($collection->studies_count ?? 0);
    if ($totalCount < $counted) {
        $totalCount = $counted;
    }
    $barTotal = max(1, $totalCount);
    $approvedPct = ($approvedCount / $barTotal) * 100;
    $pendingPct = ($pendingCount / $barTotal) * 100;
    $rejectedPct = ($rejectedCount / $barTotal) * 100;
    $inProgressPct = ($inProgressCount / $barTotal) * 100;
@endphp
<div>
    <div class="progress" style="height: 20px; min-width: 150px;">
        @if($approvedCount > 0)
            <div class="progress-bar bg-success" role="progressbar"
                style="width: {{ $approvedPct }}%;"
                aria-valuenow="{{ $approvedCount }}" aria-valuemin="0" aria-valuemax="{{ $barTotal }}"
                title="{{ $approvedCount }} approved"></div>
        @endif
        @if($pendingCount > 0)
            <div class="progress-bar bg-warning text-dark" role="progressbar"
                style="width: {{ $pendingPct }}%;"
                aria-valuenow="{{ $pendingCount }}" aria-valuemin="0" aria-valuemax="{{ $barTotal }}"
                title="{{ $pendingCount }} pending"></div>
        @endif
        @if($rejectedCount > 0)
            <div class="progress-bar bg-danger" role="progressbar"
                style="width: {{ $rejectedPct }}%;"
                aria-valuenow="{{ $rejectedCount }}" aria-valuemin="0" aria-valuemax="{{ $barTotal }}"
                title="{{ $rejectedCount }} need revision"></div>
        @endif
        @if($inProgressCount > 0)
            <div class="progress-bar bg-secondary" role="progressbar"
                style="width: {{ $inProgressPct }}%;"
                aria-valuenow="{{ $inProgressCount }}" aria-valuemin="0" aria-valuemax="{{ $barTotal }}"
                title="{{ $inProgressCount }} in progress"></div>
        @endif
    </div>
    <small>
        {{ $totalCount }} studies:
        <span class="text-success">{{ $approvedCount }} approved</span>
        · <span class="text-warning">{{ $pendingCount }} pending</span>
        · {{ $inProgressCount }} in progress
        @if($rejectedCount > 0)
            · <span class="text-danger">{{ $rejectedCount }} need revision</span>
        @endif
    </small>
</div>
