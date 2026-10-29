<div class="animal-line-lookup" 
    x-data="{ 
        lastClickTarget: null,
        init() {
            $wire.on('checkClickTarget', () => {
                // check if click in input or results
                const clickedInsideInput = this.lastClickTarget && $refs.inputGroup.contains(this.lastClickTarget);
                const clickedInsideResults = this.lastClickTarget && $refs.searchResults && $refs.searchResults.contains(this.lastClickTarget);
                 
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
    x-ref="animalLineLookup">
    

    @if ( $this->selectedAnimalLine )
        <input type="hidden" name="{{ $formField }}" value="{{ $this->selectedAnimalLine->id }}">
    @endif

    {{-- @dump($results->pluck('species')->toArray()) --}}
    <div class="position-relative">
        <div class="input-group" x-ref="inputGroup">
            <input type="text"
                    class="form-select {{ $hasError ? 'is-invalid' : '' }}"
                    name="{{ $textFieldName }}"
                    placeholder="Type to search existing animal strains or enter custom name..."
                    wire:model.live.debounce.300ms="query"
                    wire:focus="onFocus"
                    wire:blur="onBlur"
                    x-ref="animalLineInput"
                    x-on:keydown.enter="$wire.isFocused = false; $refs.animalLineInput.blur()"
                    x-on:keydown.tab="$wire.isFocused = false"
                    x-on:keydown.escape="$wire.isFocused = false; $refs.animalLineInput.blur()">
            @if($animalLineId || $query)
                <button type="button" 
                        class="btn btn-outline-danger" 
                        wire:click="clearSelection"
                        title="Clear selection">
                    <i class="bi bi-x"></i>
                </button>
            @endif
        </div>

        {{-- indicator for selection/custom --}}
        @if(!$animalLineId && $query)
            <small class="text-muted">
                <i class="bi bi-plus-circle text-info"></i> 
                Custom strain: "{{ $query }}" will be created when you save
            </small>
        @elseif($animalLineId)
        @endif

        {{-- search results - show if focused --}}
        @if($isFocused && $results->count() > 0)
            <div class="search-results position-absolute w-100 border rounded bg-white shadow-sm" 
                style="z-index: 1000; max-height: 300px; overflow-y: auto; top: 100%; margin-top: 2px;"
                x-ref="searchResults">
                @foreach($results as $result)
                    <div class="search-result-item p-2 border-bottom"
                        style="cursor: pointer;"
                        wire:click="selectAnimalLine({{ $result->id }})"
                        onmouseover="this.style.backgroundColor='#f8f9fa'"
                        onmouseout="this.style.backgroundColor='white'">

                        <div class="fw-semibold">{{ $result->label }} <span class="badge text-bg-secondary">{{ Str::headline($result->species) }}</span></div>
                        @if($result->iri)
                            <small class="text-muted">{{ $result->iri }}</small>
                        @endif
                    </div>
                @endforeach
            </div>
        {{-- no results --}}
        @elseif($isFocused && $query && $results->count() === 0)
            <div class="no-results position-absolute w-100 border rounded bg-white shadow-sm p-2" 
                style="z-index: 1000; top: 100%; margin-top: 2px;"
                x-ref="searchResults">
                <small class="text-muted">
                    No existing strains found for "{{ $query }}". 
                    <strong>Continue typing to create a custom strain.</strong>
                </small>
            </div>
        @endif
    </div>


    <!-- Strain Preview -->
    <div class="card mt-2">
        <div class="card-body small">
            @if ( $this->selectedAnimalLine )
                <p class="text-muted mb-1">
                    <i class="bi bi-check-circle text-success"></i>
                    Selected: <strong>{{ $this->selectedAnimalLine->label }}</strong>  ({{ Str::headline($this->selectedAnimalLine->species) }})
                </p>
                @if($this->selectedAnimalLine->iri)
                    <p class="mb-1">IRI: {{ $this->selectedAnimalLine->iri }}</p>
                @endif
                @if($this->selectedAnimalLine->description)
                    <p class="mb-1">{{ $this->selectedAnimalLine->description }}</p>
                @endif
            @else
                <small class="text-muted">
                    <i class="bi bi-x-circle text-danger"></i>
                    No animal strain selected
                </small>
            @endif
        </div>
    </div>
</div>
