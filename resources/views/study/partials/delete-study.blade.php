@if($study->isOwner(auth()->user()))
    <form action="{{ route('study.destroy', $study) }}" method="POST" class="d-inline"
        onsubmit="return confirm('Are you sure you want to delete this study? It will be removed from study lists and public access. Its data will be retained so the ROSSA team can restore it if needed.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Delete study {{ $study->identifier }}">
            <i class="bi bi-trash" aria-hidden="true"></i> Delete
        </button>
    </form>
@endif
