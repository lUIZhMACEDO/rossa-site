@if (!config('api.enabled'))
    <div class="alert alert-warning mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        {{ config('api.disabled_message') }}
    </div>
@endif
