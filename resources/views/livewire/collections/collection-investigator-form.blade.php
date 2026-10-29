<div>
    {{-- Existing investigators table --}}
    @if($investigators->isNotEmpty())
        <table class="table table-sm mb-4">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Organization</th>
                    <th>Role</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($investigators as $investigator)
                    <tr wire:key="investigator-{{ $investigator->id }}">
                        <td>{{ $investigator->first_name }} {{ $investigator->last_name }}</td>
                        <td>{{ $investigator->email }}</td>
                        <td>{{ $investigator->organization }}</td>
                        <td>
                            <x-investigator-role-badges :labels="$investigator->roleLabels()" />
                        </td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                wire:click="alterInvestigator({{ $investigator->id }})">
                                Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                wire:click="removeInvestigator({{ $investigator->id }})"
                                wire:confirm="Remove this investigator?">
                                Remove
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <x-session-alerts />

    {{-- Add / Edit form --}}
    @php $editing = $editingInvestigatorId !== null; @endphp
    @php $prefix = $editing ? 'editInvestigator' : 'newInvestigator'; @endphp

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $editing ? 'Edit Investigator' : 'Add Investigator' }}</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">First Name</label>
                    <input type="text" class="form-control @error($prefix.'.first_name') is-invalid @enderror"
                        wire:model="{{ $prefix }}.first_name">
                    @error($prefix.'.first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Last Name</label>
                    <input type="text" class="form-control @error($prefix.'.last_name') is-invalid @enderror"
                        wire:model="{{ $prefix }}.last_name">
                    @error($prefix.'.last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Email</label>
                    <input type="email" class="form-control @error($prefix.'.email') is-invalid @enderror"
                        wire:model="{{ $prefix }}.email">
                    @error($prefix.'.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Primary Department</label>
                    <input type="text" class="form-control @error($prefix.'.department') is-invalid @enderror"
                        wire:model="{{ $prefix }}.department">
                    @error($prefix.'.department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Primary Organization</label>
                    <input type="text" class="form-control @error($prefix.'.organization') is-invalid @enderror"
                        wire:model="{{ $prefix }}.organization">
                    @error($prefix.'.organization') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Country</label>
                    <select class="form-select @error($prefix.'.country') is-invalid @enderror"
                        wire:model.live="{{ $prefix }}.country">
                        <option value="">Select a country...</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->code }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                    @error($prefix.'.country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                @if(($editing ? $editInvestigator['country'] : $newInvestigator['country']) === 'US')
                    <div class="col-md-6">
                        <label class="form-label required">State</label>
                        <select class="form-select @error($prefix.'.state') is-invalid @enderror"
                            wire:model="{{ $prefix }}.state">
                            <option value="">Select a state...</option>
                            @foreach($states as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error($prefix.'.state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endif
            </div>

            <x-investigator-role-picker
                class="mt-3 mb-0"
                :show-submitting="false"
                :is-corresponding="$editing ? $editInvestigator['corresponding'] : $newInvestigator['corresponding']"
                :is-contributing="$editing ? $editInvestigator['is_contributing'] : $newInvestigator['is_contributing']"
                corresponding-model="{{ $prefix }}.corresponding"
                contributing-model="{{ $prefix }}.is_contributing"
                corresponding-id="corresponding-{{ $prefix }}"
                contributing-id="contributing-{{ $prefix }}"
                :error="$errors->first($prefix.'.is_contributing')"
            />

            <div class="mt-3 d-flex gap-2">
                @if($editing)
                    <button type="button" class="btn btn-primary" wire:click="updateInvestigator">Update Investigator</button>
                    <button type="button" class="btn btn-outline-secondary" wire:click="cancelEdit">Cancel</button>
                @else
                    <button type="button" class="btn btn-primary" wire:click="addInvestigator">Add Investigator</button>
                @endif
            </div>
        </div>
    </div>
</div>
