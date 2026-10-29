@extends('layout')

@section('content')
<div class="py-5">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gene Details: {{ $gene->symbol }}</h1>
            <div>
                <a href="{{ route('admin.genes.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="detail-card mb-4">
                    <h3>Basic Information</h3>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Symbol:</strong></td>
                            <td>{{ $gene->symbol }}</td>
                        </tr>
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $gene->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Alliance ID:</strong></td>
                            <td>
                                {{ $gene->alliance_id }}
                                @if($gene->isMouse())
                                    <span class="badge bg-primary">Mouse</span>
                                @elseif($gene->isRat())
                                    <span class="badge bg-success">Rat</span>
                                @elseif($gene->isHuman())
                                    <span class="badge bg-secondary">Human</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Species:</strong></td>
                            <td>{{ $gene->species_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Species Taxon ID:</strong></td>
                            <td>{{ $gene->species_taxonId ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Biotype:</strong></td>
                            <td>{{ $gene->biotype ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $gene->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Updated:</strong></td>
                            <td>{{ $gene->updated_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Gene Synopsis -->
                @if($gene->geneSynopsis)
                    <div class="detail-card mb-4">
                        <h3>Gene Synopsis</h3>
                        <div class="p-3 bg-light rounded">
                            {!! $gene->geneSynopsis !!}
                        </div>
                    </div>
                @endif

                <!-- Synonyms -->
                @if(count($gene->getSynonyms()) > 0)
                    <div class="detail-card mb-4">
                        <h3>Synonyms</h3>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($gene->getSynonyms() as $synonym)
                                <span class="badge bg-secondary">{{ $synonym }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Genome Locations -->
                @if(count($gene->getGenomeLocations()) > 0)
                    <div class="detail-card mb-4">
                        <h3>Genome Locations</h3>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Chromosome</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Strand</th>
                                        <th>Assembly</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gene->getGenomeLocations() as $location)
                                        <tr>
                                            <td>{{ $location['chromosome'] ?? 'N/A' }}</td>
                                            <td>{{ isset($location['start']) ? number_format($location['start']) : 'N/A' }}</td>
                                            <td>{{ isset($location['end']) ? number_format($location['end']) : 'N/A' }}</td>
                                            <td>{{ $location['strand'] ?? 'N/A' }}</td>
                                            <td>{{ $location['assembly'] ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <!-- Actions -->
                <div class="detail-card">
                    <h3>Actions</h3>
                    <div class="d-grid gap-2 mb-4">
                        <form action="{{ route('admin.genes.test-refresh', $gene) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                🔄 Refresh from Alliance API
                            </button>
                        </form>
                        
                        <a href="{{ $gene->getAllianceUrl() }}" target="_blank" class="btn btn-outline-secondary">
                            🔗 View on Alliance Genome
                        </a>
                    </div>
                </div>

                <!-- Raw Data -->
                @if($gene->data)
                    <div class="detail-card">
                        <h3>Raw Alliance Data</h3>
                        <div class="json-container">
                            <pre><code>{{ json_encode($gene->data, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection