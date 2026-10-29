@props(['message' => 'Loading...'])

<div class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{ $message }}</span>
    </div>
    <p class="text-muted mt-3">{{ $message }}</p>
</div>