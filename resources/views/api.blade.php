@extends('layout')

@section('title', 'API')
@section('description', 'Programmatic JSON access to ROSSA rodent skeletal studies, genes, diseases, and analysis data for research tools and pipelines.')

@section('content')
@php
    use App\Enums\InstitutionType;
@endphp
<div class="container py-5">
    <x-api-status-notice />

    {{-- Page Header (match About page styling) --}}
    <div class="text-center mb-4">
        <h1 class="display-5 fw-normal text-dark mb-3">ROSSA API</h1>
        <p class="mb-0">Programmatic access to the ROSSA database</p>
        <div class="mx-auto bg-dark" style="width: 60px; height: 1px;"></div>
    </div>

    <div class="row justify-content-center mb-4">
        <div class="col-lg-10">
            <p class="fs-5 text-dark mb-0">
                Programmatic access to the ROSSA database of rodent skeletal studies, genes,
                diseases, and analysis data. The API returns JSON and is designed for use by
                external research tools, bioinformatics pipelines, and institutional integrations.
            </p>
        </div>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-lg-10 d-flex justify-content-center">
            <a href="/docs/api" target="_blank" rel="noopener" class="btn btn-dark btn-lg">
                View Interactive Docs <i class="bi bi-box-arrow-up-right ms-1"></i>
            </a>
        </div>
    </div>

    <hr class="mb-5">

    {{-- Access Tiers --}}
    <div class="row mb-5">
        <div class="col-12">
            <h4 class="mb-3">Access Tiers</h4>
            <p class="text-muted mb-3">
                All endpoints are publicly available without a token. Providing a token unlocks
                higher limits appropriate for your use case. Tokens are free and issued after an
                administrator reviews your request.
            </p>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Tier</th>
                            <th>Who it's for</th>
                            <th>Token required?</th>
                            <th>Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Public</strong></td>
                            <td>Anyone — no sign-up required</td>
                            <td>
                                <span class="text-danger fw-semibold">
                                    <i class="bi bi-x-circle-fill me-1"></i>No
                                </span>
                            </td>
                            <td><span class="badge bg-secondary">Limited</span></td>
                        </tr>
                        @foreach (InstitutionType::cases() as $type)
                            <tr>
                                <td><strong>{{ $type->label() }}</strong></td>
                                <td>{{ $type->whoItsFor() }}</td>
                                <td>
                                    <span class="text-success fw-semibold">
                                        <i class="bi bi-check-circle-fill me-1"></i>Yes
                                    </span>
                                </td>
                                <td><span class="badge {{ $type->usageBadgeClass() }}">{{ $type->usageLabel() }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mt-2">All tiers are free of charge.</p>
        </div>
    </div>

    {{-- API Access --}}
    <div class="row mb-5">
        <div class="col-lg-8">
            <h4 class="mb-3">Get API Access</h4>

            @guest
                <p class="text-muted mb-3">
                    Sign in to request an API token. Tokens are free and issued after an administrator reviews your request.
                </p>
                <a href="{{ route('login.redirect') }}" class="btn btn-dark">
                    Sign in to request access
                </a>
            @endguest

            @auth
                <x-api-token-access />
            @endauth
        </div>
    </div>

    <hr class="mb-5">

    {{-- Base URL --}}
    <div class="row mb-5">
        <div class="col-12">
            <h4 class="mb-3">Base URL</h4>
            <pre class="bg-light border rounded p-3 mb-0"><code>{{ url('/api') }}</code></pre>
            <p class="text-muted small mt-2">All responses are JSON. Pass your token as a <code>Bearer</code> header when required.</p>
        </div>
    </div>

    {{-- Example Usage --}}
    <div class="row mb-5">
        <div class="col-12">
            <h4 class="mb-4">Example Requests</h4>

            <div class="mb-4">
                <h6 class="text-uppercase text-muted small fw-semibold mb-2">List approved studies</h6>
                <pre class="bg-dark text-light rounded p-3 mb-1"><code>GET /api/studies?q=femur&amp;per_page=10</code></pre>
                <pre class="bg-light border rounded p-3"><code>curl "{{ url('/api/studies') }}?q=femur&per_page=10"</code></pre>
            </div>

            <div class="mb-4">
                <h6 class="text-uppercase text-muted small fw-semibold mb-2">Get a study by ROSSA identifier</h6>
                <pre class="bg-dark text-light rounded p-3 mb-1"><code>GET /api/studies/{identifier}</code></pre>
                <pre class="bg-light border rounded p-3"><code>curl "{{ url('/api/studies') }}/ROSSA-20250101-XXXX"</code></pre>
            </div>

            <div class="mb-4">
                <h6 class="text-uppercase text-muted small fw-semibold mb-2">Get genes associated with a study</h6>
                <pre class="bg-dark text-light rounded p-3 mb-1"><code>GET /api/studies/{identifier}/genes</code></pre>
                <pre class="bg-light border rounded p-3"><code>curl "{{ url('/api/studies') }}/ROSSA-20250101-XXXX/genes"</code></pre>
            </div>

            <div class="mb-4">
                <h6 class="text-uppercase text-muted small fw-semibold mb-2">Authenticated request (user token)</h6>
                <pre class="bg-light border rounded p-3"><code>curl "{{ url('/api/studies') }}" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"</code></pre>
            </div>
        </div>
    </div>

    {{-- CTA --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="border rounded p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Interactive documentation</h5>
                    <p class="text-muted mb-0 small">
                        Explore all endpoints, query parameters, and response schemas.
                        Try requests directly from the browser.
                    </p>
                </div>
                <a href="/docs/api" target="_blank" rel="noopener" class="btn btn-outline-dark ms-4 flex-shrink-0">
                    Open API Docs <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <hr class="mb-5">

    <div class="row justify-content-center">
            <x-api-terms-notice />
    </div>
</div>
@endsection
