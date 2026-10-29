<div>
    <form wire:submit.prevent="submitQuery" class="mb-3">
        <div class="mb-3">
            <label for="diseaseQuery" class="form-label">Search Disease (MONDO)</label>
            <input type="text" class="form-control" id="diseaseQuery" wire:model="query" wire:keydown.enter.prevent="submitQuery" placeholder="Enter disease name or MONDO ID">
        </div>
        <button type="button" class="btn btn-primary" wire:click="submitQuery">Search</button>
    </form>

    @if(isset($results))
        @php 
            // sort results by label
            usort($results, function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
        @endphp
        <div class="my-3">
            @if(!empty($query))
                <h4 class="mb-3">Search Results for "{{ $query }}":</h4>
            @endif
            <div class="row">
                <div class="col-md-8">
                    <ul class="list-group list-group-flush overflow-auto" style="max-height: 300px;">
                        @forelse($results as $i => $result)
                            <li wire:click="selectDisease('{{ $result['iri']}}')"
                                class="bg-light list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3 hover-bg-secondary"
                                style="cursor: pointer;">
                                <span class="text-primary">{{ $result['label'] }}</span>
                                <small class="text-muted">{{ $result['obo_id'] }}</small>
                            </li>
                        @empty
                            @if(!empty($query))
                            <div class="alert alert-info" role="alert">
                                No results found.
                            </div>
                            @endif
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if ($selected->isNotEmpty())
        <div class="container-fluid d-flex flex-column ps-2">
            <div class="row g-3">
            @foreach($selected as $disease)
                <div class="col-12 ps-0">
                    <div class="d-flex gap-2">
                        
                        <div class="card w-100" style="border-left: 4px solid var(--bs-primary);">
                            <div class="card-body p-3">
                                <h6 class="card-title mb-2">
                                    <strong>{{ $disease->label }}</strong>
                                </h6>
                                @if ($disease->iri)
                                    <a href="{{ $disease->iri }}"
                                        target="_blank"
                                        class="text-decoration-none whitespace-nowrap small"
                                        title="View Disease Line"
                                        data-bs-toggle="tooltip">
                                        {{ $disease->obo_id }}
                                    </a>
                                @else
                                    <span class="text-muted whitespace-nowrap small">{{ $disease->obo_id }}</span>
                                @endif
                                @if(!empty($disease->xrefs))
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        @foreach($disease->xrefs as $xref)
                                            @if($xref['url'])
                                                <a href="{{ $xref['url'] }}"
                                                    target="_blank"
                                                    class="text-decoration-none whitespace-nowrap small"
                                                    title="{{ $xref['url'] }}"
                                                    data-bs-toggle="tooltip">
                                                    {{ $xref['database'] }}: {{ $xref['id'] }}
                                                </a>
                                            @else
                                                <span class="whitespace-nowrap small">{{ $xref['database'] }}: {{ $xref['id'] }}</span>
                                            @endif
                                            @if ( !$loop->last )
                                                <span>|</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted mb-0 small">No cross-references available.</p>
                                @endif
                                <button
                                    class="btn btn-danger btn-sm shrink-0 position-absolute top-0 end-0 m-2"
                                    type="button"
                                    wire:click.stop="removeDisease('{{ $disease['iri'] }}')"
                                    title="Remove Disease">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
