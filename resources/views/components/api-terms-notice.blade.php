@props([
    'compact' => false,
])

@php
    $termsUrl = config('api.terms_url') ?: route('about.terms-of-use');
@endphp

@if ($compact)
    <p {{ $attributes->merge(['class' => 'text-muted small mb-0']) }}>
        By submitting this request, you confirm that you have read and agree to the
        <a href="{{ $termsUrl }}" target="_blank" rel="noopener">API terms and licensing agreement</a>.
    </p>
@else
    <div {{ $attributes }}>
        <h4 class="mb-3">Terms and licensing</h4>
        <p class="text-muted mb-3">
            Use of the ROSSA API is subject to the same repository terms and licensing policies that govern
            ROSSA data, including attribution requirements and restrictions on commercial use.
            Review the agreement before requesting a token or building integrations against the API.
        </p>
        <a href="{{ $termsUrl }}" class="link-dark">
            Read the full terms and licensing agreement <i class="bi bi-box-arrow-up-right small"></i>
        </a>
    </div>
@endif
