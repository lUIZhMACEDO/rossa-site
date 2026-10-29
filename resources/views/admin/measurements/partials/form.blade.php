{{-- 
    Form partial for creating/editing measurements
    Variables expected:
    - $measurement (optional) - the measurement being edited
    - $analysisTypes - collection of analysis types
    - $isEdit (boolean) - whether this is an edit form
--}}

@php
    $isEdit = $isEdit ?? false;
    $measurement = $measurement ?? null;
@endphp

<div class="card">
    <div class="card-body">
        @if(!$isEdit)
        {{-- Name field - only shown when creating --}}
        <div class="mb-3">
            <label for="name" class="form-label">Name (System Identifier) <span class="text-danger">*</span></label>
            <input 
                type="text" 
                class="form-control @error('name') is-invalid @enderror" 
                id="name" 
                name="name" 
                value="{{ old('name', $measurement?->name) }}"
                required
                placeholder="e.g., bone_mineral_density"
            >
            <div class="form-text">
                Use lowercase letters, numbers, and underscores only. This will be used as the internal identifier.
            </div>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Analysis Type - only shown when creating --}}
        <div class="mb-3">
            <label for="analysis_type_id" class="form-label">Analysis Type</label>
            <select 
                class="form-select @error('analysis_type_id') is-invalid @enderror" 
                id="analysis_type_id" 
                name="analysis_type_id"
            >
                <option value="">Universal (All Analysis Types)</option>
                @foreach($analysisTypes as $analysisType)
                    <option 
                        value="{{ $analysisType->id }}" 
                        @selected(old('analysis_type_id', $measurement?->analysis_type_id) == $analysisType->id)
                    >
                        {{ $analysisType->name }}
                    </option>
                @endforeach
            </select>
            <div class="form-text">
                Select an analysis type if this measurement is specific to one, or leave as Universal.
            </div>
            @error('analysis_type_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @else
        {{-- Display read-only info for edit mode --}}
        <div class="mb-3">
            <label class="form-label">Name (System Identifier)</label>
            <input type="text" class="form-control" value="{{ $measurement->name }}" disabled>
            <div class="form-text">The system identifier cannot be changed.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Analysis Type</label>
            <input 
                type="text" 
                class="form-control" 
                value="{{ $measurement->analysisType?->name ?? 'Universal (All Analysis Types)' }}" 
                disabled
            >
            <div class="form-text">The analysis type cannot be changed.</div>
        </div>
        @endif

        {{-- Display Name --}}
        <div class="mb-3">
            <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
            <input 
                type="text" 
                class="form-control @error('display_name') is-invalid @enderror" 
                id="display_name" 
                name="display_name" 
                value="{{ old('display_name', $measurement?->display_name) }}"
                required
                placeholder="e.g., Bone Mineral Density"
            >
            <div class="form-text">
                The human-readable name that will be displayed to users.
            </div>
            @error('display_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Unit --}}
        <div class="mb-3">
            <label for="unit" class="form-label">Unit</label>
            <input 
                type="text" 
                class="form-control @error('unit') is-invalid @enderror" 
                id="unit" 
                name="unit" 
                value="{{ old('unit', $measurement?->unit) }}"
                placeholder="e.g., g/cm^2, mm, N"
            >
            <div class="form-text">
                The unit of measurement (leave blank if not applicable).
            </div>
            @error('unit')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Synonyms --}}
        <div class="mb-3">
            <label for="synonyms" class="form-label">Synonyms</label>
            <textarea 
                class="form-control @error('synonyms') is-invalid @enderror" 
                id="synonyms" 
                name="synonyms" 
                rows="4"
                placeholder="Enter one synonym per line&#10;BMD&#10;BMD (g/cm^2)&#10;Bone Mineral Density"
            >{{ old('synonyms', is_array($measurement?->synonyms) ? implode("\n", $measurement->synonyms) : '') }}</textarea>
            <div class="form-text">
                Enter alternative names or abbreviations for this measurement, one per line. These help with automatic mapping during data upload.
            </div>
            @error('synonyms')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Graph Exclude (optional) --}}
        <div class="mb-3">
            <div class="form-check">
                <input 
                    type="checkbox" 
                    class="form-check-input @error('graph_exclude') is-invalid @enderror" 
                    id="graph_exclude" 
                    name="graph_exclude" 
                    value="1"
                    @checked(old('graph_exclude', $measurement?->graph_exclude ?? false))
                >
                <label class="form-check-label" for="graph_exclude">
                    Exclude from Graphs
                </label>
                <div class="form-text">
                    Check this if this measurement should not be displayed in graphs (e.g., identifier fields).
                </div>
            </div>
            @error('graph_exclude')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
