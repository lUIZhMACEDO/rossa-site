@props(['id'])

<div id="{{ $id }}" {{ $attributes->merge(['class' => 'accordion mb-4']) }}>
    <div class="accordion-item">
        <h4 class="accordion-header">
            <button class="accordion-button collapsed fw-bold fs-4" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $id }}Item" aria-expanded="false" aria-controls="{{ $id }}Item">
                <div class="span d-flex">
                    <span>@if ( isset($title) ) {{ $title }} @else Title Here @endif</span>
                    {{-- <span class="badge text-bg-warning ms-3 fs-6 status-badge"><i class="bi-exclamation-triangle"></i> Has Empty Fields</span> --}}
                </div>
            </button>
        </h4>
        <div id="{{ $id }}Item" class="accordion-collapse collapse text-bg-light" data-bs-parent="#{{ $id}}">
            <div class="accordion-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>