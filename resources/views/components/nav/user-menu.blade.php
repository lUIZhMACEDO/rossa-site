@php
    $user = auth()->user();
    $label = $user->display_name !== '' ? $user->display_name : $user->email;
    $showMyCollections = (
            $user->hasPermission(\App\Enums\PermissionType::MANAGE_COLLECTIONS->value)
            ||
            $user->hasPermission(\App\Enums\PermissionType::VIEW_OWN_COLLECTIONS->value)
            || $user->hasPermission(\App\Enums\PermissionType::EDIT_OWN_COLLECTIONS->value)
            || $user->hasPermission(\App\Enums\PermissionType::SUBMIT_OWN_COLLECTIONS->value)
        )
        && \App\Models\Collection::visibleToUser($user)->exists();
@endphp

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center"
        href="#"
        id="userMenuDropdown"
        role="button"
        data-bs-toggle="dropdown"
        aria-expanded="false">
        Welcome, {{ $label }}
        @if (($adminNotificationCount ?? 0) > 0)
            <span class="badge text-bg-danger ms-2">{{ $adminNotificationCount }}</span>
        @endif
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown">
        <li>
            <a class="dropdown-item" href="{{ route('home') }}">ROSSA Home</a>
        </li>
        @if($showMyCollections)
            <li>
                <a class="dropdown-item" href="{{ route('my-studies') }}#my-collections">My Collections</a>
            </li>
        @endif
        @canany(['manage-studies', 'manage-users', 'manage-feedback', 'manage-facilities', 'manage-collections'])
            <li>
                <a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ route('admin.studies') }}">
                    Admin
                    @if (($adminNotificationCount ?? 0) > 0)
                        <span class="badge text-bg-danger ms-2">{{ $adminNotificationCount }}</span>
                    @endif
                </a>
            </li>
        @endcanany
        <li>
            <a class="dropdown-item" href="{{ route('account.profile') }}">Account</a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('logout') }}">Logout</a>
        </li>
    </ul>
</li>
