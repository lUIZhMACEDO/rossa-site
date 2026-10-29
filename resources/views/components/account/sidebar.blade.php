@php
    $user = auth()->user();

    $items = [
        [
            'label' => 'Profile',
            'route' => 'account.profile',
            'active' => request()->routeIs('account.profile'),
        ],
        [
            'label' => 'Security',
            'route' => 'account.security',
            'active' => request()->routeIs('account.security'),
        ],
        [
            'label' => 'API Access',
            'route' => 'account.api',
            'active' => request()->routeIs('account.api'),
        ],
    ];
@endphp

<div class="card shadow-sm account-sidebar">
    <div class="card-body p-0">
        <div class="px-3 py-3 border-bottom bg-light">
            <h2 class="h5 mb-2">Account</h2>
            <p class="fw-semibold mb-1">{{ $user->display_name }}</p>
            <p class="text-muted small mb-0 text-break">{{ $user->email }}</p>
        </div>
        <div class="list-group list-group-flush">
            @foreach ($items as $item)
                <a href="{{ route($item['route']) }}"
                    @class([
                        'list-group-item list-group-item-action',
                        'active' => $item['active'],
                    ])>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
