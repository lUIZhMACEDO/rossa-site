@extends('layout')

@section('content')
<div class="py-5">
    <div class="container">
        <h1>Gene Database</h1>

        <p>This is powered by the <a href="https://www.alliancegenome.org/">Alliance of Genome Resources</a>.</p>

        <p>Genes are added upon successful selection or search in the test forms.</p>

        <!-- Bootstrap Tabs -->
        <ul class="nav nav-tabs" id="geneAdminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="genes-list-tab" data-bs-toggle="tab" data-bs-target="#genes-list" 
                        type="button" role="tab" aria-controls="genes-list" aria-selected="true">
                    Genes List ({{ $genes->total() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="test-forms-tab" data-bs-toggle="tab" data-bs-target="#test-forms" 
                        type="button" role="tab" aria-controls="test-forms" aria-selected="false">
                    Test Forms
                </button>
            </li>
        </ul>

        <div class="tab-content" id="geneAdminTabsContent">
            <!-- Genes List Tab -->
            <div class="tab-pane fade show active" id="genes-list" role="tabpanel" aria-labelledby="genes-list-tab">
                <div class="mt-4">
                    <!-- Search Form -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('admin.genes.index') }}" class="d-flex">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search genes by symbol, name, Alliance ID, or species..." 
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-outline-primary ms-2">Search</button>
                                @if(request('search'))
                                    <a href="{{ route('admin.genes.index') }}" class="btn btn-outline-secondary ms-2">Clear</a>
                                @endif
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            @if(request('search'))
                                <small class="text-muted">
                                    Showing {{ $genes->total() }} result(s) for "{{ request('search') }}"
                                </small>
                            @else
                                <small class="text-muted">
                                    Showing {{ $genes->total() }} total genes
                                </small>
                            @endif
                        </div>
                    </div>

                    @if($genes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Symbol</th>
                                        <th>Name</th>
                                        <th>Alliance ID</th>
                                        <th>Species</th>
                                        <th>Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($genes as $gene)
                                        <tr>
                                            <td>
                                                <strong>{{ $gene->symbol }}</strong>
                                                @if($gene->isMouse())
                                                    <span class="badge bg-primary">Mouse</span>
                                                @elseif($gene->isRat())
                                                    <span class="badge bg-success">Rat</span>
                                                @elseif($gene->isHuman())
                                                    <span class="badge bg-secondary">Human</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ Str::limit($gene->name ?? 'N/A', 50) }}</span>
                                            </td>
                                            <td>
                                                <code>{{ $gene->alliance_id }}</code>
                                            </td>
                                            <td>{{ $gene->species_name ?? 'N/A' }}</td>
                                            <td>
                                                <small class="text-muted">{{ $gene->updated_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.genes.show', $gene) }}" 
                                                       class="btn btn-outline-primary btn-sm" title="View Details">
                                                        View
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            {{ $genes->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            @if(request('search'))
                                <p class="text-muted">No genes found matching "{{ request('search') }}".</p>
                                <a href="{{ route('admin.genes.index') }}" class="btn btn-outline-primary">Clear search</a>
                            @else
                                <p class="text-muted">No genes found. Use the Test Forms tab to create some!</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Test Forms Tab -->
            <div class="tab-pane fade" id="test-forms" role="tabpanel" aria-labelledby="test-forms-tab">
                <div class="mt-4">
                    <div class="row">
                        <div class="col-lg-4 mb-4">
                            <!-- Test by Alliance ID -->
                            <div class="card" style="border-left: 4px solid var(--bs-primary);">
                                <div class="card-body">
                                    <div class="card-title">Test by Alliance ID</div>
                                    <p class="card-text small text-muted">Create or update a gene using its Alliance ID</p>
                                    <form action="{{ route('admin.genes.test-alliance-id') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="alliance_id" class="form-label">Alliance ID</label>
                                            <input type="text" class="form-control" id="alliance_id" name="alliance_id" 
                                                   placeholder="e.g., MGI:88057" value="{{ old('alliance_id') }}">
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">Test Alliance ID</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-4">
                            <!-- Test by Symbol and Species -->
                            <div class="card" style="border-left: 4px solid var(--bs-primary);">
                                <div class="card-body">
                                    <div class="card-title">Test by Symbol & Species</div>
                                    <p class="card-text small text-muted">Search for a gene by symbol and specific species</p>
                                    <form action="{{ route('admin.genes.test-symbol-species') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="symbol" class="form-label">Gene Symbol</label>
                                            <input type="text" class="form-control" id="symbol" name="symbol" 
                                                   placeholder="e.g., Brca1" value="{{ old('symbol') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="species" class="form-label">Species</label>
                                            <select class="form-select" id="species" name="species">
                                                <option value="MGI" {{ old('species') == 'MGI' ? 'selected' : '' }}>Mouse (MGI)</option>
                                                <option value="RGD" {{ old('species') == 'RGD' ? 'selected' : '' }}>Rat (RGD)</option>
                                                <option value="HGNC" {{ old('species') == 'HGNC' ? 'selected' : '' }}>Human (HGNC)</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-success w-100">Test Symbol & Species</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-4">
                            <!-- Test Mouse or Rat Gene -->
                            <div class="card" style="border-left: 4px solid var(--bs-primary);">
                                <div class="card-body">
                                    <div class="card-title">Test Mouse/Rat Gene</div>
                                    <p class="card-text small text-muted">Search for a mouse or rat gene by symbol (auto-detects MGI or RGD)</p>
                                    <form action="{{ route('admin.genes.test-mouse-rat') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="mouse_rat_symbol" class="form-label">Gene Symbol</label>
                                            <input type="text" class="form-control" id="mouse_rat_symbol" name="symbol" 
                                                   placeholder="e.g., Brca1" value="{{ old('symbol') }}">
                                        </div>
                                        <button type="submit" class="btn btn-warning w-100">Test Mouse/Rat Gene</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection