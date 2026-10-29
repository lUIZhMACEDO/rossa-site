<?php

namespace App\Livewire;

use App\Enums\AnalysisTypeEnum;
use App\Livewire\Forms\CoreFacilityFormObject;
use Livewire\Component;

class CfCreate extends Component
{
    public CoreFacilityFormObject $form;

    public function mount()
    {
        // check if user is logged in
        if (! auth()->check()) {
            return redirect()->back()->with('error', 'You must be logged in to register a core facility.');
        }
        $this->form->availableAnalysisTypes = AnalysisTypeEnum::getStandardTypes();
    }

    public function addOtherAnalysisType()
    {
        $this->form->addOtherAnalysisType();
    }

    public function removeOtherAnalysisType($index)
    {
        $this->form->removeOtherAnalysisType($index);
    }

    public function render()
    {
        return view('livewire.cf-create');
    }

    public function store()
    {
        // check if user is logged in
        if (! auth()->check()) {
            return redirect()->back()->with('error', 'You must be logged in to register a core facility.');
        }

        // store
        $this->form->store();

        return redirect()->route('core-facility.home')->with('success', 'Your core facility has been registered successfully and is pending review.');
    }
}
