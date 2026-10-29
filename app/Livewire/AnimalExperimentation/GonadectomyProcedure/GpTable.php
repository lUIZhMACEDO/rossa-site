<?php

namespace App\Livewire\AnimalExperimentation\GonadectomyProcedure;

use App\Models\Study;
use Livewire\Attributes\On;
use Livewire\Component;

class GpTable extends Component
{
    public Study $study;

    public function mount(Study $study)
    {
        if (! auth()->user()?->canEditStudy($study)) {
            return redirect()->back()->with('error', 'You are not authorized to edit this study.');
        }

        $this->study = $study;
    }

    #[On('refreshTable')]
    public function refreshTable()
    {
        // should refresh on its own
    }

    public function deleteProcedure($procedureId)
    {
        if (! auth()->user()?->canEditStudy($this->study)) {
            return redirect()->back()->with('error', 'You are not authorized to edit this study.');
        }

        $procedure = $this->study->gonadectomyProcedures()->findOrFail($procedureId);

        $procedure->delete();
        $this->dispatch('livewire-alert', [
            'status' => 'success',
            'message' => 'Procedure deleted successfully.',
        ]);
    }

    public function render()
    {
        return view('livewire.animal-experimentation.gonadectomy-procedure.gp-table');
    }
}
