<div>
    @if ($study->gonadectomyProcedures()->count() == 0)
        <div class="alert mb-3 d-flex align-items-center" role="alert" style="background-color: rgba(26, 125, 181, 0.08); border-left: 4px solid #1A7DB5; color: #14273f;">
            <i class="bi bi-info-circle-fill me-2" style="color: #1A7DB5; font-size: 1.5rem;"></i>
            <span><strong>Note:</strong> Currently no gonadectomy procedures exist for your study. Please select the button below to add one.</span>
        </div>
    @endif

    <button 
        class="btn btn-primary mb-4" 
        wire:click="$dispatch('openModal', { component: 'animal-experimentation.gonadectomy-procedure.gp-modal-form', arguments:  {study: {{ $study->id }}}})"
        title="Add Procedure"
        data-bs-toggle="tooltip">
        <i class="bi bi-plus-lg"></i>
        Add Procedure
    </button>

    <div class="card mb-4" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
    <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
        <h5 class="mb-0">Table of Gonadectomy Procedures</h5>
    </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table small align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Type of Surgery</th>
                            <th>Age During Surgery (wk)</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($study->gonadectomyProcedures()->count() == 0)
                            <tr>
                                <td colspan="100%" class="text-center text-muted fst-italic py-4">No gonadectomy procedures have been added to this study yet.</td>
                            </tr>
                        @else
                            @foreach ($study->gonadectomyProcedures as $procedure)
                                <tr>
                                    <td>{{ $procedure->type }}</td>
                                    <td>{{ $procedure->age }}</td>
                                    <td>{{ $procedure->description ?? '' }}</td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group" aria-label="Actions">
                                            <button wire:click="$dispatch('openModal', { component: 'animal-experimentation.gonadectomy-procedure.gp-modal-form', arguments:  {study: {{ $study->id }}, procedureId: {{ $procedure->id }}}})" 
                                                class="btn btn-sm btn-secondary me-1"
                                                data-bs-toggle="tooltip"
                                                title="Edit Procedure">
                                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                            </button>
                                            <button wire:click="$dispatch('openModal', { component: 'animal-experimentation.gonadectomy-procedure.gp-modal-delete', arguments:  {study: {{ $study->id }}, procedureId: {{ $procedure->id }}}})" 
                                                class="btn btn-sm btn-danger me-1"
                                                data-bs-toggle="tooltip"
                                                title="Delete Procedure">
                                                <i class="bi bi-trash" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>