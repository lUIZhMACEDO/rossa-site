<div>
    <h6 class="fw-semibold mb-2">FACILITY INFORMATION</h6>
    <hr class="mt-0 mb-3">

    <div class="mb-3">
        <label for="facility_name" class="form-label required">Core Facility Name</label>
        <input type="text" class="form-control @error('form.facility_name') is-invalid @enderror" id="facility_name" name="facility_name" wire:model.blur="form.facility_name">
        @error('form.facility_name') <span class="invalid-feedback">{{ $message ?? '' }}</span> @enderror
    </div>

    <div class="mb-3">
        <label for="web_address" class="form-label required">Core Facility Web Address</label>
        <input type="url" class="form-control @error('form.web_address') is-invalid @enderror" id="web_address" name="web_address" wire:model.blur="form.web_address">
        @error('form.web_address') <span class="invalid-feedback">{{ $message ?? '' }}</span> @enderror
    </div>

    <div class="mb-3">
        <label for="institution" class="form-label required">Institution/Organization Name</label>
        <input type="text" class="form-control @error('form.institution') is-invalid @enderror" id="institution" name="institution" wire:model.blur="form.institution">
        @error('form.institution') <span class="invalid-feedback">{{ $message ?? '' }}</span> @enderror
    </div>

    <h6 class="fw-semibold mb-2 mt-4">CONTACT INFORMATION</h6>
    <hr class="mt-0 mb-3">

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="contact_name" class="form-label required">Contact Person Name</label>
            <input type="text" class="form-control @error('form.contact_name') is-invalid @enderror" id="contact_name" name="contact_name" wire:model.blur="form.contact_name">
            @error('form.contact_name') <span class="invalid-feedback">{{ $message ?? '' }}</span> @enderror
        </div>
        <div class="col-md-6">
            <label for="contact_email" class="form-label required">Contact Person Email</label>
            <input type="email" class="form-control @error('form.contact_email') is-invalid @enderror" id="contact_email" name="contact_email" wire:model.blur="form.contact_email">
            @error('form.contact_email') <span class="invalid-feedback">{{ $message ?? '' }}</span> @enderror
        </div>
    </div>

    <div x-data="{ show: $wire.entangle('form.showSecondaryContact') }">
        <div x-show="!show" class="mb-3">
            <button type="button" class="btn btn-sm btn-outline-primary"
                @click="show = true"
                data-bs-toggle="tooltip"
                title="Add Secondary Contact">
                Add Secondary Contact <i class="bi bi-plus-circle"></i>
            </button>
        </div>

        <div class="row mb-3" x-show="show" x-cloak x-transition >
            <div class="col-md-6">
                <label for="secondary_contact_name" class="form-label">Secondary Contact Name</label>
                <input type="text" class="form-control @error('form.secondary_contact_name') is-invalid @enderror" id="secondary_contact_name" name="secondary_contact_name" wire:model.blur="form.secondary_contact_name">
                @error('form.secondary_contact_name') <span class="invalid-feedback">{{ $message ?? '' }}</span> @enderror
            </div>
            <div class="col-md-6">
                <label for="secondary_contact_email" class="form-label">Secondary Contact Email</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="email" class="form-control @error('form.secondary_contact_email') is-invalid @enderror" id="secondary_contact_email" name="secondary_contact_email" wire:model.blur="form.secondary_contact_email">
                    <button type="button" class="btn btn-sm btn-outline-danger"
                        @click="show = false"
                        data-bs-toggle="tooltip"
                        title="Remove Secondary Contact">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                @error('form.secondary_contact_email') <span class="invalid-feedback">{{ $message ?? '' }}</span> @enderror
            </div>
        </div>
    </div>

    <h6 class="fw-semibold mb-2 mt-4">ANALYSIS TYPES</h6>
    <hr class="mt-0 mb-3">

    <div class="mb-3">
        <label class="form-label required">Types of Analysis Performed</label>
        <div class="@error('form.analysis_types') is-invalid @enderror">
            @foreach ($form->availableAnalysisTypes as $analysisType)
                <div class="form-check" wire:key="analysis-type-{{ $analysisType['slug'] }}">
                    <input class="form-check-input" type="checkbox" value="{{ json_encode($analysisType) }}" 
                           id="{{ $analysisType['slug'] }}" wire:model="form.analysis_types">
                    <label class="form-check-label" for="{{ $analysisType['slug'] }}">
                        {{ $analysisType['name'] }}
                    </label>
                </div>
            @endforeach

            {{-- Custom Analysis Types --}}
            <div class="mt-2 col-4">
                <strong class="me-1">Don't see your analysis type?</strong>
            
                @if(empty($form->other_analysis_types))
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Click the "<i class="bi bi-plus-circle"></i>" icon to add analysis types not listed above.
                    </p>
                @endif
            
                {{-- Custom Analysis Type Fields --}}
                @foreach($form->other_analysis_types as $index => $customType)
                    <div class="d-flex align-items-center mt-2" wire:key="other-type-{{ $index }}">
                        <div class="flex-grow-1 me-2">
                            <input type="text" 
                                   class="form-control @error('form.other_analysis_types.' . $index) is-invalid @enderror" 
                                   placeholder="Enter custom analysis type name"
                                   wire:model="form.other_analysis_types.{{ $index }}"
                                   x-data
                                   x-init="if (!$el.value) $nextTick(() => $el.focus())">
                            @error('form.other_analysis_types.' . $index)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                wire:click="removeOtherAnalysisType({{ $index }})"
                                data-bs-toggle="tooltip"
                                title="Remove Custom Type">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                @endforeach

                <div class="d-flex align-items-center mt-2">
                    @if(count($form->other_analysis_types) < 5)
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            wire:click="addOtherAnalysisType"
                            data-bs-toggle="tooltip"
                            title="Add Custom Type">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                    @endif
                    @if(count($form->other_analysis_types) >= 5)
                        <small class="text-muted">(Maximum 5 custom types)</small>
                    @endif
                </div>
            </div>
        </div>
        
        @error('form.analysis_types')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @error('form.other_analysis_types')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
