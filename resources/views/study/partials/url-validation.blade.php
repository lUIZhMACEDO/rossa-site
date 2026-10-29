@php
    $isValid = (bool) ($valid ?? false);
@endphp

<div
    class="d-inline-flex align-items-center gap-1 small text-dark border rounded px-2 py-1 {{ $isValid ? 'bg-success-subtle border-success-subtle' : 'bg-warning-subtle border-warning-subtle' }}"
    role="status"
    aria-live="polite"
>
    <i class="bi {{ $isValid ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}" aria-hidden="true"></i>
    <span>{{ $message ?? ($isValid ? 'URL looks valid.' : 'Unable to validate this URL.') }}</span>
</div>