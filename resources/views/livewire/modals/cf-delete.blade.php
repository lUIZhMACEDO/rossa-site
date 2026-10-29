<div>
    <div class="modal-header">
        <h5 class="modal-title mb-3">Confirm Status Update</h5>
        <button type="button" class="btn-close" wire:click="$dispatch('closeModal')" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <p>Are you sure you want to <strong>DELETE</strong> "{{ $facilityName }}"? This action cannot be undone.</p>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" wire:click="$dispatch('closeModal')">Cancel</button>
        <button type="button" class="btn btn-danger" wire:click="confirm">DELETE</button>
    </div>
</div>