{{ $studies->links() }}

@if ($studies->count() == 0)
    <div class="text-center py-5">
        @if(request('search') || (request('status') && request('status') !== 'all'))
            <p class="text-muted">No studies found matching your criteria.</p>
            <a href="{{ route('admin.studies') }}" class="btn btn-outline-primary">Clear filters</a>
        @else
            <p class="text-muted">No studies found.</p>
        @endif
    </div>
@endif

@foreach ($studies as $study)
<a href="{{ route('admin.study.edit', $study->id) }}" class="card mb-3 shadow-sm text-decoration-none
    @if($study->status === App\Enums\StudyStatus::PENDING) border-warning border-2 @endif
    @if($study->status === App\Enums\StudyStatus::APPROVED) border-success border-2 @endif" style="border-left: 4px solid var(--bs-primary);">
    <div class="card-body">
        <div class="d-flex flex-column">
            <!-- Header Row -->
            <div class="d-flex flex-row justify-content-between align-items-start mb-3">
                <div class="flex-grow-1">
                    <h5 class="card-title fw-bold mb-1 text-dark">
                        {{ $study->title ? $study->title : 'Untitled Study' }}
                        @if($study->status === App\Enums\StudyStatus::PENDING)
                            <span class="badge bg-warning text-dark ms-2">
                                <i class="bi bi-clock"></i> Pending Approval
                            </span>
                        @endif
                        @if($study->status === App\Enums\StudyStatus::APPROVED)
                            <span class="badge bg-success ms-2">
                                <i class="bi bi-check-circle"></i> Approved
                            </span>
                        @endif
                        @if($study->isAbandonedDraft(App\Services\StudyService::ABANDONED_DRAFT_DAYS))
                            <span class="badge bg-danger ms-2">
                                <i class="bi bi-trash"></i> Abandoned Draft
                            </span>
                        @endif
                        @if($study->isEmbargoed())
                            <span class="badge bg-secondary ms-2">
                                <i class="bi bi-hourglass-split"></i> Embargoed
                            </span>
                        @endif
                    </h5>
                    <div class="d-flex align-items-center mb-2">
                        <small class="text-secondary me-3">
                            <strong>ID:</strong> {{ $study->identifier }}
                        </small>
                        <small class="text-secondary me-3">
                            <strong>User:</strong> {{ $study->user->name }}
                        </small>
                        @if($study->animalModels->count() > 0)
                            <small class="text-secondary me-3">
                                <strong>Animal Models:</strong>
                                @foreach($study->animalModels as $model)
                                    <span class="badge bg-light text-dark me-1">{{ $model->animal_model_name }} ({{ ucfirst($model->species ?? 'Not specified') }})</span>
                                @endforeach
                            </small>
                        @endif
                        @if($study->is_published)
                            <small class="text-success">
                                <i class="bi bi-book"></i> Published
                            </small>
                        @endif
                    </div>
                </div>
                <span class="badge text-bg-{{ $study->status->color() }} p-2">{{ $study->status->label() }}</span>
            </div>

            <!-- Study Details -->
            <div class="row mb-3">
                <div class="col-md-8">
                    @if($study->summary)
                        <p class="text-muted mb-2 small">
                            <strong>Summary:</strong> {{ Str::limit($study->summary, 200) }}
                        </p>
                    @endif
                    
                    <!-- Experimental Methods -->
                    <div class="d-flex flex-wrap gap-2 mb-2">

                        @if($study->drugAdministrationPlans->count() > 0)
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-capsule-pill"></i> {{ $study->drugAdministrationPlans->count() }} Drug Administration Plans(s)
                            </span>
                        @endif
                        @if($study->diseaseLines->count() > 0)
                            <span class="badge bg-danger text-white">
                                <i class="bi bi-virus"></i> {{ $study->diseaseLines->count() }} Disease Line(s)
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Key Information -->
                    <div class="text-end">
                        @if($study->user)
                            <p class="text-secondary mb-1 small">
                                <strong>Created by:</strong> {{ $study->user->name }}
                            </p>
                        @endif
                        @if($study->investigators->count() > 0)
                            <p class="text-secondary mb-1 small">
                                <strong>Investigators:</strong> 
                                @foreach($study->investigators->take(2) as $investigator)
                                    {{ $investigator->first_name }} {{ $investigator->last_name }}@if(!$loop->last), @endif
                                @endforeach
                                @if($study->investigators->count() > 2)
                                    <span class="text-muted">+{{ $study->investigators->count() - 2 }} more</span>
                                @endif
                            </p>
                        @endif
                        @if($study->completion_date)
                            <p class="text-secondary mb-1 small">
                                <strong>Completion:</strong> {{ $study->completion_date->format('M j, Y') }}
                            </p>
                        @endif
                        <p class="text-secondary mb-0 small">
                            <strong>Created:</strong> {{ $study->created_at->diffForHumans() }}
                        </p>
                        <p class="text-secondary mb-0 small">
                            <strong>Last Activity:</strong> {{ $study->updated_at->diffForHumans() }}
                        </p>
                        @if($study->submitted_at)
                            <p class="text-secondary mb-0 small">
                                <strong>Submitted:</strong> {{ $study->submitted_at->diffForHumans() }}
                            </p>
                        @endif
                        @if($study->isAbandonedDraft(App\Services\StudyService::ABANDONED_DRAFT_DAYS))
                            <p class="text-danger mb-0 small">
                                <strong>Inactive For:</strong> {{ $study->updated_at->diffForHumans(null, true) }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sample Information -->
        </div>
    </div>
</a>
@endforeach

{{ $studies->links() }}
