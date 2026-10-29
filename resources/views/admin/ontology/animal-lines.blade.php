<x-layout.ontology-admin>
<div class="mt-4">
    <!-- Search Form -->
    <div class="row mb-3">
        <div class="col-md-6">
            <form method="GET" action="{{ route('admin.ontology') }}" class="d-flex">
                <input type="text" name="animal_search" class="form-control" 
                        placeholder="Search animal strains by label, description, IRI, or ontology..." 
                        value="{{ request('animal_search') }}">
                <button type="submit" class="btn btn-outline-primary ms-2">Search</button>
                @if(request('animal_search'))
                    <a href="{{ route('admin.ontology') }}" class="btn btn-outline-secondary ms-2">Clear</a>
                @endif
            </form>
        </div>
        <div class="col-md-6 text-end">
            @if(request('animal_search'))
                <small class="text-muted">
                    Showing {{ $animalLines->total() }} result(s) for "{{ request('animal_search') }}"
                </small>
            @else
                <small class="text-muted">
                    Showing {{ $animalLines->total() }} total animal strains
                </small>
            @endif
        </div>
    </div>

    @if($animalLines->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Species</th>
                        <th>Description</th>
                        <th>Ontology</th>
                        <th>Synonyms</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($animalLines as $line)
                        <tr>
                            <td>
                                <strong>{{ $line->label }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $line->species }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ Str::limit($line->description ?? 'N/A', 80) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $line->ontology }}</span>
                            </td>
                            <td>
                                @if($line->synonyms && count($line->synonyms) > 0)
                                    @foreach(array_slice($line->synonyms, 0, 3) as $synonym)
                                        <span class="badge bg-secondary me-1">{{ $synonym }}</span>
                                    @endforeach
                                    @if(count($line->synonyms) > 3)
                                        <small class="text-muted">+{{ count($line->synonyms) - 3 }} more</small>
                                    @endif
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $line->updated_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ $line->iri }}" target="_blank" 
                                        class="btn btn-outline-secondary btn-sm" title="View IRI">
                                        View IRI
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center mt-3">
            {{ $animalLines->links() }}
        </div>
    @else
        <div class="text-center py-5">
            @if(request('animal_search'))
                <p class="text-muted">No animal strains found matching "{{ request('animal_search') }}".</p>
                <a href="{{ route('admin.ontology') }}" class="btn btn-outline-primary">Clear search</a>
            @else
                <p class="text-muted">No animal strains found.</p>
            @endif
        </div>
    @endif
</div>
</x-layout.ontology-admin>