<div @if($study->isLockedForEditing(auth()->user())) data-input-locked @endif>

    <h1 class="mb-2 p-3" style="background-color: #0d3b52; color: #ffffff; border-radius: 0.375rem;">Investigator Information</h1>

    @if ($study->collection_id)
        <div class="alert alert-info small mb-4" role="alert">
            <strong>This study belongs to a collection.</strong>
            Investigators are shared across every study in
            <strong>{{ $study->collection->identifier }}</strong>
            and are managed on the collection, not on individual studies.
            @php
                $collection = $study->collection;
                $user = auth()->user();
            @endphp
            @if ($collection && $user && $collection->canUserEditMetadata($user))
                <a href="{{ route('my-collections.edit', $collection) }}" class="alert-link">Edit collection investigators</a>
            @elseif ($collection && $user?->hasPermission('manage-collections'))
                <a href="{{ route('admin.collections.edit', $collection) }}" class="alert-link">Edit collection investigators</a>
            @endif
        </div>
    @else
        <div class="alert alert-info small mb-4" role="alert">
                        <strong>Note:</strong> A Corresponding Investigator is required for submission. Mark additional investigators as Contributing.
        </div>
    @endif

    @unless ($study->collection_id)
    <!-- Investigator Form -->
    <div class="card mb-4" style="border-left: 4px solid #0d3b52;">
        <div class="card-title text-white p-3 mb-0 d-flex justify-content-between align-items-center" style="background-color: #0d3b52;">
            <h5 class="mb-0">Add New Investigator</h5>
            <button type="button" class="btn btn-outline-light btn-sm" wire:click="addMyself">
                <i class="bi-person-check me-1"></i>Add myself
            </button>
        </div>
        <div class="card-body">
            
            <x-investigator-role-picker
                :is-submitting="$newInvestigator['is_submitting']"
                :is-corresponding="$newInvestigator['corresponding']"
                :is-contributing="$newInvestigator['is_contributing']"
                submitting-model="newInvestigator.is_submitting"
                corresponding-model="newInvestigator.corresponding"
                contributing-model="newInvestigator.is_contributing"
                submitting-id="isSubmitting"
                corresponding-id="isCorresponding"
                contributing-id="isContributing"
                :error="$errors->first('newInvestigator.is_contributing')"
            />

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="firstName" class="form-label required">First Name</label>
                    <input type="text" class="form-control" id="firstName" wire:model="newInvestigator.first_name">
                    @error('newInvestigator.first_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="lastName" class="form-label required">Last Name</label>
                    <input type="text" class="form-control" id="lastName" wire:model="newInvestigator.last_name">
                    @error('newInvestigator.last_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" class="form-control" id="email" wire:model="newInvestigator.email">
                    @error('newInvestigator.email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="department" class="form-label required">Primary Department</label>
                    <input type="text" class="form-control" id="department" wire:model="newInvestigator.department">
                    @error('newInvestigator.department') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
				
                <div class="col-md-4 mb-3">
					<label for="organization" class="form-label required">Primary Organization</label>
                    <input type="text" class="form-control" id="organization" wire:model="newInvestigator.organization">
                    @error('newInvestigator.organization') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
				
                <div class="col-md-4 mb-3">
                    <label for="country" class="form-label required">Country</label>
                    <select class="form-select" id="country" wire:model.live="newInvestigator.country">
                        <option value="">Select Country...</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->code }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                    @error('newInvestigator.country') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                @if($newInvestigator['country'] === 'US')
                    <div class="col-md-4 mb-3">
                        <label for="state">State</label>
                        <select class="form-select" id="state" wire:model="newInvestigator.state">
                            <option value="">Select State...</option>
                            @foreach($states as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('newInvestigator.state') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>

            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary mt-4" wire:click="addInvestigator">
                    <i class="bi-person-add"></i> Add Investigator to Study
                </button>
            </div>
        </div>
    </div>
    @endunless

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('lower-success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('lower-success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Investigators Table -->
     <div class="card mb-4" style="border-left: 4px solid #0d3b52;">
    <div class="card-title text-white p-3 mb-0 text-left" style="background-color: #0d3b52;">
        <h5 class="mb-0">
                    @if ($study->collection_id)
                        Investigators from This Collection
                    @else
                        Table of Contributing Investigators
                    @endif
                </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table small">
                <thead>
                        <tr>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Type</th>
                            @unless ($study->collection_id)
                                <th>Actions</th>
                            @endunless
                        </tr>
                    </thead>
                    <tbody>
                        @if($investigators->count() > 0)
                            @foreach($investigators as $investigator)
                                @if(!$study->collection_id && $editingInvestigatorId == $investigator->id)
                                    <tr class="table-active">
                                        <td colspan="100%">
                                            <div class="card" style="border-left: 4px solid var(--bs-secondary);">
                                                <div class="card-body">
                                                    <div class="card-title">
                                                        <h5 class="mb-0">Edit Investigator</h5>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label required">First Name</label>
                                                            <input type="text" class="form-control" wire:model="editInvestigator.first_name">
                                                            @error('editInvestigator.first_name') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label required">Last Name</label>
                                                            <input type="text" class="form-control" wire:model="editInvestigator.last_name">
                                                            @error('editInvestigator.last_name') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label required">Email</label>
                                                            <input type="email" class="form-control" wire:model="editInvestigator.email">
                                                            @error('editInvestigator.email') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label required">Primary Department</label>
                                                            <input type="text" class="form-control" wire:model="editInvestigator.department">
                                                            @error('editInvestigator.department') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label required">Primary Organization</label>
                                                            <input type="text" class="form-control" wire:model="editInvestigator.organization">
                                                            @error('editInvestigator.organization') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label required">Country</label>
                                                            <select class="form-select" wire:model.live="editInvestigator.country">
                                                                <option value="">Select Country...</option>
                                                                @foreach($countries as $country)
                                                                    <option value="{{ $country->code }}">{{ $country->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('editInvestigator.country') <span class="text-danger">{{ $message }}</span> @enderror
                                                        </div>
                                                        @if($editInvestigator['country'] === 'US')
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label required">State</label>
                                                                <select class="form-select" wire:model="editInvestigator.state">
                                                                    <option value="">Select State...</option>
                                                                    @foreach($states as $code => $name)
                                                                        <option value="{{ $code }}">{{ $name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('editInvestigator.state') <span class="text-danger">{{ $message }}</span> @enderror
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <x-investigator-role-picker
                                                        :is-submitting="$editInvestigator['is_submitting']"
                                                        :is-corresponding="$editInvestigator['corresponding']"
                                                        :is-contributing="$editInvestigator['is_contributing']"
                                                        submitting-model="editInvestigator.is_submitting"
                                                        corresponding-model="editInvestigator.corresponding"
                                                        contributing-model="editInvestigator.is_contributing"
                                                        submitting-id="editIsSubmitting"
                                                        corresponding-id="editIsCorresponding"
                                                        contributing-id="editIsContributing"
                                                        :error="$errors->first('editInvestigator.is_contributing')"
                                                    />
                                                    <div class="d-flex gap-2 justify-content-end">
                                                        <button type="button" class="btn btn-primary" wire:click="updateInvestigator">
                                                            <i class="bi-person-up"></i> Update
                                                        </button>
                                                        <button type="button" class="btn btn-secondary" wire:click="cancelEdit">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td>
                                            {{ $investigator->first_name }} {{ $investigator->last_name }}
                                            <span class="d-block text-muted">{{ $investigator->email }}</span></td>
                                        <td>{{ $investigator->department }}
                                            <span class="d-block text-muted">{{ $investigator->organization }}</span>
                                        </td>
                                        <td>{{ $investigator->state ? $investigator->state . ', ' : '' }}{{ $investigator->country->name }}</td>
                                        <td>
                                            <x-investigator-role-badges :labels="$investigator->roleLabels()" />
                                        </td>
                                        @unless ($study->collection_id)
                                            <td>
                                                <div class="btn-group gap-2" role="group">
                                                    <button type="button"
                                                        class="btn btn-primary btn-sm"
                                                        wire:click="alterInvestigator({{ $investigator->id }})"
                                                        title="Edit Investigator"
                                                        data-bs-toggle="tooltip">
                                                        <i class="bi-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm"
                                                        wire:click="removeInvestigator({{ $investigator->id }})"
                                                        title="Remove Investigator"
                                                        data-bs-toggle="tooltip">
                                                        <i class="bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        @endunless
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr>
                                <td colspan="100%" class="text-muted text-center">
                                    @if ($study->collection_id)
                                        No investigators on this collection yet.
                                        @if ($study->collection && auth()->user() && $study->collection->canUserEditMetadata(auth()->user()))
                                            <a href="{{ route('my-collections.edit', $study->collection) }}">Add them on the collection</a>.
                                        @elseif ($study->collection && auth()->user()?->hasPermission('manage-collections'))
                                            <a href="{{ route('admin.collections.edit', $study->collection) }}">Add them on the collection</a>.
                                        @endif
                                    @else
                                        No investigators have been added yet. Use the form above to add a new investigator.
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                @if (!$study->collection_id && $study->progressInvestigators() === 1)
                    <div class="alert alert-warning">
                        <i class="bi-exclamation-triangle"></i>
                        Please add at least one submitting and one corresponding investigator.
                    </div>
                @elseif ($study->collection_id && $study->progressInvestigators() === 1)
                    <div class="alert alert-warning">
                        <i class="bi-exclamation-triangle"></i>
                        This collection needs at least one corresponding investigator before its studies can be submitted.
                    </div>
                @endif
            </div>
        </div>
    </div>
    <form id="inv-save-continue-form" action="{{ route('study.save-investigators', $study) }}" method="POST" hx-boost="true">
        @csrf
        <div class="mt-4 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary btn-lg">Save & Continue <i class="bi-arrow-right"></i></button>
        </div>
    </form>

    <div class="modal fade" id="unsavedChangesModal" tabindex="-1" aria-labelledby="unsavedChangesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unsavedChangesModalLabel">
                        <i class="bi-exclamation-triangle-fill text-warning me-2"></i>Unsaved Investigator
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    You have unsaved information in the <strong>Add New Investigator</strong> form.
                    Click <strong>Add Investigator to Study</strong> first if you'd like to keep this investigator before continuing.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Go Back</button>
                    <button type="button" class="btn btn-primary" id="confirmContinueBtn">Continue Anyway</button>
                </div>
            </div>
        </div>
    </div>
</div>