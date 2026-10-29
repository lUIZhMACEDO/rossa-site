<div>
    <div class="modal-header">
        <h5 class="modal-title">Update Core Facility</h5>
        <button type="button" class="btn-close" wire:click="$dispatch('closeModal')" aria-label="Close"></button>
    </div>
    <form wire:submit.prevent="update">
        <div class="modal-body">
            @include('livewire.partials.cf-fields')
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" wire:click="$dispatch('closeModal')">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
