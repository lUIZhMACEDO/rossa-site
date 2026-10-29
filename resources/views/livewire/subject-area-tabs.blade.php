<div @if($study->isLockedForEditing(auth()->user())) data-input-locked @endif>
    <ul class="nav nav-tabs cg-tabs mb-4" role="tablist" data-input-unlock>
        @foreach($TABS as $tabId => $tabName)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === $tabId ? 'active' : '' }}" 
                        wire:click="setActiveTab('{{ $tabId }}')" 
                        type="button" 
                        >
                {{ $tabName }}
                </button>
            </li>
        @endforeach
    </ul>

    <div>
        @php
    $subtitles = [
        'thematic-tab' => 'Select thematic areas appropriate for your study.',
        'tissue-tab' => 'Select tissue interactions appropriate for your study.',
        'cell-tab' => 'Select cell types appropriate for your study.',
        'disease-tab' => 'Select disease terms appropriate for your study.',
    ];
    @endphp
        <h4 class="mb-1">{{ $TABS[$activeTab] }}</h4>
        <p class="mb-3 fw-semibold" style="color: #1A7DB5;">{{ $subtitles[$activeTab] ?? '' }}</p>
        @switch ($activeTab)
            @case('thematic-tab')
            @case('tissue-tab')
                @php
                    $tags = \App\Models\Tag::where('tab', $TABS[$activeTab])
                        ->whereNull('cell_id')
                        ->whereNull('study_id')
                        ->orderBy('name')
                        ->get();
                    $customTags = \App\Models\Tag::where('tab', $TABS[$activeTab])
                        ->where('study_id', $study->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
                @endphp
                <div class="row">
                    @foreach($tags as $tag)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input wire:model="tags" class="form-check-input" type="checkbox" 
                                        name="tags[]" value="{{ $tag->id }}" id="tag_{{ $tag->id }}">
                                <label class="form-check-label" for="tag_{{ $tag->id }}">{{ $tag->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    <hr>
                    <h5 class="mb-3">Custom {{ $TABS_SINGULAR[$activeTab] }}</h5>
                    <div class="mb-3">
                        <label for="otherTagInput" class="form-label">Add Custom {{ $TABS_SINGULAR[$activeTab] }}:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="otherTagInput" 
                                   wire:model="otherTagInput" 
                                   placeholder="Enter custom {{ $TABS_SINGULAR[$activeTab] }} name"
                                   wire:keydown.enter="addOtherTag('{{ $TABS[$activeTab] }}')">
                            <button class="btn btn-outline-secondary" type="button" wire:click="addOtherTag('{{ $TABS[$activeTab] }}')">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                        @error('otherTagInput') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    @if($customTags->count() > 0)
                        <div class="row">
                            @foreach($customTags as $tag)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input wire:model="tags" class="form-check-input" type="checkbox" 
                                                name="tags[]" value="{{ $tag->id }}" id="tag_{{ $tag->id }}">
                                        <label class="form-check-label" for="tag_{{ $tag->id }}">{{ $tag->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No custom {{ $TABS_SINGULAR[$activeTab] }} added yet. Use the input above to add one.</p>
                    @endif
                </div>
                @break
            @case('cell-tab')
                @php
                    $cellTypeTags = \App\Models\Tag::where('tab', 'Cell Types')
                        ->whereNull('study_id')
                        ->with('cell')
                        ->orderBy('name')
                        ->get()
                        ->groupBy('cell.group')
                        ->sortKeys();
                    $customTags = \App\Models\Tag::where('tab', 'Cell Types')
                        ->where('study_id', $study->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
                @endphp
                <div class="row">
                    <div class="col-md-6">
                        @foreach($cellTypeTags as $group => $tags)
                            <div class="mb-4">
                                <h5 class="text-muted mb-2">{{ $group }}</h5>
                                @foreach($tags as $tag)
                                    <div class="form-check">
                                        <input wire:model="tags" class="form-check-input" type="checkbox" 
                                                name="tags[]" value="{{ $tag->id }}" id="tag_{{ $tag->id }}"
                                                >
                                        <label class="form-check-label" for="tag_{{ $tag->id }}">{{ $tag->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                            @if($loop->iteration == ceil($cellTypeTags->count() / 2))
                                </div>
                                <div class="col-md-6">
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    <hr>
                    <h5 class="mb-3">Custom {{ $TABS_SINGULAR[$activeTab] }}</h5>
                    <div class="mb-3">
                        <label for="otherTagInput" class="form-label">Add custom {{ $TABS_SINGULAR[$activeTab] }}:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="otherTagInput" 
                                   wire:model="otherTagInput" 
                                   placeholder="Enter custom {{ $TABS_SINGULAR[$activeTab] }} name"
                                   wire:keydown.enter="addOtherTag('Cell Types')">
                            <button class="btn btn-outline-secondary" type="button" wire:click="addOtherTag('Cell Types')">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                        @error('otherTagInput') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    @if($customTags->count() > 0)
                        <div class="row">
                            @foreach($customTags as $tag)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input wire:model="tags" class="form-check-input" type="checkbox" 
                                                name="tags[]" value="{{ $tag->id }}" id="tag_{{ $tag->id }}">
                                        <label class="form-check-label" for="tag_{{ $tag->id }}">{{ $tag->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No custom tags added yet. Use the input above to add one.</p>
                    @endif
                </div>
                @break
            @case('disease-tab')
                <livewire:disease-search form="subject-areas-form" study-id="{{ $study->id }}" />
                @break
        @endswitch
    </div>

    <div class="mt-4 d-flex justify-content-between gap-2">
        <div></div>
        <button class="btn btn-primary" wire:click='save'>Save & Continue <i class="bi-arrow-right"></i></button>
    </div>
</div>