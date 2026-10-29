<div class="molecule-search"
    x-data="{ 
        lastClickTarget: null,
        init() {
            $wire.on('checkClickTarget', () => {
                // check if click in input or results
                const clickedInsideInput = this.lastClickTarget && $refs.moleculeSearchInput.contains(this.lastClickTarget);
                const clickedInsideResults = this.lastClickTarget && $refs.moleculeSearchResults && $refs.moleculeSearchResults.contains(this.lastClickTarget);
                
                // if not inside, hide results
                if (!clickedInsideInput && !clickedInsideResults) {
                    $wire.handleClickOutside();
                }
            });
            
            // track global clicks
            document.addEventListener('mousedown', (e) => {
                this.lastClickTarget = e.target;
            });
        }
    }"
    x-ref="moleculeSearch">
    
    <input type="hidden" name="molecule_id" value="{{ $molecule ? $molecule->id : '' }}">
    <div class="position-relative">
        <div class="row">
            <div class="col-md-6">
                @if (!$cannotFindMolecule)
                    <input type="text"
                        class="form-control"
                        placeholder="Search for drugs..."
                        wire:model.live.debounce.300ms="query"
                        wire:focus="onFocus"
                        wire:blur="onBlur"
                        x-ref="moleculeSearchInput"
                        x-on:keydown.enter="$wire.isFocused = false; $refs.moleculeSearchInput.blur()"
                        x-on:keydown.tab="$wire.isFocused = false"
                        x-on:keydown.escape="$wire.isFocused = false; $refs.moleculeSearchInput.blur()">

                    {{-- search results - show if focused --}}
                    @if($isFocused && $results && count($results) > 0)
                        <div class="search-results position-absolute w-100 border rounded bg-white shadow-sm" 
                            style="z-index: 1000; max-height: 300px; overflow-y: auto; margin-top: 2px;"
                            x-ref="moleculeSearchResults">
                            @foreach($results as $mol)
                                <div class="search-result-item p-2 border-bottom"
                                    style="cursor: pointer;"
                                    wire:click="selectMolecule('{{ $mol['id'] }}')"
                                    onmouseover="this.style.backgroundColor='#f8f9fa'"
                                    onmouseout="this.style.backgroundColor='white'">

                                    <div class="fw-semibold">{{ $mol['pref_name'] ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $mol['chembl_id'] ?? 'N/A' }}</small>
                                    @if(isset($mol['molecule_type']))
                                        <small class="text-muted"> • {{ $mol['molecule_type'] }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    {{-- no results --}}
                    @elseif($isFocused && $query && count($results) === 0)
                        <div class="no-results position-absolute w-100 border rounded bg-white shadow-sm p-2" 
                            style="z-index: 1000; top: 100%; margin-top: 2px;"
                            x-ref="moleculeSearchResults">
                            <small class="text-muted">
                                No drugs found matching: <strong>"{{ $query }}"</strong>.
                            </small>
                        </div>
                    @endif
                @else
                    <div class="mt-3">
                        <label for="custom-molecule-input" class="form-label">Enter Drug Name:</label>
                        <div class="input-group">
                            <input type="text" 
                                class="form-control"
                                id="custom-molecule-input"
                                placeholder="Enter custom drug name"
                                wire:model.live="customMoleculeName">
                            <button class="btn btn-primary" 
                                type="button"
                                wire:click="selectCustomMolecule"
                                @if(!$customMoleculeName || trim($customMoleculeName) === '') disabled @endif>
                                Add Custom Drug
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle"></i> 
                            Please provide the drug name or identifier in your submission.
                        </small>
                    </div>
                @endif
            </div>

            <div class="col-md-6">
                <div class="form-check mt-2">
                    <input type="checkbox" 
                        class="form-check-input" 
                        id="cannot-find-molecule" 
                        wire:model.live="cannotFindMolecule">
                    <label class="form-check-label" for="cannot-find-molecule">
                        I cannot find my drug in ChEMBL
                    </label>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-body small">
                        @if ( $molecule )
                            <p class="text-muted mb-1">
                                <i class="bi bi-check-circle text-success"></i> 
                                Selected: <strong>{{ $moleculeName }}</strong>
                            </p>
                            @if($molecule->is_custom)
                                <p class="mb-1"><span class="badge text-bg-primarydark">Custom Drug</span></p>
                            @else
                                <p class="mb-1">ChEMBL ID: {{ $molecule->chembl_id }}</p>
                                @if($molecule->molecule_type)
                                    <p class="mb-1">Type: {{ $molecule->molecule_type }}</p>
                                @endif
                                @if($molecule->formula)
                                    <p class="mb-1">Formula: {{ $molecule->formula }}</p>
                                @endif
                                @if($molecule->max_phase !== null)
                                    <p class="mb-1">Max Phase: {{ $molecule->max_phase }}</p>
                                @endif
                            @endif
                        @else
                            <small class="text-muted">
                                <i class="bi bi-x-circle text-danger"></i> 
                                No drug selected
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
