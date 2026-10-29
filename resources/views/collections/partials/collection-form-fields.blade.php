{{-- Shared collection metadata fields. Included inside a <form> by both the
     admin edit page (collection-information.blade.php) and the investigator
     edit page (my-collection-edit.blade.php), so there is one copy of the
     field markup rather than two that can drift apart. --}}

<div class="mb-3">
    <label for="title" class="form-label required">Collection Title</label>
    <input type="text" class="form-control @error('title') is-invalid @enderror"
        id="title" name="title" value="{{ old('title', $collection->title) }}"
        @error('title') aria-describedby="title-error" @enderror>
    @error('title')
        <div id="title-error" class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

<div class="mb-3">
    <label for="summary" class="form-label">Collection Summary</label>
    <textarea class="form-control @error('summary') is-invalid @enderror"
            id="summary" name="summary" rows="4"
            @error('summary') aria-describedby="summary-error" @enderror>{{ old('summary', $collection->summary) }}</textarea>
    @error('summary')
        <div id="summary-error" class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

<div class="mb-3">
    <label for="funding_sources" class="form-label">Funding Sources</label>
    <textarea class="form-control @error('funding_sources') is-invalid @enderror"
            id="funding_sources" name="funding_sources" rows="2"
            @error('funding_sources') aria-describedby="funding_sources-error" @enderror>{{ old('funding_sources', $collection->funding_sources) }}</textarea>
    @error('funding_sources')
        <div id="funding_sources-error" class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

<div class="mb-3">
    <label for="conflicts" class="form-label">Conflicts of Interest</label>
    <textarea class="form-control @error('conflicts') is-invalid @enderror"
            id="conflicts" name="conflicts" rows="2"
            @error('conflicts') aria-describedby="conflicts-error" @enderror>{{ old('conflicts', $collection->conflicts) }}</textarea>
    @error('conflicts')
        <div id="conflicts-error" class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

<div class="mb-3">
    <div class="col-md-3">
        <label for="completion_date" class="form-label">Collection Completion Date</label>
        <input type="date" class="form-control @error('completion_date') is-invalid @enderror"
            id="completion_date" name="completion_date"
            value="{{ old('completion_date', $collection->completion_date?->format('Y-m-d')) }}"
            @error('completion_date') aria-describedby="completion_date-error" @enderror>
        @error('completion_date')
            <div id="completion_date-error" class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>
