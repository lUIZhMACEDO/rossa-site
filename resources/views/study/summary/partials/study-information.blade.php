<p class="text-muted mb-0">{{ $study->identifier }}</p>
@if ($study->user_id == auth()->id())
    <!-- status information -->
    <span class="badge text-bg-{{ $study->status->color() }}"
        style="cursor: default">
        {{ $study->status->label() }}
    </span>
@endif
<div class="row mt-3">
    <div class="col-md-6">
        <div>
            <h5>Title:</h5>
            <p class="ms-3">{{ $study->title }}</p>
        </div>
        @if ($study->completion_date)
            <div class="mt-3">
                <h5>Completion Date:</h5>
                <p class="ms-3">{{ $study->completion_date->format('F j, Y') }}</p>
            </div>
        @endif
    </div>

    <div class="col-md-6">
        <h5>Publication Information:</h5>
        <p class="ms-3">
            @if ($study->collection_id)
                <span class="text-muted small d-block mb-1">
                    This study belongs to a collection.
                </span>
                <a href="{{ route('collections.show', $study->collection) }}">
                    View publications from this collection
                </a>
            @else
                <strong>Published:</strong> {{ $study->is_published ? 'Yes' : 'No' }}
                @if ($study->is_published)
                    <br>
                    <strong>DOI:</strong> <a href="{{ $study->doiLink() }}" target="_blank">{{ $study->doi ?? '-' }}</a>
                    <br>
                    <strong>PubMed ID:</strong> <a href="{{ $study->pubmedLink() }}" target="_blank">{{ $study->pubmed_id ?? '-' }}</a>
                @else
                    @if ($study->publication_plan === 'different_journal')
                        <br>
                        <strong>Publication Plan:</strong> Different Journal
                        <br>
                        <strong>Embargo Period:</strong> {{ $study->embargo_months ?? '-' }} months
                    @elseif ($study->publication_plan === 'special_issue')
                        <br>
                        <strong>Publication Plan:</strong> Special Issue of Bone
                    @else
                        <strong>Publication Plan:</strong> {{ $study->publication_plan ?? '-' }}
                    @endif
                @endif
            @endif
        </p>
    </div>
</div>
<div>
    <h5>Summary</h5>
    <p class="ms-3">{{ $study->summary }}</p>
</div>
@if($study->funding_sources)
    <div class="mt-3">
        <h5>Funding Sources</h5>
        <p class="ms-3">{{ $study->funding_sources }}</p>
    </div>
@endif
@if($study->conflicts)
    <div class="mt-3">
        <h5>Conflicts of Interest</h5>
        <p class="ms-3">{{ $study->conflicts }}</p>
    </div>
@endif