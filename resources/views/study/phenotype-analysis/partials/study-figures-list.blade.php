@php
    $sectionNumber = $sectionNumber ?? '02';
    $scanEditable = $scanEditable ?? (! $study->isLockedForEditing(auth()->user())
        && ! $study->isPhenotypeAnalysisLocked(auth()->user()));
    $studyFigures = $studyFigures ?? collect();
@endphp

<div class="rounded p-3 p-md-4" style="background-color: #0D3B52;">
    <div class="d-flex align-items-baseline gap-2 mb-3">
        <span class="font-monospace" style="font-size: .9375rem; color: #E6F1FB;">{{ $sectionNumber }}</span>
        <h2 class="h5 mb-0" style="color: #fff;">Uploaded figures</h2>
        @if($studyFigures->isNotEmpty())
            <span style="font-size: .8125rem; color: #E6F1FB;">
                {{ $studyFigures->count() }} {{ Str::plural('figure', $studyFigures->count()) }}
            </span>
        @endif
    </div>

    <div class="bg-white rounded p-3">
        @forelse ($studyFigures as $figure)
            <div class="d-flex flex-column flex-md-row gap-3 py-3 @if(! $loop->last) border-bottom @endif">
                <div class="flex-shrink-0" style="width: 8rem;">
                    <img src="{{ route('study.figures.show', [$study, $figure]) }}"
                         alt="{{ $figure->caption }}"
                         class="img-fluid rounded border figure-preview-thumb"
                         style="max-height: 6rem; object-fit: contain; cursor: zoom-in;"
                         role="button"
                         tabindex="0"
                         title="Click to view full screen"
                         data-bs-toggle="modal"
                         data-bs-target="#figureFullscreenModal"
                         data-figure-src="{{ route('study.figures.show', [$study, $figure]) }}"
                         data-figure-caption="{{ $figure->caption }}">
                </div>
                <div class="flex-grow-1">
                    <div class="small text-muted mb-1 font-monospace">{{ $figure->original_filename }}</div>
                    @if($scanEditable)
                        <div class="d-flex flex-column flex-sm-row gap-2 align-items-sm-start">
                            <form method="POST"
                                  action="{{ route('study.figures.update', [$study, $figure]) }}"
                                  class="d-flex flex-grow-1 flex-column flex-sm-row gap-2 align-items-sm-start">
                                @csrf
                                @method('PATCH')
                                <textarea name="caption"
                                          class="form-control form-control-sm"
                                          rows="3"
                                          maxlength="500"
                                          required>{{ old('caption', $figure->caption) }}</textarea>
                                <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap">Save caption</button>
                            </form>
                            <form method="POST"
                                  action="{{ route('study.figures.destroy', [$study, $figure]) }}"
                                  class="flex-shrink-0"
                                  onsubmit="return confirm('Delete this figure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">
                                    <i class="bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    @else
                        <div style="color: #2C2C2A;">{{ $figure->caption }}</div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-4" style="color: #2C2C2A;">
                No figures uploaded yet.
            </div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="figureFullscreenModal" tabindex="-1" aria-labelledby="figureFullscreenCaption" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white text-truncate me-3" id="figureFullscreenCaption"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex align-items-center justify-content-center p-2 p-md-4">
                <img id="figureFullscreenImg"
                     src=""
                     alt=""
                     class="img-fluid"
                     style="max-height: calc(100vh - 5rem); max-width: 100%; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('figureFullscreenModal');
    if (!modal) return;

    const img = document.getElementById('figureFullscreenImg');
    const caption = document.getElementById('figureFullscreenCaption');

    modal.addEventListener('show.bs.modal', function (event) {
        const thumb = event.relatedTarget;
        if (!thumb) return;
        img.src = thumb.dataset.figureSrc || thumb.src;
        img.alt = thumb.dataset.figureCaption || thumb.alt || '';
        caption.textContent = thumb.dataset.figureCaption || '';
    });

    modal.addEventListener('hidden.bs.modal', function () {
        img.src = '';
        img.alt = '';
        caption.textContent = '';
    });

    document.querySelectorAll('.figure-preview-thumb').forEach(function (thumb) {
        thumb.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                thumb.click();
            }
        });
    });
});
</script>
