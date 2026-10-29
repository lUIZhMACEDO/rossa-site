<x-layouts.account title="API Access">
    <div class="card shadow-sm" style="border-left: 4px solid var(--bs-primary);">
        <div class="card-body p-4">
            <x-api-status-notice />

            <h1 class="h3 mb-2">API Access</h1>
            <p class="text-muted mb-4">
                Request and manage your ROSSA API token. Tokens are free and issued after an administrator reviews your request.
            </p>

            <x-api-token-access />

            <div class="mt-4 pt-3 border-top">
                <a href="{{ route('api-docs') }}" class="btn btn-outline-dark btn-sm" target="_blank" rel="noopener">
                    View API documentation <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</x-layouts.account>
