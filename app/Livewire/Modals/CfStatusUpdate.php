<?php

namespace App\Livewire\Modals;

use App\Enums\CoreFacilityStatus;
use App\Livewire\CustomModalComponent;
use App\Models\CoreFacility;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Rule;

class CfStatusUpdate extends CustomModalComponent
{
    public CoreFacility $facility;

    public CoreFacilityStatus $status;

    #[Rule('required|string', as: 'reason', message: [
        'required' => 'Please select a reason for rejection.',
        'string' => 'The reason must be a valid string.',
    ])]
    public $reason = '';

    #[Rule('required_if:reason,other|string|max:255', as: 'other_reason', message: [
        'required_if' => 'Please specify the other reason.',
        'string' => 'The other reason must be a valid string.',
        'max' => 'The other reason cannot be longer than 255 characters.',
    ])]
    public $other_reason = '';

    // set modal max width - override config
    public static function modalMaxWidth(): string
    {
        return 'md';
    }

    public function mount(CoreFacility $facility, CoreFacilityStatus $status)
    {
        Gate::authorize('manage-facilities');

        $this->facility = $facility;
        $this->status = $status;
    }

    public function render()
    {
        return view('livewire.modals.cf-status-update');
    }

    public function confirm()
    {
        Gate::authorize('manage-facilities');

        if ($this->status === CoreFacilityStatus::REJECTED) {
            $this->validate();
        }

        $rejectionReason = $this->reason === 'other' ? $this->other_reason : $this->reason;

        // dispatch event to table to update status
        $this->dispatch('cf-status-update-confirmed', [
            'facilityId' => $this->facility->id,
            'status' => $this->status->value,
            'reason' => $rejectionReason,
        ]);
        $this->closeModal();
    }
}
