<div class="d-flex gap-2 mb-3">
    <span class="d-flex align-items-center">Data type:</span>
    <button
        type="button"
        wire:click="setFilterType('all')"
        class="btn btn-sm allow-rounded rounded-pill px-3 {{ $filterType === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}"
    >
        All
    </button>
    <button
        type="button"
        wire:click="setFilterType('micro_ct')"
        class="btn btn-sm allow-rounded rounded-pill px-3 {{ $filterType === 'micro_ct' ? 'btn-primary' : 'btn-outline-secondary' }}"
    >
        µCT
    </button>
    <button
        type="button"
        wire:click="setFilterType('dexa')"
        class="btn btn-sm allow-rounded rounded-pill px-3 {{ $filterType === 'dexa' ? 'btn-primary' : 'btn-outline-secondary' }}"
    >
        DEXA
    </button>
    <button
        type="button"
        wire:click="setFilterType('bone_histo')"
        class="btn btn-sm allow-rounded rounded-pill px-3 {{ $filterType === 'bone_histo' ? 'btn-primary' : 'btn-outline-secondary' }}"
    >
        Histomorphometry
    </button>
    <button
        type="button"
        wire:click="setFilterType('mechanical_testing')"
        class="btn btn-sm allow-rounded rounded-pill px-3 {{ $filterType === 'mechanical_testing' ? 'btn-primary' : 'btn-outline-secondary' }}"
    >
        Mechanical testing
    </button>
    <span class="d-flex align-items-center" style="margin-left:10px;">Sex:</span>
    <button
        type="button"
        wire:click="setSexFilter('all')"
        class="btn btn-sm allow-rounded rounded-pill px-3 {{ $sexFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}"
    >
        All
    </button>
    <button
        type="button"
        wire:click="setSexFilter('M')"
        class="btn btn-sm allow-rounded rounded-pill px-3 {{ $sexFilter === 'M' ? 'btn-primary' : 'btn-outline-secondary' }}"
    >
        Male
    </button>
    <button
        type="button"
        wire:click="setSexFilter('F')"
        class="btn btn-sm allow-rounded rounded-pill px-3 {{ $sexFilter === 'F' ? 'btn-primary' : 'btn-outline-secondary' }}"
    >
        Female
    </button>
</div>
