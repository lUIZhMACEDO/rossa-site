@extends('layout')

@section('content')
<div class="container">
    <h1>Search ChEMBL Molecules</h1>

    <form method="POST" action="{{ route('chembl.search') }}">
        @csrf
        <div class="form-group">
            <input type="text" name="query" class="form-control" value="{{ old('query', $query ?? '') }}" placeholder="Enter drug name, e.g., Advil">
        </div>
        <button type="submit" class="btn btn-primary mt-2">Search</button>
    </form>

    @if(!empty($molecules))
        <h2 class="mt-4">Results</h2>
        <ul class="list-group">
            @foreach($molecules as $molecule)
                <li class="list-group-item">
                    <strong>{{ $molecule['pref_name'] ?? 'N/A' }}</strong> —
                    {{ $molecule['molecule_chembl_id'] }}
                    <a href="{{ route('chembl.details', $molecule['molecule_chembl_id']) }}" class="btn btn-sm btn-info float-end">Details</a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
