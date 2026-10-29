<!DOCTYPE html>
<html>
<head>
    <title>Gene Lookup</title>
</head>
<body>
    <h1>Search for a Gene</h1>

    @if ($errors->any())
        <div style="color: red;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ url('/gene-search') }}">
        @csrf
        <label for="symbol">Gene Symbol:</label>
        <input type="text" name="symbol" id="symbol" value="{{ old('symbol') }}" required>

        <label for="species">Species:</label>
        <select name="species" id="species">
            <option value="MGI" {{ old('species') == 'MGI' ? 'selected' : '' }}>Mouse</option>
            <option value="RGD" {{ old('species') == 'RGD' ? 'selected' : '' }}>Rat</option>
            <option value="HGNC" {{ old('species') == 'HGNC' ? 'selected' : '' }}>Human</option>
        </select>

        <button type="submit">Search</button>
    </form>

    @if ($geneDetails)
    <h2>Gene Details</h2>
    <ul>
        <li><strong>Symbol:</strong> {{ $geneDetails['symbol'] ?? 'N/A' }}</li>
        <li><strong>Name:</strong> {{ $geneDetails['name'] ?? 'N/A' }}</li>
        <li><strong>Species:</strong> {{ $geneDetails['species']['name'] ?? 'N/A' }}</li>
        <li><strong>Synonyms:</strong> {{ implode(', ', $geneDetails['synonyms'] ?? []) }}</li>

        {{-- ✅ Added Gene Synopsis --}}
        <li><strong>Gene Synopsis:</strong> {!! $geneDetails['geneSynopsis'] ?? 'N/A' !!}</li>

        {{-- ✅ Added Genome Locations --}}
        @if (!empty($geneDetails['genomeLocations']))
            <li><strong>Genome Locations:</strong>
                <ul>
                    @foreach ($geneDetails['genomeLocations'] as $location)
                        <li>
                            Chromosome: {{ $location['chromosome'] ?? 'N/A' }},
                            Start: {{ number_format($location['start'] ?? 0) }},
                            End: {{ number_format($location['end'] ?? 0) }},
                            Assembly: {{ $location['assembly'] ?? 'N/A' }},
                            Strand: {{ $location['strand'] ?? 'N/A' }}
                        </li>
                    @endforeach
                </ul>
            </li>
        @endif

        <li><strong>Alliance Page:</strong>
            <a href="https://www.alliancegenome.org/gene/{{ urlencode($geneDetails['id']) }}" target="_blank">
                View on Alliance
            </a>
        </li>
    </ul>

    <h3>Raw Data</h3>
    <pre>{{ json_encode($geneDetails, JSON_PRETTY_PRINT) }}</pre>
    @endif

</body>
</html>
