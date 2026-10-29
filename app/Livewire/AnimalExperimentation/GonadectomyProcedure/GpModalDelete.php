<?php

namespace App\Livewire\AnimalExperimentation\GonadectomyProcedure;

use App\Livewire\CustomModalComponent;
use App\Models\GonadectomyProcedure;
use App\Models\Study;

class GpModalDelete extends CustomModalComponent
{
    public Study $study;

    public GonadectomyProcedure $procedure;

    public static function modalMaxWidth(): string
    {
        return 'md';
    }

    public function mount(Study $study, $procedureId)
    {
        if (! auth()->user()?->canEditStudy($study)) {
            return redirect()->back()->with('error', 'You are not authorized to update this study.');
        }

        $this->study = $study;
        $this->procedure = GonadectomyProcedure::find($procedureId);
    }

    public function deleteProcedure()
    {
        if (! auth()->user()?->canEditStudy($this->study)) {
            return redirect()->back()->with('error', 'You are not authorized to edit this study.');
        }

        $this->procedure->delete();

        $this->closeModalWithEvents([
            'refreshTable',
        ]);

        $this->dispatch('livewire-alert', ['status' => 'success', 'message' => 'Procedure deleted successfully.']);
    }

    public function render()
    {
        return view('livewire.animal-experimentation.gonadectomy-procedure.gp-modal-delete');
    }
}
