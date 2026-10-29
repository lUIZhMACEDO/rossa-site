<div class="gene-search"
    x-data="{ 
        lastClickTarget: null,
        init() {
            $wire.on('checkClickTarget', () => {
                // check if click in input or results
                const clickedInsideInput = this.lastClickTarget && $refs.geneSearchInput.contains(this.lastClickTarget);
                const clickedInsideResults = this.lastClickTarget && $refs.geneSearchResults && $refs.geneSearchResults.contains(this.lastClickTarget);
                
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
    x-ref="geneSearch">
    
    {{-- <label class="form-label">Gene Target</label> --}}
    @if ( $this->geneId )
        <input type="hidden" name="{{ $formField }}" value="{{ $this->geneId }}">
    @endif

            <div class="position-relative">
                <div class="input-group">
                    @if (! $lockSpecies)
                        <select wire:model.live="species" class="form-select border rounded px-4 py-2">
                            <option value="">Select Species</option>
                            <option value="HGNC">Human</option>
                            <option value="MGI">Mouse</option>
                            <option value="RGD">Rat</option>
                        </select>
                    @endif
                    <input type="text"
                        class="form-control"
                        placeholder="{{ $lockSpecies && !$species ? 'Select a species first...' : 'Search for genes...' }}"
                        @disabled($lockSpecies && !$species)
                        wire:model.live.debounce.300ms="query"
                        wire:focus="onFocus"
                        wire:blur="onBlur"
                        x-ref="geneSearchInput"
                        x-on:keydown.enter="$wire.isFocused = false; $refs.geneSearchInput.blur()"
                        x-on:keydown.tab="$wire.isFocused = false"
                        x-on:keydown.escape="$wire.isFocused = false; $refs.geneSearchInput.blur()">
                </div>

                {{-- search results - show if focused --}}
                @if($isFocused && $results && count($results) > 0)
                    <div class="search-results position-absolute w-100 border rounded bg-white shadow-sm" 
                        style="z-index: 1000; max-height: 300px; overflow-y: auto; margin-top: 2px;"
                        x-ref="geneSearchResults">
                        @foreach($results as $g)
                            <div class="search-result-item p-2 border-bottom"
                                style="cursor: pointer;"
                                wire:click="selectGene('{{ $g['alliance_id'] }}')"
                                onmouseover="this.style.backgroundColor='#f8f9fa'"
                                onmouseout="this.style.backgroundColor='white'">

                                <div class="fw-semibold">{{ $g['symbol'] }}</div>

                                <small class="text-muted">{{ $g['alliance_id'] }}</small>
                                @if ($g['name'] !== $g['symbol'])
                                    | <small class="text-muted">{{ $g['name'] }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                {{-- no results --}}
                @elseif($isFocused && $query && count($results) === 0)
                    <div class="no-results position-absolute w-100 border rounded bg-white shadow-sm p-2" 
                        style="z-index: 1000; top: 100%; margin-top: 2px;"
                        x-ref="geneSearchResults">
                        <small class="text-muted">
                            No genes found matching: <strong>"{{ $query }}"</strong>.
                        </small>
                    </div>
                @endif

                <div class="card mt-3">
                    <div class="card-body small">
                        @if ( $gene )
                            <p class="text-muted mb-1">
                                <i class="bi bi-check-circle text-success"></i>
                                Selected: <strong>{{ $geneSymbol }}</strong>  ({{ $displayGeneSpecies }})
                            </p>
                            <p class="mb-1">Alliance: {{ $gene->alliance_id }}</p>
                            <p class="mb-1">{{ $gene->name }}</p>
                            <p class="mb-1">{{ $gene->biotype }}</p>
                        @else
                            <small class="text-muted">
                                <i class="bi bi-x-circle text-danger"></i>
                                No gene selected
                            </small>
                        @endif
                    </div>
                </div>
            </div>            

</div>
