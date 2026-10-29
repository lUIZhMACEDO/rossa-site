@props([
    'color' => 'primary',
    'selected' => false,
    'buttonName' => '',
    'buttonValue' => '',
    'hideSelectButton' => false,
])

<div class="card h-100 w-100 border-0 rounded-0" style="border-top: 5px solid var(--bs-{{ $color }}) !important;">
    <div class="card-body d-flex flex-column p-2 px-3">
        <h6 class="card-title fw-semibold mb-1" style="font-size: .875rem; line-height: 1.3;">{{ $title }}</h6>
        <div class="mb-2 text-secondary" style="font-size: .8125rem; line-height: 1.35;">
            {{ $slot }}
        </div>
        @if(!$hideSelectButton)
        <div class="d-flex mt-auto">
            <button class="btn btn-outline-dark w-100 fw-semibold"
                style="font-size: .875rem;"
                name="{{ $buttonName }}"
                value="{{ $buttonValue }}">
                {{ $button ?? 'Select' }}
            </button>
        </div>
        @endif
    </div>
</div>