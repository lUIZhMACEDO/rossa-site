<div>
    @php
        use App\Enums\CoreFacilityStatus;
    @endphp
    <div class="modal-header">
        <h5 class="modal-title">Confirm Status Update</h5>
        <button type="button" class="btn-close" wire:click="$dispatch('closeModal')" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        @if ($status === CoreFacilityStatus::PENDING)
            <p>Are you sure you want to mark "{{ $facility->facility_name }}" as <strong>pending</strong>?</p>
        @else
            <p>Are you sure you want to <strong>{{ $status->action() }}</strong> "{{ $facility->facility_name }}"?</p>
        @endif

        @if ($status === CoreFacilityStatus::REJECTED)
            <div>
                <div class="mt-3">
                    <label for="rejection_reason" class="form-label required">Reason for Rejection</label>
                    <select id="rejection_reason" wire:model.blur="reason" class="form-select @error('reason') is-invalid @enderror">
                        <option value="">Select a reason</option>
                        <option value="Invalid Information">Invalid Information</option>
                        <option value="other">Other</option>
                    </select>
                    @error('reason') <span class="invalid-feedback">{{ $message ?? '' }}</span> @enderror
                </div>

                <div x-data x-show="$wire.reason === 'other'" class="mt-3">
                    <label for="other_reason" class="form-label required">Please specify:</label>
                    <input id="other_reason" type="text" wire:model.blur="other_reason" class="form-control @error('other_reason') is-invalid @enderror" placeholder="Enter reason">
                    @error('other_reason') <span class="invalid-feedback">{{ $message ?? '' }}</span> @enderror
                </div>
            </div>
        @endif
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" wire:click="$dispatch('closeModal')">Cancel</button>
        <button type="button" class="btn btn-{{ $status->color() }}" wire:click="confirm">{{ ucfirst($status->action()) }}</button>
    </div>
</div>