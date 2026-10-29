<div class="card">
    

    <div class="card-header d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Inducible Drug Treatments</h5>
        <button type="button" class="btn btn-primary btn-sm" wire:click="openModal">
            Add Treatment
        </button>
    </div>

    <div class="card-body">
        @if(session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($treatments->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Drug Name</th>
                            <th>Dose</th>
                            <th>Duration</th>
                            <th>Frequency</th>
                            <th>Route</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($treatments as $treatment)
                            <tr>
                                <td>{{ $treatment->drug_name }}</td>
                                <td>
                                    @if(floor($treatment->drug_dose) == $treatment->drug_dose)
                                        {{ number_format($treatment->drug_dose, 0) }}
                                    @else
                                        {{ $treatment->drug_dose }}
                                    @endif
                                    {{ $treatment->drug_dose_unit }}
                                </td>
                                <td>{{ $treatment->drug_duration }} {{ $treatment->drug_duration_unit }}</td>
                                <td>{{ $treatment->drug_frequency }}</td>
                                <td>{{ $treatment->drug_route }}</td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            wire:click="removeTreatment({{ $treatment->id }})"
                                            onclick="return confirm('Are you sure you want to remove this treatment?')">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">No drug treatments added yet.</p>
        @endif
    </div>

    

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Drug Treatment</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Drug Name</label>
                                <input type="text" class="form-control @if($readonlyName) disabled @endif" wire:model="newTreatment.drugName" 
                                       @if($readonlyName) readonly @endif>
                                @error('newTreatment.drugName') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Drug Dose</label>
                                <input type="number" step="any" class="form-control" wire:model="newTreatment.drugDose">
                                @error('newTreatment.drugDose') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Drug Dose Unit</label>
                                <select class="form-select" wire:model="newTreatment.drugDoseUnit">
                                    <option value="">Select dose unit...</option>
                                    @foreach ($doseUnitDropdownOptions as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('newTreatment.drugDoseUnit') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Treatment Duration</label>
                                <input type="number" step="any" class="form-control" wire:model="newTreatment.drugDuration">
                                @error('newTreatment.drugDuration') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Duration Unit</label>
                                <select class="form-select" wire:model="newTreatment.drugDurationUnit">
                                    <option value="">Select duration unit...</option>
                                    @foreach ($durationUnitDropdownOptions as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('newTreatment.drugDurationUnit') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Treatment Frequency</label>
                                <select class="form-select" wire:model="newTreatment.drugFrequency">
                                    <option value="">Select frequency...</option>
                                    @foreach ($frequencyDropdownOptions as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('newTreatment.drugFrequency') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        @if($newTreatment['drugFrequency'] === 'Other')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Other Frequency</label>
                                    <input type="text" class="form-control" wire:model="newTreatment.drugFrequencyOther">
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Administration Route</label>
                                <select class="form-select" wire:model="newTreatment.drugRoute">
                                    <option value="">Select route...</option>
                                    @foreach ($routeDropdownOptions as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('newTreatment.drugRoute') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="addTreatment">Add Treatment</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('inducible-system-changed', function(event) {
                @this.call('updateAutofillName', event.detail.value);
            });
        });
    </script>
</div>