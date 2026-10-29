<div>
    @php
        use App\Enums\ApiTokenRequestStatus;
        use App\Enums\InstitutionType;
    @endphp

    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <label class="form-label fw-bold mb-1">Status</label>
            <div class="d-flex flex-wrap gap-3">
                @foreach($statuses as $status)
                    <div class="form-check">
                        <input type="checkbox"
                            class="form-check-input fs-6"
                            wire:model.live="selectedStatuses"
                            value="{{ $status->value }}"
                            id="api_token_status_{{ $status->value }}">
                        <label class="form-check-label fs-6" for="api_token_status_{{ $status->value }}">
                            {{ $status->label() }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="col-md-4">
            <input type="text"
                class="form-control"
                placeholder="Search token requests..."
                wire:model.live.debounce.300ms="search" />
        </div>
    </div>

    @if($selectedStatuses === [])
        <div class="alert alert-info mb-0">
            Select at least one status to view requests.
        </div>
    @elseif($tokenRequests->isEmpty())
        <div class="alert alert-info mb-0">
            No API token requests found. Try adjusting the filters or search.
        </div>
    @else
        <p class="text-muted small mb-2">
            Showing <strong>{{ $tokenRequests->total() }}</strong> result(s)
            @if($search !== '')
                for &ldquo;{{ $search }}&rdquo;
            @endif
        </p>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Department</th>
                        <th>Location</th>
                        <th>Joined</th>
                        <th class="api-token-request-col">Request</th>
                        <th class="sortable-header">
                            <a href="#" wire:click.prevent="sortBy('created_at')" class="text-dark text-decoration-none">
                                Submitted
                                @if($sort === 'created_at')
                                    <i class="bi bi-arrow-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="sortable-header">
                            <a href="#" wire:click.prevent="sortBy('status')" class="text-dark text-decoration-none">
                                Status
                                @if($sort === 'status')
                                    <i class="bi bi-arrow-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tokenRequests as $submission)
                        <tr wire:key="api-token-request-{{ $submission->id }}">
                            <td>
                                <div class="fw-semibold">{{ $submission->user->name }}</div>
                                <a href="mailto:{{ $submission->user->email }}" class="text-muted small">
                                    {{ $submission->user->email }}
                                </a>
                            </td>
                            <td>
                                @if($submission->user->department || $submission->user->organization)
                                    @if($submission->user->department)
                                        {{ $submission->user->department }}
                                    @endif
                                    @if($submission->user->organization)
                                        <span class="d-block text-muted">{{ $submission->user->organization }}</span>
                                    @endif
                                    @if($submission->user->institution_type)
                                        <span class="badge {{ $submission->user->institution_type->badgeClass() }} mt-1">
                                            {{ $submission->user->institution_type->label() }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($submission->user->country)
                                    <small class="text-muted">
                                        {{ $submission->user->state ? $submission->user->state . ', ' : '' }}{{ $submission->user->country->name }}
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $submission->user->created_at->format('M d, Y') }}
                                </small>
                            </td>
                            <td class="api-token-request-col">
                                <div class="api-token-request-message">
                                    {{ $submission->message }}
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $submission->created_at->format('M d, Y') }}<br>
                                    {{ $submission->created_at->format('g:i A') }}
                                </small>
                            </td>
                            <td>
                                <span class="badge {{ $submission->status->badgeClass() }}">
                                    {{ $submission->status->label() }}
                                </span>
                                @if ($submission->category)
                                    <br>
                                    <span class="badge {{ $submission->category->badgeClass() }}">{{ $submission->category->label() }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group" aria-label="API token request actions">
                                    <a href="{{ route('admin.users', ['search' => $submission->user->id]) }}"
                                        class="btn btn-sm btn-secondary me-1"
                                        data-bs-toggle="tooltip"
                                        title="View user">
                                        <i class="bi bi-person" aria-hidden="true"></i>
                                    </a>
                                    @if ($submission->status === ApiTokenRequestStatus::Pending)
                                        <button type="button"
                                            class="btn btn-sm btn-success me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#acceptModal-{{ $submission->id }}"
                                            data-bs-tooltip
                                            title="Accept">
                                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.api-token-requests.reject', $submission) }}" class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                data-bs-toggle="tooltip"
                                                title="Reject"
                                                onclick="return confirm('Reject this API token request?')">
                                                <i class="bi bi-x-lg" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @elseif ($submission->status === ApiTokenRequestStatus::Accepted)
                                        <form method="POST" action="{{ route('admin.api-token-requests.disable', $submission) }}" class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-sm btn-dark"
                                                data-bs-toggle="tooltip"
                                                title="Disable"
                                                onclick="return confirm('Disable this API token?')">
                                                <i class="bi bi-slash-circle" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @foreach($tokenRequests as $submission)
            @if($submission->status !== ApiTokenRequestStatus::Pending)
                @continue
            @endif
            @php
                $defaultTier = $submission->user->institution_type?->value ?? InstitutionType::CommercialForProfit->value;
                $showEduWarning = $submission->user->institution_type === InstitutionType::AcademicNonprofit
                    && ! $submission->user->hasEduEmail();
            @endphp
            <div class="modal fade" id="acceptModal-{{ $submission->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('admin.api-token-requests.accept', $submission) }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Accept API token request</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted small mb-2">Issue a token for:</p>
                                <div class="card card-body mb-3 py-2" style="border-left: 4px solid var(--bs-primary);">
                                    <div><strong>{{ $submission->user->name }}</strong> ({{ $submission->user->email }})</div>
                                    @if($submission->user->department || $submission->user->organization)
                                        <div class="text-muted small">
                                            @if($submission->user->department)
                                                {{ $submission->user->department }}
                                            @endif
                                            @if($submission->user->organization)
                                                <span> &middot; {{ $submission->user->organization }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if($submission->user->institution_type)
                                        <span class="badge {{ $submission->user->institution_type->badgeClass() }} mt-1">
                                            {{ $submission->user->institution_type->label() }}
                                        </span>
                                    @endif
                                </div>

                                @if($showEduWarning)
                                    <div class="alert alert-warning d-flex align-items-start gap-2 small mb-3" role="alert">
                                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0" aria-hidden="true"></i>
                                        <div class="text-break">
                                            This user selected an academic or non-profit institution but their email does not end in <strong>.edu</strong>. Confirm the access tier before issuing a token.
                                        </div>
                                    </div>
                                @endif

                                <label class="form-label" for="category-{{ $submission->id }}">Access tier</label>
                                <select name="category" id="category-{{ $submission->id }}" class="form-select" required>
                                    @foreach (InstitutionType::cases() as $type)
                                        <option value="{{ $type->value }}" @selected($defaultTier === $type->value)>
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($submission->user->institution_type)
                                    <div class="form-text">
                                        Pre-selected from institution type ({{ $submission->user->institution_type->label() }}).
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Accept and issue token</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="mt-4">
            {{ $tokenRequests->links() }}
        </div>
    @endif
</div>
