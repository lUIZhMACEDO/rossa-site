<div>
    @if($publications->isNotEmpty())
        <table class="table table-sm mb-3">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>DOI</th>
                    <th>PubMed ID</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($publications as $publication)
                    <tr wire:key="publication-{{ $publication->id }}">
                        <td>{{ $publication->title ?: '—' }}</td>
                        <td>{{ $publication->doi ?: '—' }}</td>
                        <td>{{ $publication->pubmed_id ?: '—' }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                wire:click="removePublication({{ $publication->id }})"
                                wire:confirm="Remove this publication?">
                                Remove
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label for="pub-title" class="form-label">Title</label>
            <input type="text" class="form-control @error('newPublication.title') is-invalid @enderror"
                id="pub-title" wire:model="newPublication.title">
        </div>
        <div class="col-md-3">
            <label for="pub-doi" class="form-label">DOI</label>
            <input type="text" class="form-control @error('newPublication.doi') is-invalid @enderror"
                id="pub-doi" wire:model="newPublication.doi" placeholder="xxxxx/xxxxx">
        </div>
        <div class="col-md-3">
            <label for="pub-pubmed" class="form-label">PubMed ID</label>
            <input type="text" class="form-control @error('newPublication.pubmed_id') is-invalid @enderror"
                id="pub-pubmed" wire:model="newPublication.pubmed_id">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-outline-primary w-100" wire:click="addPublication">
                Add
            </button>
        </div>
    </div>
</div>
