@extends('study.summary.layout')

@section('tab-content')
@if (Auth::user()->can('manage-studies'))
    <div class="d-flex flex-row align-items-center gap-2 mb-2">
        <h5 class="mb-0">Status</h5>
        <span class="badge text-bg-{{ $study->status->color() }}">
            {{ $study->status->label() }}
        </span>
    </div>


    @if($study->reviewed_at)
        <div class="mb-3 small text-muted">
            <div class="mb-1"><strong>Reviewed At:</strong> {{ $study->reviewed_at->format('M j, Y g:i A') }}</div>
            @if($study->reviewer)
                <div class="mb-1"><strong>Reviewed By:</strong> {{ $study->reviewer->name }}</div>
            @endif
            @if($study->rejection_reason)
                <div><strong>Revision Request:</strong> {{ $study->rejection_reason }}</div>
            @endif
        </div>
    @endif

    @if($study->status === App\Enums\StudyStatus::PENDING)
        <div class="d-flex flex-column gap-3 pt-3 border-top">
            <form id="approveForm" action="{{ route('admin.study.approve', ['id' => $study->id]) }}" method="POST">
                @csrf
                <div class="d-flex flex-column">
                    <p class="small text-muted mb-3">{{ $study->status->description() }}</p>
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to approve this study?');">
                        <i class="bi bi-check-circle me-2"></i>Approve Study
                    </button>
                </div>
            </form>

            <form id="rejectForm" action="{{ route('admin.study.reject', ['id' => $study->id]) }}" method="POST">
                @csrf
                <div class="d-flex flex-column">
                    <label for="rejection_reason" class="form-label">Revision comments (optional)</label>
                    <textarea id="rejection_reason" name="rejection_reason" class="form-control mb-2" rows="2" placeholder="Add optional reviewer feedback for the submitter."></textarea>
                    <button type="submit" class="btn btn-outline-danger w-100 mb-2" onclick="return confirm('Are you sure? This will notify the user by email to revise their submission.');">
                        <i class="bi bi-x-circle me-2"></i>Request Revision
                    </button>
                    <small class="form-text text-muted">When you request revision, the submitter receives an email with your comments.</small>
                </div>
            </form>
        </div>
    @endif

    @if($study->isAbandonedDraft(App\Services\StudyService::ABANDONED_DRAFT_DAYS))
        <div class="d-flex flex-column gap-2 pt-3 border-top mt-3">
            <p class="small text-muted mb-0">
                This draft has been inactive for {{ $study->updated_at->diffForHumans(null, true) }} and is eligible for abandoned-study cleanup.
            </p>
            <form action="{{ route('admin.study.destroy-abandoned', ['id' => $study->id]) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Delete this abandoned study draft? This will soft-delete it and hide it from active study lists.');">
                    <i class="bi bi-trash me-2"></i>Delete Abandoned Study
                </button>
            </form>
        </div>
    @endif
@endif
@endsection
