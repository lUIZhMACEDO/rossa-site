@section('title', 'Study Report')
<x-layouts.submission :$study>
    <div class="py-4">
        <div class="mb-4">
            <nav aria-label="breadcrumb" style="--bs-breadcrumb-divider: '>';">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item active">Report</li>
                </ol>
            </nav>
            <h1 class="mb-4">Study Report</h1>
        </div>
        <div>
            @if($study->isOwner(Auth::user()) && $study->status === App\Enums\StudyStatus::IN_PROGRESS)
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-info-circle"></i> Your study is currently in progress. When you are ready, you can submit the study for approval.
                    </div>
                    <div>
                    <form method="POST" action="{{ route('study.summary.submit', $study) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary">Submit for Approval</button>
                    </form>
                    </div>
                </div>
            @endif

        @include('study.summary.study-view')
    </div>
</x-layouts.submission>