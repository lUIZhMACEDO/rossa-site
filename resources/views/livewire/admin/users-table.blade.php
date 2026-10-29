<div>
    @php
        use App\Enums\PermissionType;
    @endphp

    <div class="d-flex justify-content-between align-items-end mb-3 flex-wrap gap-3">
        <div class="col-md-3">
            <label class="form-label fw-bold mb-1">Role</label>
            <select class="form-select" wire:model.live="selectedRole">
                <option value="all">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold mb-1">Status</label>
            <select class="form-select" wire:model.live="selectedStatus">
                <option value="active">Active</option>
                <option value="disabled">Disabled</option>
                <option value="all">All</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold mb-1">Search</label>
            <input type="text"
                class="form-control"
                placeholder="Search users by name, email, id..."
                wire:model.live.debounce.300ms="search" />
        </div>
    </div>

    @if($users->isEmpty())
        <div class="alert alert-info mb-0">
            @if($search !== '' || $selectedRole !== 'all')
                No users found matching your criteria. Try adjusting the filters or search.
            @else
                No users found.
            @endif
        </div>
    @else
        <p class="text-muted small mb-2">
            Showing <strong>{{ $users->total() }}</strong> result(s)
            @if($search !== '')
                for &ldquo;{{ $search }}&rdquo;
            @endif
        </p>

        <div class="table-responsive admin-users-table-scroll"
            x-data
            x-on:scroll.passive="$el.classList.toggle('is-scrolled', $el.scrollLeft > 0)">
            <table class="table table-striped table-hover table-nowrap table-vertical-align-middle admin-users-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="sticky-col sticky-col-actions"><span class="visually-hidden">Actions</span></th>
                        <th class="sticky-col sticky-col-id">ID</th>
                        <th class="sortable-header sticky-col sticky-col-name">
                            <a href="#" wire:click.prevent="sortBy('name')" class="text-dark text-decoration-none">
                                Name
                                @if($sort === 'name')
                                    <i class="bi bi-arrow-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th class="sortable-header">
                            <a href="#" wire:click.prevent="sortBy('email')" class="text-dark text-decoration-none">
                                Email
                                @if($sort === 'email')
                                    <i class="bi bi-arrow-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>Department</th>
                        <th>Location</th>
                        <th class="sortable-header">
                            <a href="#" wire:click.prevent="sortBy('created_at')" class="text-dark text-decoration-none">
                                Joined
                                @if($sort === 'created_at')
                                    <i class="bi bi-arrow-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>Verified</th>
                        <th>Abilities</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="{{ $user->isDisabled() ? 'table-secondary' : '' }}">
                            <td class="sticky-col sticky-col-actions">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-popper-config='{"strategy":"fixed"}'
                                        aria-expanded="false"
                                        aria-label="Actions for {{ $user->display_name }}">
                                        <i class="bi bi-three-dots" aria-hidden="true"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.user.roles', ['id' => $user->id]) }}">
                                                <i class="bi bi-shield me-2" aria-hidden="true"></i> Edit role
                                            </a>
                                        </li>
                                        @if($user->isAdmin())
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.user.subscriptions', ['id' => $user->id]) }}">
                                                    <i class="bi bi-bell me-2" aria-hidden="true"></i> Edit subscriptions
                                                </a>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button type="button"
                                                class="dropdown-item {{ $user->isDisabled() ? '' : 'text-danger' }}"
                                                wire:click="toggleDisabled({{ $user->id }})">
                                                <i class="bi bi-{{ $user->isDisabled() ? 'person-check' : 'person-slash' }} me-2" aria-hidden="true"></i>
                                                {{ $user->isDisabled() ? 'Enable user' : 'Disable user' }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                            <td class="sticky-col sticky-col-id text-muted">{{ $user->id }}</td>
                            <td class="sticky-col sticky-col-name">
                                <div class="fw-semibold">
                                    {{ $user->display_name }}
                                    @if($user->isDisabled())
                                        <span class="badge bg-secondary ms-1">Disabled</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                            </td>
                            <td>
                                @if($user->department || $user->organization)
                                    @if($user->department)
                                        {{ $user->department }}
                                    @endif
                                    @if($user->organization)
                                        <span class="d-block text-muted">{{ $user->organization }}</span>
                                    @endif
                                    @if($user->institution_type)
                                        <span class="badge {{ $user->institution_type->badgeClass() }} mt-1">
                                            {{ $user->institution_type->label() }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($user->country)
                                    <small class="text-muted">
                                        {{ $user->state ? $user->state . ', ' : '' }}{{ $user->country->name }}
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $user->created_at->format('M d, Y') }}
                                </small>
                            </td>
                            <td>
                                @if($user->hasVerifiedEmail())
                                    <span class="badge bg-success"
                                        title="Verified {{ $user->email_verified_at->format('M d, Y g:i A') }}"
										data-bs-toggle="tooltip">
                                        <i class="bi bi-check-circle" aria-hidden="true"></i> Verified
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-exclamation-circle" aria-hidden="true"></i> Unverified
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($user->role && $user->role->permissions->isNotEmpty())
                                    <div class="ability-list">
                                        @foreach($user->role->permissions as $permission)
                                            @php
                                                $permissionType = PermissionType::tryFrom($permission->name);
                                                $description = $permissionType ? PermissionType::description($permissionType) : null;
                                                $label = \Illuminate\Support\Str::headline(str_replace('-', ' ', $permission->name));
                                            @endphp
                                            <span class="badge text-bg-light border fw-normal" @if($description) title="{{ $description }}" @endif>{{ $label }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>
