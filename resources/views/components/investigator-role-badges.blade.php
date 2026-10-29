@props(['labels'])

<span {{ $attributes->class('d-inline-flex flex-wrap gap-1') }}>
    @forelse($labels as $label)
        <span @class([
            'badge fw-normal',
            'text-bg-secondary' => $label === 'Contributing',
            'text-bg-primary' => $label !== 'Contributing',
        ])>{{ $label }}</span>
    @empty
        <span class="text-muted">—</span>
    @endforelse
</span>
