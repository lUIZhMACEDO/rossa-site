@php
    use App\Enums\ApiTokenRequestStatus;
    use App\Enums\InstitutionType;

    $tokenRequest = auth()->user()->apiTokenRequest;
@endphp

<div data-api-token-access>
    @if (!auth()->user()->hasVerifiedEmail())
        <p class="text-muted mb-3">
            Submit a request and an administrator will review it before issuing your API token.
        </p>

        <div class="alert alert-warning">
            <p class="mb-2">
                Verify your email before requesting an API token.
                We sent a verification link when you registered.
            </p>
            <a href="{{ route('verification.notice') }}" class="btn btn-sm btn-dark me-2">
                Verify email
            </a>
            <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-dark">
                    Resend email
                </button>
            </form>
        </div>
    @elseif ($tokenRequest?->status === ApiTokenRequestStatus::Accepted && $tokenRequest->hasToken())
        @php
            $tier = $tokenRequest->category;
            $token = $tokenRequest->personalAccessToken;
        @endphp

        <p class="text-muted mb-3">Your API token is active.</p>

        <div class="card border shadow-sm" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="fw-semibold">ROSSA API</span>
                    @if ($tier)
                        <span class="badge {{ $tier->badgeClass() }}">{{ $tier->label() }}</span>
                    @endif
                </div>

                @if ($token)
                    <p class="text-muted small mb-3">
                        Created {{ $token->created_at->format('M j, Y') }}
                        @if ($token->last_used_at)
                            &middot; Last used {{ $token->last_used_at->diffForHumans() }}
                        @else
                            &middot; Never used
                        @endif
                    </p>
                @endif

                <label class="form-label small text-muted mb-1">API token</label>
                <div class="input-group" data-api-token-display>
                    <input type="password"
                        data-api-token-input
                        class="form-control font-monospace bg-light user-select-none border-secondary-subtle"
                        style="cursor: default; pointer-events: none;"
                        readonly
                        tabindex="-1"
                        aria-readonly="true"
                        value="{{ $tokenRequest->getPlainToken() }}"
                        autocomplete="off">
                    <button type="button"
                        class="btn btn-outline-secondary"
                        data-api-token-toggle
                        title="Show token"
                        data-bs-toggle="tooltip">
                        <i class="bi bi-eye" data-api-token-toggle-icon aria-hidden="true"></i>
                    </button>
                    <button type="button"
                        class="btn btn-outline-secondary"
                        data-api-token-copy
                        title="Copy token"
                        data-bs-toggle="tooltip">
                        <i class="bi bi-clipboard" aria-hidden="true"></i>
                    </button>
                </div>

                <p class="text-muted small mt-2 mb-0">
                    Use as <code>Authorization: Bearer YOUR_TOKEN</code>.
                    Need changes? <a href="{{ route('contact') }}">Contact us</a>.
                </p>
            </div>
        </div>
    @elseif ($tokenRequest?->status === ApiTokenRequestStatus::Pending)
        <p class="text-muted mb-3">
            Submit a request and an administrator will review it before issuing your API token.
        </p>
        <div class="alert alert-info mb-0">
            <p class="mb-0">
                Your API token request was submitted on {{ $tokenRequest->created_at->format('M j, Y') }}.
                An administrator will review it shortly.
            </p>
        </div>
    @elseif ($tokenRequest?->status === ApiTokenRequestStatus::Rejected)
        <p class="text-muted mb-3">
            Submit a request and an administrator will review it before issuing your API token.
        </p>
        <div class="alert alert-warning mb-0">
            <p class="mb-0">
                Your API token request was not approved.
                <a href="{{ route('contact') }}">Contact us</a> if you have questions.
            </p>
        </div>
    @elseif ($tokenRequest?->status === ApiTokenRequestStatus::Disabled)
        <p class="text-muted mb-3">
            Submit a request and an administrator will review it before issuing your API token.
        </p>
        <div class="alert alert-secondary mb-0">
            <p class="mb-0">
                Your API token has been disabled.
                <a href="{{ route('contact') }}">Contact us</a> if you need access restored.
            </p>
        </div>
    @else
        <p class="text-muted mb-3">
            Submit a request and an administrator will review it before issuing your API token.
        </p>

        <button type="button"
            data-api-token-request-open
            class="btn btn-dark"
            data-bs-toggle="modal"
            data-bs-target="#apiTokenRequestModal">
            Request API Token
        </button>

        <div class="modal fade" id="apiTokenRequestModal" tabindex="-1" aria-labelledby="apiTokenRequestModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form data-api-token-request-form
                        action="{{ route('api-token-request.store') }}"
                        method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="apiTokenRequestModalLabel">Request API token</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small">
                                Tell us who you are and how you plan to use the API. An administrator will review your request.
                            </p>
                            <div class="mb-3">
                                <label for="api-token-message" class="form-label">Request details</label>
                                <textarea id="api-token-message"
                                    data-api-token-message
                                    name="message"
                                    class="form-control"
                                    rows="5"
                                    required
                                    minlength="20"
                                    maxlength="2000"
                                    placeholder="Describe your project, organization, and intended API use."></textarea>
                                <div data-api-token-request-error class="invalid-feedback d-block"></div>
                            </div>
                            <x-api-terms-notice compact class="mt-3" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" data-api-token-request-submit class="btn btn-dark">
                                Submit request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
