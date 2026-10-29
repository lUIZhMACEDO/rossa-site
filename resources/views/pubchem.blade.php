@extends('layout')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">PubChem Compound Search</h2>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('pubchem.search') }}" method="GET" class="mb-4">
        <div class="mb-3">
            <label for="query" class="form-label">Enter Chemical Name:</label>
            <input type="text" name="query" id="query" class="form-control" value="{{ request('query') }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    @if(!empty($query))
        <h4>Search results for "{{ $query }}"</h4>
    @endif

    @if(!empty($compounds))
        <ul class="list-group mt-3">
            @foreach($compounds as $compound)
                <li class="list-group-item">
                    <a href="{{ route('pubchem.show', ['cid' => $compound['cid']]) }}">
                        {{ $compound['name'] }} (CID: {{ $compound['cid'] }})
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    @isset($data)
        <div class="card p-4 mt-4">
            <h4>Results for: {{ $input_name }}</h4>

            <table class="table table-bordered mt-3">
                <tbody>
                    <tr><th scope="row">Primary Name</th><td>{{ $data['primary_name'] }}</td></tr>
                    <tr><th scope="row">IUPAC Name</th><td>{{ $data['iupac_name'] ?? 'N/A' }}</td></tr>
                    <tr><th scope="row">Molecular Formula</th><td>{{ $data['molecular_formula'] ?? 'N/A' }}</td></tr>
                    <tr><th scope="row">Molecular Weight</th><td>{{ $data['molecular_weight'] ?? 'N/A' }}</td></tr>

                    <tr>
                        <th scope="row">Synonyms</th>
                        <td>
                            @if(!empty($data['synonyms']))
                                {{ implode(', ', array_slice($data['synonyms'], 0, 15)) }}
                                @if(count($data['synonyms']) > 15)
                                    <br><small>Showing first 15 of {{ count($data['synonyms']) }} synonyms.</small>
                                @endif
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Descriptions</th>
                        <td>
                            @if(!empty($data['description_sources']))
                                @foreach($data['description_sources'] as $source => $desc)
                                    <strong>{{ $source }}</strong>
                                    <p>{{ $desc }}</p>
                                @endforeach
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Cross References</th>
                        <td>
                            <ul>
                                @foreach($data['cross_references'] as $label => $ref)
                                    <li>
                                        <strong>{{ $label }}:</strong>
                                        @if($ref && isset($ref['id']))
                                            @if(isset($ref['url']))
                                                <a href="{{ $ref['url'] }}" target="_blank">{{ $ref['id'] }}</a>
                                            @else
                                                {{ $ref['id'] }}
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endisset
</div>
@endsection
