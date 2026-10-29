<x-layout.ontology-admin>
    <div class="mt-4">
        <!-- Search Form -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" action="{{ route('admin.ontology.disease-lines') }}" class="d-flex">
                    <input type="text" name="disease_search" class="form-control" 
                            placeholder="Search disease lines by label, description, IRI, ontology, or short form..." 
                            value="{{ request('disease_search') }}">
                    <button type="submit" class="btn btn-outline-primary ms-2">Search</button>
                    @if(request('disease_search'))
                        <a href="{{ route('admin.ontology') }}" class="btn btn-outline-secondary ms-2">Clear</a>
                    @endif
                    <!-- Preserve animal search when searching diseases -->
                    @if(request('animal_search'))
                        <input type="hidden" name="animal_search" value="{{ request('animal_search') }}">
                    @endif
                </form>
            </div>
            <div class="col-md-6 text-end">
                @if(request('disease_search'))
                    <small class="text-muted">
                        Showing {{ $diseaseLines->total() }} result(s) for "{{ request('disease_search') }}"
                    </small>
                @else
                    <small class="text-muted">
                        Showing {{ $diseaseLines->total() }} total disease lines
                    </small>
                @endif
            </div>
        </div>

        @if($diseaseLines->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Description</th>
                            <th>Ontology</th>
                            <th>Short Form</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($diseaseLines as $line)
                            <tr>
                                <td>
                                    <strong>{{ $line->label }}</strong>
                                </td>
                                <td>
                                    <span class="text-muted">{{ Str::limit($line->description ?? 'N/A', 80) }}</span>
                                </td>
                                <td>
                                    <span class="badge text-bg-warning">{{ $line->ontology }}</span>
                                </td>
                                <td>
                                    @if($line->short_form)
                                        <code>{{ $line->short_form }}</code>
                                    @else
                                        <span class="text-muted">N/A</span>
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
                {{ $diseaseLines->links() }}
            </div>
        @else
            <div class="text-center py-5">
                @if(request('disease_search'))
                    <p class="text-muted">No disease lines found matching "{{ request('disease_search') }}".</p>
                    <a href="{{ route('admin.ontology') }}" class="btn btn-outline-primary">Clear search</a>
                @else
                    <p class="text-muted">No disease lines found.</p>
                @endif
            </div>
        @endif
    </div>
</x-layout.ontology-admin>