@extends('layout')

@section('content')
<div class="container">
    <h1>ChEMBL Compound Details</h1>
<hr>
    @php 
    
        $m = $details['molecule'] ?? []; 
        $targets = $molecule['targets'] ?? [];
        $pathways = $molecule['pathways'] ?? [];
        $xrefs = $molecule['cross_refs'] ?? [];
               
    @endphp
    
    <h2>{{ $m['pref_name'] ?? 'Unnamed Molecule' }} ({{ $m['molecule_chembl_id'] ?? 'N/A' }})</h2>
    <p><strong>Formula:</strong> {{ $m['molecule_properties']['full_molformula'] ?? 'N/A' }}</p>
    <p><strong>Molecule Type:</strong> {{ $m['molecule_type'] ?? 'N/A' }}</p>
    <p><strong>Clinical Development Phase:</strong> {{ $m['max_phase'] ?? 'N/A' }} ({{ $m['max_phase_label'] ?? 'Unknown' }})</p>

    @if(count($targets) > 0)
        <h3>Targets</h3>
        <ul>
            @foreach($targets as $target)
                <li>{{ $target['pref_name'] ?? 'Unnamed Target' }} ({{ $target['target_chembl_id'] ?? 'N/A' }})</li>
            @endforeach
        </ul>
    @endif

    @if(count($pathways) > 0)
        <h3>Pathways</h3>
        <ul>
            @foreach($pathways as $pathway)
                <li>{{ $pathway['name'] ?? 'Unnamed Pathway' }}</li>
            @endforeach
        </ul>
    @endif

    
    @if (!empty($mechanisms))
    <hr>
    <h3>Mechanism of Action</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Mechanism</th>
                <th>Target Name</th>
                <th>Target Type</th>
                <th>Target ChEMBL ID</th>
                <th>Action Type</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mechanisms as $mech)
            <tr>
                <td>{{ $mech['mechanism_of_action'] ?? 'N/A' }}</td>
                <td>{{ $mech['target_pref_name'] ?? 'Unnamed' }}</td>
                <td>{{ $mech['target_type'] ?? 'N/A' }}</td>
                <td>{{ $mech['target_chembl_id'] ?? 'N/A' }}</td>
                <td>{{ $mech['action_type'] ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    <hr>
   <ul>
    @foreach($crossReferences as $xref)
        <li>
            <strong>{{ $xref['database'] }}:</strong>
            <a href="{{ $xref['url'] }}" target="_blank">{{ $xref['compound_id'] }}</a>
        </li>
    @endforeach
    </ul>



    <a href="{{ route('chembl.search') }}" class="btn btn-secondary mt-3">Back to Search</a>
</div>
@endsection


