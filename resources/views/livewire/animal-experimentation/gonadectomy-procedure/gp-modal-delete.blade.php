<div>
    <div class="modal-header text-white" style="background-color: #0D3B52;">
        <h5 class="modal-title mb-0">Confirm Procedure Deletion</h5>
        <button type="button" class="btn-close btn-close-white" wire:click="$dispatch('closeModal')" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <p>Are you sure you want to <strong>DELETE</strong> the following procedure?</p>
        <ul class="ms-3">
            <li><strong>Type of Surgery:</strong> {{ $procedure->type }}</li>
            <li><strong>Age During Surgery (Weeks):</strong> {{ $procedure->age }}</li>
            @if ($procedure->description)
                <li><strong>Description:</strong> {{ $procedure->description }}</li>
            @endif
        </ul>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" wire:click="$dispatch('closeModal')">Cancel</button>
        <button type="button" class="btn btn-danger" wire:click="deleteProcedure">DELETE</button>
    </div>
</div>