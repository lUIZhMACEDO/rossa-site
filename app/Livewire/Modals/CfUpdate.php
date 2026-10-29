<?php

namespace App\Livewire\Modals;

use App\Enums\AnalysisTypeEnum;
use App\Livewire\CustomModalComponent;
use App\Livewire\Forms\CoreFacilityFormObject;
use App\Models\CoreFacility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CfUpdate extends CustomModalComponent
{
    public CoreFacilityFormObject $form;

    public function mount(CoreFacility $facility)
    {
        // check if user is creator (or admin)
        if ($facility->user_id != Auth::id() && ! Gate::check('manage-facilities')) {
            return redirect()->back()->with('error', 'You are not authorized to update this core facility.');
        }

        $this->form->setFacility($facility);
        $this->form->availableAnalysisTypes = AnalysisTypeEnum::getStandardTypes();
    }

    public function render()
    {
        return view('livewire.modals.cf-update');
    }

    public function addOtherAnalysisType()
    {
        $this->form->addOtherAnalysisType();
    }

    public function removeOtherAnalysisType($index)
    {
        $this->form->removeOtherAnalysisType($index);
    }

    public function update()
    {
        // check if user is creator (or admin)
        if ($this->form->facility->user_id != Auth::id() && ! Gate::check('manage-facilities')) {
            return redirect()->back()->with('error', 'You are not authorized to update this core facility.');
        }

        // update
        $this->form->update();

        $this->closeModalWithEvents([
            'refreshTable',
        ]);

        $this->dispatch('livewire-alert', ['status' => 'success', 'message' => 'Core facility updated successfully.']);
    }
}
