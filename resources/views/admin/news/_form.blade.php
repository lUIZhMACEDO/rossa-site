@php
    $post = $newsPost ?? null;
@endphp

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <p class="fw-semibold mb-1">Please correct the following:</p>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label for="published_at" class="form-label">Publish date</label>
    <input id="published_at" name="published_at" type="date" required
           class="form-control @error('published_at') is-invalid @enderror"
           value="{{ old('published_at', optional($post?->published_at)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
    <div class="form-text">Controls the date shown on the site and used for sorting. Defaults to today.</div>
</div>

<div class="mb-3">
    <label for="title" class="form-label">Title</label>
    <input id="title" name="title" type="text" maxlength="150" required
           class="form-control @error('title') is-invalid @enderror"
           value="{{ old('title', $post?->title) }}">
    <div class="form-text">Maximum 150 characters.</div>
</div>

<div class="mb-3">
    <label for="excerpt" class="form-label">Excerpt</label>
    <textarea id="excerpt" name="excerpt" maxlength="500" rows="3" required
              class="form-control @error('excerpt') is-invalid @enderror">{{ old('excerpt', $post?->excerpt) }}</textarea>
    <div class="form-text">Maximum 500 characters. This appears on the homepage and news page.</div>
</div>

<div class="mb-3">
    <label for="link_url" class="form-label">Link <span class="text-muted fw-normal">(optional)</span></label>
    <input id="link_url" name="link_url" type="text" maxlength="2048"
           class="form-control @error('link_url') is-invalid @enderror"
           value="{{ old('link_url', $post?->link_url) }}">
    <div class="form-text">Use an HTTP/HTTPS URL or a site-relative path such as /guides/data-ingestion.</div>
</div>

<div class="mb-3">
    <label for="link_label" class="form-label">Link label <span class="text-muted fw-normal">(optional)</span></label>
    <input id="link_label" name="link_label" type="text" maxlength="150"
           class="form-control @error('link_label') is-invalid @enderror"
           value="{{ old('link_label', $post?->link_label) }}">
    <div class="form-text">Provide both a link and label, or leave both blank. Maximum 150 characters.</div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>