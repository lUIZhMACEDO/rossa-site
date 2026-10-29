@section('title', 'Submission')
@section('guide-content')
    @include('guides.data-ingestion._07_submission')
@endsection
<x-layouts.submission :$study>
<div class="py-4">
    <div class="mb-4">
        @if (session()->has('validationErrors'))
            <div class="alert alert-danger">
                <h4 class="alert-heading mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Study Cannot Be Submitted
                </h4>
                <p class="mb-3">Please address the following issues before submitting:</p>
                <ul class="mb-3">
                    @foreach(session('validationErrors') as $error)
                        <li>
                            @if(isset($error['route']) && $error['route'])
                                <a href="{{ $error['route'] }}" class="alert-link fw-semibold">
                                    {{ $error['message'] }}
                                </a>
                            @else
                                {{ $error['message'] }}
                            @endif
                        </li>
                    @endforeach
                </ul>
                @if(collect(session('validationErrors'))->where('route', '!=', null)->isNotEmpty())
                    <p class="mb-0 small">Click on any linked item above to go directly to that section.</p>
                @endif
            </div>
        @endif

        @if ($study->status === App\Enums\StudyStatus::IN_PROGRESS || $study->status === App\Enums\StudyStatus::REJECTED)
            @if ($study->status === App\Enums\StudyStatus::REJECTED)
                <div class="alert alert-warning" role="alert">
                    <h2 class="h4 mb-3">Revisions Needed</h2>
                    <p>
                        Thank you for your submission of <strong>{{ $study->title }}</strong> ({{ $study->identifier }}). The curation team has reviewed your study and identified some areas that need revision before it can be approved.
                    </p>
                    <p>
                        Please review the feedback provided below and make the necessary updates to your study. Once you've made these revisions, you can resubmit for another round of review.
                    </p>
                    <p>
                        If you have any questions about the feedback or need assistance with revisions, please don't hesitate to contact the repository team for support.
                    </p>

                    <div class="mt-4">
                        <h2 class="h5 mb-2">Revision Feedback</h2>
                        @if ($study->rejection_reason)
                            <p>{{ $study->rejection_reason }}</p>
                        @else
                            <p>No specific feedback was provided by the reviewer.</p>
                        @endif
                    </div>
                </div>
            @endif
            <div class="card" style="border-left: 4px solid var(--bs-primary);">
                <div class="card-body">
                    <h1 class="h3 mb-3">Ready to Submit?</h1>

                    <p>
                        If you haven't already, please take a moment to thoroughly review the ROSSA Study Report. This report is what will be publicly shared with the broader research community, so its accuracy and authenticity is essential.  If everything looks correct, please confirm below and submit your study for curation review.
                    </p>

                    @php
                        $incompleteRequirements = $study->getIncompleteRequirements();
                        $hasIncompleteRequirements = !empty($incompleteRequirements);
                    @endphp

                    @if ($hasIncompleteRequirements)
                        <div class="bg-light p-3 rounded mb-4" style="border-left: 4px solid var(--bs-warning);">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-3" style="font-size: 1.5rem;"></i>
                                <div class="flex-grow-1">
                                    <h2 class="h5 mb-2 fw-semibold">Outstanding Requirements</h2>
                                    <p class="mb-2 text-muted">Please complete the following sections before submitting your study:</p>
                                    <ul class="mb-0">
                                        @foreach($incompleteRequirements as $error)
                                            <li class="mb-1">
                                                <a href="{{ $error['route'] }}" class="text-decoration-none fw-semibold">
                                                    {{ $error['message'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                    <p class="mb-4">
                        If you need to make corrections, return to the form using the button below.
                    </p>
                    @php
                        $canSubmit = auth()->user()->canSubmitStudy($study);
                        $correspondingInvestigators = $study->investigators->where('corresponding', true);
                        $canRequestApproval = $study->collection_id === null
                            && ! $canSubmit
                            && auth()->user()->canEditStudy($study);
                    @endphp

                    @unless ($canSubmit)
                        <div class="alert alert-info">
                            @if ($study->collection_id !== null)
                                <strong>Only the study owner can submit this collection study.</strong>
                            @else
                                <strong>Only a corresponding investigator can submit this study.</strong>
                                @if($correspondingInvestigators->isNotEmpty())
                                    Corresponding investigator:
                                    {{ $correspondingInvestigators->map(fn ($i) => trim($i->first_name . ' ' . $i->last_name))->join(', ') }}.
                                    @if ($canRequestApproval)
                                        When your part is finished, use Send for approval to ask them to review the study and submit it.
                                    @endif
                                @else
                                    This study does not have a corresponding investigator yet. Add one on the Investigators page.
                                @endif
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('study.summary', $study) }}" class="btn btn-secondary">Return to Form</a>
                            @if ($canRequestApproval && $correspondingInvestigators->isNotEmpty())
                                <form method="POST" action="{{ route('study.submission.request-approval', $study) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary" @disabled($hasIncompleteRequirements)>
                                        Send for approval
                                    </button>
                                </form>
                            @endif
                        </div>
                    @else
                    <form method="POST" action="{{ route('study.submission.submit', $study) }}">
                        @csrf
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="attest_accuracy" class="form-check-input border-primary" id="attest_accuracy" required>
                                <label class="form-check-label" for="attest_accuracy">
                                    I confirm that the information provided in this submission is accurate and complete to the best of my knowledge.
                                </label>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <a href="{{ route('study.summary', $study) }}" class="btn btn-secondary">
                                Return to Form
                            </a>
                            @if($hasIncompleteRequirements)
                                <div class="d-inline-block" 
                                        data-bs-toggle="tooltip" 
                                        title="Please complete all requirements before submitting"
                                        style="cursor: not-allowed!important;">
                                    <button 
                                        type="button" 
                                        class="btn btn-primary" 
                                        style="pointer-events: none; opacity: 0.65;"
                                        disabled>
                                        Submit Study
                                    </button>
                                </div>
                            @else
                                <button type="submit" class="btn btn-primary">
                                    Submit Study
                                </button>
                            @endif
                        </div>
                    </form>
                    @endunless
                </div>
            </div>
        @endif

        @if ($study->status === App\Enums\StudyStatus::PENDING)
            <div class="card" style="border-left: 4px solid var(--bs-primary);">
                <div class="card-body">
                    <h1 class="h3 mb-3">Thank You for Your Contribution!</h1>
                    <p>
                        On behalf of the skeletal research community, we sincerely thank you for your contribution. By making your work openly available, you are advancing skeletal biology and making a lasting impact.
                    </p>

                    <div class="mt-4">
                        <h2 class="h5 mb-2">Submission Details</h2>
                        <p class="mb-1"><strong>Study ID:</strong> {{ $study->identifier }}</p>
                        <p class="mb-1"><strong>Study:</strong> {{ $study->title }}</p>
                        @php
                            $submitter = $study->submittedBy ?? $study->user;
                            $submitterInvestigator = $study->investigators->first(fn ($i) => strtolower((string) $i->email) === strtolower((string) $submitter?->email));
                        @endphp
                        <p class="mb-1"><strong>Submitted by:</strong> {{ $submitter?->display_name ?: 'N/A' }}, {{ $submitterInvestigator?->organization ?? $study->investigators->first()?->organization ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Date:</strong> {{ optional($study->submitted_at)->format('F j, Y') ?? now()->format('F j, Y') }}</p>
                    </div>

                    <p class="mt-4 mb-4">
                        Please save your Study ID for your records. You may need it if you contact us with questions about your submission.
                    </p>

                    <h2 class="h5 mb-2">What Happens Next</h2>
                    <p>
                        Your study will be reviewed by the ROSSA curation team to ensure your submission is complete and accurate. This process typically takes 2 weeks. You may be contacted by a curator if clarification or additional information is needed. Once approved, your study will be publicly available in the ROSSA archive and fully citable.
                    </p>
                    <p class="mb-0">
                        If your study is currently under embargo, it will be securely archived but not publicly shared until you update your publication status. You will be notified when your study goes live.
                    </p>
                </div>
            </div>
        @endif

        @if ($study->status === App\Enums\StudyStatus::APPROVED)
            <div class="card" style="border-left: 4px solid var(--bs-primary);">
                <div class="card-body">
                    <h2 class="h4 mb-3">Study Approved</h2>
                    <p>
                        Congratulations! Your study <strong>{{ $study->title }}</strong> ({{ $study->identifier }}) has been approved.
                    </p>

                    @if ( $study->isEmbargoed() )
                    <p>Your study has been partially published in the repository - showing only the 'Study Information' until the embargo is lifted and publication information is added.</p>
                    @else
                    <p>
                        Your study is fully published in the repository. You can view your published study and share it with others. If you need to make any updates or corrections, please contact the repository team for assistance.
                    </p>
                    @endif

                    <form action="{{ route('search.show', $study) }}" method="GET">
                        <button type="submit" class="btn btn-primary">View Published Study</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
</x-layouts.submission>