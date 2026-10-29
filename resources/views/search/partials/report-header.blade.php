<div class="p-4 mb-4" style="background-color: #0D3B52; border-radius: 0.5rem;">
    <h1 class="mb-1" style="color: #fff;">{{ $study->title }}</h1>
    <p class="mb-2 font-monospace" style="color: #DCEEF3;">{{ $study->identifier }}</p>
    <a href="{{ $backUrl ?? route('search.index') }}" class="btn btn-sm" style="background-color: #fff; color: #0D3B52; border: none;">
        <i class="bi bi-arrow-left"></i> {{ $backLabel ?? 'Back to Search' }}
    </a>
</div>

@if ($study->isEmbargoed())
    <div class="alert alert-warning mb-4">
        <i class="bi bi-lock"></i> <strong>This study is currently under embargo</strong> and is not visible to the public. Certain details and data may not be available until the embargo is lifted.
    </div>
@endif

@if (auth()->user() && auth()->user()->hasPermission('manage-studies'))
    <div class="d-flex align-items-center gap-2 p-2 px-3 mb-4" style="background-color: #F7F6F3; border: 1px solid #D3D1C7; border-radius: 0.375rem; font-size: 0.875rem;">
        <i class="bi bi-shield-lock"></i> <strong>Admin Action:</strong>
        <a href="{{ route('study.study-information', $study) }}" class="btn btn-primary btn-sm ms-1">
            <i class="bi bi-pencil"></i> Edit Study
        </a>
    </div>
@endif
