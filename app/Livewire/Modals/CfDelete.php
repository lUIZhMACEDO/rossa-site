<?php

namespace App\Livewire\Modals;

use App\Livewire\CustomModalComponent;
use App\Models\CoreFacility;
use Illuminate\Support\Facades\Gate;

class CfDelete extends CustomModalComponent
{
    public int $facilityId;

    public string $facilityName;

    // set modal max width - override config
    public static function modalMaxWidth(): string
    {
        return 'md';
    }

    public function mount(CoreFacility $facility)
    {
        Gate::authorize('manage-facilities');
        $this->facilityId = $facility->id;
        $this->facilityName = $facility->facility_name;
    }

    public function render()
    {
        return view('livewire.modals.cf-delete');
    }

    public function confirm()
    {
        // Find the model to authorize against before dispatching the delete event.
        $facility = CoreFacility::findOrFail($this->facilityId);
        Gate::authorize('manage-facilities');

        // dispatch event to table to delete facility
        $this->dispatch('cf-delete-confirmed', [
            'facilityId' => $this->facilityId,
        ]);
        $this->closeModal();
    }
}
