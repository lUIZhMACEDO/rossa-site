<div>
    @php
        use App\Enums\CoreFacilityStatus;
    @endphp
    @if(!$facilities)
        <!-- no facilities -->
        @if($myFacilities)
            <div class="alert alert-info cf-empty-state">
                You have not registered any core facilities yet. <a class="alert-link" href="{{ route('core-facility.create') }}">Register a core facility</a>.
            </div>
        @else
            <div class="alert alert-info cf-empty-state">
                No core facilities found.
            </div>
        @endif
    @else
    <div class="d-flex justify-content-between align-items-end mb-2">
        <div>
            @if(Gate::check('manage-facilities') || $myFacilities)
                <label class="form-label fw-bold">Status</label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($selectableStatuses as $status)
                        <div class="form-check">
                            <input type="checkbox" 
                                class="form-check-input fs-6" 
                                wire:model.live="selectedStatuses" 
                                value="{{ $status->value }}" 
                                id="status_{{ $status->value }}">
                            <label class="form-check-label fs-6" for="status_{{ $status->value }}">
                                {{ $status->label() }}
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Search..." wire:model.live.debounce.300ms="search"/>
        </div>
    </div>

        @if($facilities->isEmpty())
            <div class="alert alert-info cf-empty-state">
                No core facilities found. Try adjusting the filters.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover table-nowrap cf-table">
                    <thead class="table-light">
                        <tr>
                            @foreach($columns as $column => $columnData)
                                @if($columnData['sortable'])
                                    <th class="sortable-header {{ $column === 'web_address' ? 'web-address-col' : '' }}">
                                        <a href="#" wire:click.prevent="sortBy('{{ $column }}')" class="text-dark text-decoration-none">
                                            {{ $columnData['name'] }}
                                        @if($sort === $column)
                                            <i class="bi bi-arrow-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up"></i>
                                        @endif
                                        </a>
                                    </th>
                                @else
                                    <th>{{ $columnData['name'] }}</th>
                                @endif
                            @endforeach

                            @if(Gate::check('manage-facilities') || $myFacilities)
                                <th class="text-center">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($facilities as $facility)
                            <tr>
                                @foreach($columns as $column => $columnData)
                                    <td class="{{ $column === 'web_address' ? 'web-address-col' : '' }}">
                                        @switch($column)
                                            @case('facility_name')
                                                <div class="fw-bold fs-6">
                                                    {{ $facility->facility_name }}
                                                </div>
                                                <div class="text-muted fs-6">
                                                    {{ $facility->institution }}
                                                </div>
                                                @break

                                            @case('web_address')
                                                @if($facility->web_address)
                                                    <a href="//{{ $facility->web_address }}" target="_blank" rel="noopener noreferrer">{{ $facility->web_address }}</a>
                                                @else
                                                    -
                                                @endif
                                                @break
                                                
                                            @case('contact_name')
                                                <div>
                                                    {{ $facility->contact_name }}
                                                    @if($facility->contact_email)
                                                        <span>[<a href="mailto:{{ $facility->contact_email }}">{{ $facility->contact_email }}</a>]</span>
                                                    @endif
                                                </div>
                                                @if($facility->secondary_contact_name || $facility->secondary_contact_email)
                                                    <div class="text-muted">
                                                        {{ $facility->secondary_contact_name ? $facility->secondary_contact_name : '-' }}
                                                        @if($facility->secondary_contact_email)
                                                            <span>[<a href="mailto:{{ $facility->secondary_contact_email }}">{{ $facility->secondary_contact_email }}</a>]</span>
                                                        @endif
                                                    </div>
                                                @endif
                                                @break

                                            @case('analysis_types')
                                                @if(!empty($facility->analysis_types))
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach($facility->analysis_types as $type)
                                                            <li class="small">• {{ $type['name'] ?? '' }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    -
                                                @endif
                                                @break

                                            @case('status')
                                                @php
                                                    $rejectionLabel = null;
                                                    if ($facility->rejection_reason) {
                                                        if ($facility->status === CoreFacilityStatus::REJECTED) {
                                                            $rejectionLabel = 'Rejection Reason';
                                                        } elseif ($facility->status === CoreFacilityStatus::PENDING) {
                                                            $rejectionLabel = 'Prior Rejection Reason';
                                                        }
                                                    }
                                                @endphp
                                                <span class="badge text-bg-{{ $facility->status->color() }}"
                                                    data-bs-toggle="tooltip"
                                                    data-tooltip-type="cf-status"
                                                    data-tooltip-status="{{ $facility->status->label() }}"
                                                    @if($facility->createdBy) data-tooltip-created-by="{{ $facility->createdBy->name }}" @endif
                                                    @if($facility->parent) data-tooltip-parent="{{ $facility->parent->facility_name }}" @endif
                                                    @if($rejectionLabel) data-tooltip-rejection-label="{{ $rejectionLabel }}" data-tooltip-rejection-reason="{{ $facility->rejection_reason }}" @endif
                                                    @if($facility->reviewed_at) data-tooltip-reviewed-at="{{ $facility->reviewed_at->format('H:i, m/d/y') }}" @endif
                                                    @if($facility->reviewedBy) data-tooltip-reviewed-by="{{ $facility->reviewedBy->name }}" @endif
                                                    style="cursor: default">
                                                    {{ $facility->status->label() }}
                                                    <i class="bi bi-info-circle ms-1"></i>
                                                </span>
                                                @break
                                            
                                            @case('web_status')
                                                @if(isset($facility->web_status))
                                                    <div class="text-center">
                                                        <span class="badge bg-{{ $facility->getWebStatusColor() }}"
                                                            data-bs-toggle="tooltip"
                                                            data-tooltip-type="cf-web-status"
                                                            @if($facility->web_status_desc) data-tooltip-web-desc="{{ $facility->web_status_desc }}" @endif
                                                            @if($facility->web_status_error) data-tooltip-web-error="{{ $facility->web_status_error }}" @endif
                                                            @if($facility->web_checked_at) data-tooltip-web-checked-at="{{ $facility->web_checked_at->format('H:i, m/d/y') }}" @endif
                                                            style="cursor: default">
                                                            {{ $facility->web_status }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="text-center">
                                                        -
                                                    </div>
                                                @endif
                                                @break
                                            
                                            @case('last_refreshed_at')
                                                @if($facility->last_refreshed_at)
                                                    {{ $facility->last_refreshed_at->format('H:i, m/d/y') }}
                                                @else
                                                    -
                                                @endif
                                                @break

                                            @case('expires_at')
                                                @if($facility->expires_at)
                                                    {{ $facility->expires_at->format('m/d/y') }}
                                                @else
                                                    <div class="text-center">-</div>
                                                @endif
                                                @break

                                            @case('user_id')
                                                @if($facility->createdBy)
                                                    <a href="mailto:{{ $facility->createdBy->email }}">{{ $facility->createdBy->email }}</a>
                                                @else
                                                    -
                                                @endif
                                                @break

                                            @default
                                                {{ $facility->{$column} ?? '-' }}
                                        @endswitch
                                    </td>
                                @endforeach

                                @if(!$myFacilities)
                                    @if(Gate::check('manage-facilities'))
                                        <td class="text-center">
                                            <div class="btn-group" role="group" aria-label="Approval actions">
                                                @if($facility->status === CoreFacilityStatus::PENDING)
                                                    <button wire:click="$dispatch('openModal', { component: 'modals.cf-status-update', arguments: { facility: {{ $facility->id }}, status: '{{ CoreFacilityStatus::ACCEPTED->value }}' }})" 
                                                        class="btn btn-sm btn-success me-1"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ ucfirst(CoreFacilityStatus::ACCEPTED->action()) }}">
                                                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                                                    </button>
                                                    <button wire:click="$dispatch('openModal', { component: 'modals.cf-status-update', arguments: { facility: {{ $facility->id }}, status: '{{ CoreFacilityStatus::REJECTED->value }}' }})" 
                                                        class="btn btn-sm btn-danger me-1"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ ucfirst(CoreFacilityStatus::REJECTED->action()) }}">
                                                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                                                    </button>
                                                @endif
                                                @if($facility->status === CoreFacilityStatus::ACCEPTED || $facility->status === CoreFacilityStatus::REJECTED)
                                                    <button wire:click="$dispatch('openModal', { component: 'modals.cf-status-update', arguments: { facility: {{ $facility->id }}, status: '{{ CoreFacilityStatus::PENDING->value }}' }})" 
                                                        class="btn btn-sm btn-warning me-1"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ ucfirst(CoreFacilityStatus::PENDING->action()) }}">
                                                        <i class="bi bi-clock" aria-hidden="true"></i>
                                                    </button>
                                                @endif
                                                <button wire:click="$dispatch('openModal', { component: 'modals.cf-update', arguments:  {facility: {{ $facility->id }}}})" 
                                                    class="btn btn-sm btn-secondary me-1"
                                                    data-bs-toggle="tooltip"
                                                    title="Edit">
                                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                                </button>
                                                <button wire:click="$dispatch('openModal', { component: 'modals.cf-delete', arguments:  {facility: {{ $facility->id }}}})" 
                                                    class="btn btn-sm btn-dark"
                                                    data-bs-toggle="tooltip"
                                                    title="Delete">
                                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </td>
                                    @endif
                                @else
                                    <td class="text-center">
                                        <div class="btn-group" role="group" aria-label="Edit">
                                            <button wire:click="$dispatch('openModal', { component: 'modals.cf-update', arguments:  {facility: {{ $facility->id }}}})" 
                                                class="btn btn-sm btn-secondary"
                                                data-bs-toggle="tooltip"
                                                title="Edit">
                                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $facilities->links() }}
            </div>
        @endif
    @endif
</div>