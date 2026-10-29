<?php

namespace App\Livewire\AnimalExperimentation\GonadectomyProcedure;

use App\Enums\GonadectomyType;
use App\Livewire\CustomModalComponent;
use App\Models\GonadectomyProcedure;
use App\Models\Study;
use Illuminate\Validation\Rules\Enum;

class GpModalForm extends CustomModalComponent
{
    public Study $study;

    public ?GonadectomyProcedure $procedure = null;

    // properties - using runtime validation
    public $procedureType;

    public $age;

    public $description;

    public function mount(Study $study, $procedureId = null)
    {
        if (! auth()->user()?->canEditStudy($study)) {
            return redirect()->back()->with('error', 'You are not authorized to update this study.');
        }

        $this->study = $study;
        $this->procedure = $procedureId ? GonadectomyProcedure::find($procedureId) : null;

        if ($this->procedure) {
            $this->procedureType = $this->procedure->type;
            $this->age = $this->procedure->age;
            $this->description = $this->procedure->description;
        }
    }

    public function saveProcedure()
    {
        if (! auth()->user()?->canEditStudy($this->study)) {
            return redirect()->back()->with('error', 'You are not authorized to edit this study.');
        }

        $this->procedureValidation();

        if ($this->procedure) {
            // update existing procedure
            $this->procedure->update([
                'type' => $this->procedureType,
                'age' => $this->age,
                'description' => $this->description,
            ]);
            $message = 'Procedure updated successfully.';
        } else {
            // create new
            $this->study->gonadectomyProcedures()->create([
                'type' => $this->procedureType,
                'age' => $this->age,
                'description' => $this->description,
            ]);
            $message = 'Procedure added successfully.';
        }

        $this->closeModalWithEvents([
            'refreshTable',
        ]);

        $this->dispatch('livewire-alert', ['status' => 'success', 'message' => $message]);
    }

    private function procedureValidation()
    {
        $this->validate([
            'procedureType' => ['required', 'string', new Enum(GonadectomyType::class)],
            'age' => 'required|integer|min:0|max:9999',
            'description' => 'nullable|string',
        ], [
            'procedureType.required' => 'Please select a procedure type.',
            'procedureType.string' => 'Please select a valid procedure type.',
            'procedureType.in' => 'Please select a valid procedure type.',
            'age.required' => 'Please enter an age.',
            'age.integer' => 'Please enter a valid age.',
            'age.min' => 'Please enter a valid age.',
            'age.max' => 'Please enter a valid age.',
            'description.string' => 'Please enter a valid description.',
        ]);
    }

    public function render()
    {
        return view('livewire.animal-experimentation.gonadectomy-procedure.gp-modal-form');
    }
}
