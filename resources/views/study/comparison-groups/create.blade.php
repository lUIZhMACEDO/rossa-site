@section('title', 'Edit Comparison Group')
@section('guide-content')
    @include('guides.data-ingestion._05_1upload-groups')
@endsection
<x-layouts.submission :$study>

@php
    $isEdit = isset($comparisonGroup);
@endphp

<div class="pt-0 pb-5">
    <div class="p-3 mb-3" style="background-color: #0D3B52;border-radius: 0.375rem;">
        <h1 class="mb-1" style="color: #fff;">Edit Comparison Group</h1>
        <p class="mb-0" style="color: #E6F1FB;">
            Modify the sample groups and their order.
        </p>
    </div>

    @if($study->sampleGroups->isEmpty())
        <div class="alert alert-warning">
            <i class="bi-exclamation-triangle"></i> No sample groups found. Please create sample groups first before creating comparison groups.
        </div>
        <a href="{{ route('study.sample-groups', $study) }}" class="btn btn-primary">
            <i class="bi-arrow-left"></i> Go to Sample Groups
        </a>
    @else

        <div class="mb-3">
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#instructionsCollapse" aria-expanded="false" aria-controls="instructionsCollapse">
                <i class="bi-info-circle"></i> How does this work? (click to expand instructions & example)
            </button>
        </div>

        <div class="collapse mb-4" id="instructionsCollapse">
            <h2 class="h5">Instructions</h2>
            <p>For the purpose of grouping and ordering your data properly in tables and graphs, please define the experimental sample groups you would like to compare.
                Within each comparison, specify the order of groups as shown in the example below. This ensures consistent presentation across all visualizations and statistical analyses.
            </p>
            <h6 class="fw-bold">Example:</h6>
            <div class="bg-light p-3 rounded">
                <div class="text-center my-3">
                    <img src="{{ asset('images/instructions/comparison-group-example.png') }}" 
                        alt="Comparison Groups Example" 
                        class="img-fluid border rounded"
                        style="max-height: 400px;">
                </div>
            </div>
        </div>

        <hr>

        @if($errors->any())
            <div class="alert alert-danger" id="errorAlert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h2 id="formSection" class="h5">Edit Comparison Group</h2>
        <form method="POST" action="{{ route('study.comparison-groups.update', [$study, $comparisonGroup]) }}" id="comparisonForm">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="card" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
                        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
                            <h5 class="mb-0">Available Sample Groups</h5>
                            <small style="color: #E6F1FB;">Click to add to comparison</small>
                        </div>
                        <div class="card-body">
                            <div id="availableGroups" class="list-group">
                                @php
                                    $selectedIds = old('sample_group_ids', $comparisonGroup->sample_group_ids ?? []);
                                @endphp
                                @foreach($study->sampleGroups as $group)
                                    <div class="list-group-item sample-group-item {{ in_array($group->id, $selectedIds) ? 'disabled' : '' }}" 
                                        data-group-id="{{ $group->id }}"
                                        data-is-control="{{ $group->control ? 'true' : 'false' }}"
                                        data-label="{{ $group->label }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi-grip-vertical text-muted me-2"></i>
                                                    <strong class="me-1">{{ $group->label }}</strong>
                                                    @if($group->control)
                                                        <span class="badge bg-primary ms-1" data-bs-toggle="tooltip" title="Control" style="cursor: pointer;">Control</span>
                                                    @endif
                                                    @if($group->sex)
                                                        <span class="badge text-bg-primarydark ms-1" data-bs-toggle="tooltip" title="Sex" style="cursor: pointer;">{{ ucfirst($group->sex) }}</span>
                                                    @endif
                                                    @if($group->age)
                                                        <span class="badge bg-success ms-1" data-bs-toggle="tooltip" title="Age" style="cursor: pointer;">{{ $group->age }}wk</span>
                                                    @endif
                                                </div>
                                                <small class="text-muted">
                                                    @if($group->animalModel)
                                                        {{ $group->animalModel->animal_model_name.' ('.$group->animalModel->experimentalStrainLabel().')' }}
                                                    @else
                                                        <strong>Unknown Animal Model</strong>
                                                    @endif
                                                    @if($group->genotype)
                                                        • {{ $group->genotypeWithAllele() }}
                                                    @endif
                                                    @if($group->gonadectomyProcedure)
                                                        • <strong>GP:</strong> {{ $group->gonadectomyProcedure->getShortDisplayName() }} ({{ $group->gonadectomyProcedure->age }}wk)
                                                    @endif
                                                    @if($group->drugAdministrationPlan)
                                                        @php
                                                            $drugNames = $group->drugAdministrationPlan->getDrugNames();
                                                        @endphp
                                                        • <strong>DT:</strong> {{ implode(', ', $drugNames) }}
                                                    @endif
                                                </small>
                                            </div>
                                            <i class="bi-plus-circle text-primary"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
                        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
                            <h5 class="mb-0 required">Selected Groups (Ordered)</h5>
                            <small style="color: #E6F1FB;">Drag to reorder. This order will be used in visualizations.</small>
                        </div>
                        <div class="card-body">
                            <div id="selectedGroups" class="selected-groups list-group mb-3">
                                @php
                                    $selectedIds = old('sample_group_ids', $comparisonGroup->sample_group_ids ?? []);
                                    $groupsById = $study->sampleGroups->keyBy('id');
                                    $sortedGroups = collect($selectedIds)
                                        ->map(fn($id) => $groupsById->get($id))
                                        ->filter()
                                        ->concat($study->sampleGroups->filter(fn($g) => !in_array($g->id, $selectedIds)));
                                @endphp
                                <div class="text-muted text-center p-4 {{ !empty($selectedIds) ? 'd-none' : '' }}" id="emptyMessage">
                                    <i class="bi-arrow-left"></i> Click groups from the left to add them here
                                </div>
                                @foreach($sortedGroups as $group)
                                    <div class="list-group-item selected-group-item {{ in_array($group->id, $selectedIds) ? '' : 'd-none' }}"
                                         data-group-id="{{ $group->id }}"
                                         data-is-control="{{ $group->control ? 'true' : 'false' }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi-grip-vertical text-muted me-2"></i>
                                                    <strong class="me-1">{{ $group->label }}</strong>
                                                    @if($group->control)
                                                        <span class="badge bg-primary ms-1" data-bs-toggle="tooltip" title="Control" style="cursor: pointer;">Control</span>
                                                    @endif
                                                    @if($group->sex)
                                                        <span class="badge text-bg-primarydark ms-1" data-bs-toggle="tooltip" title="Sex" style="cursor: pointer;">{{ ucfirst($group->sex) }}</span>
                                                    @endif
                                                    @if($group->age)
                                                        <span class="badge bg-success ms-1" data-bs-toggle="tooltip" title="Age" style="cursor: pointer;">{{ $group->age }}wk</span>
                                                    @endif
                                                </div>
                                                <small class="text-muted">
                                                    @if($group->animalModel)
                                                        {{ $group->animalModel->animal_model_name.' ('.$group->animalModel->experimentalStrainLabel().')' }}
                                                    @else
                                                        <strong>Unknown Animal Model</strong>
                                                    @endif
                                                    @if($group->genotype)
                                                        • {{ $group->genotypeWithAllele() }}
                                                    @endif
                                                    @if($group->gonadectomyProcedure)
                                                        • <strong>GP:</strong> {{ $group->gonadectomyProcedure->getShortDisplayName() }} ({{ $group->gonadectomyProcedure->age }}wk)
                                                    @endif
                                                    @if($group->drugAdministrationPlan)
                                                        @php
                                                            $drugNames = $group->drugAdministrationPlan->getDrugNames();
                                                        @endphp
                                                        • <strong>DT:</strong> {{ implode(', ', $drugNames) }}
                                                    @endif
                                                </small>
                                            </div>
                                            <i class="bi-x-circle text-danger" title="Remove"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('study.comparison-groups.index', $study) }}" class="btn btn-secondary">
                    <i class="bi-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="bi-check-circle"></i> Update Comparison Group
                </button>
            </div>
        </form>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const availableGroups = document.getElementById('availableGroups');
    const selectedGroups = document.getElementById('selectedGroups');
    const emptyMessage = document.getElementById('emptyMessage');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('comparisonForm');

    if (!form) return;

    const sortable = Sortable.create(selectedGroups, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        onEnd: function() {
            updateVisibleClasses();
        }
    });

    availableGroups.addEventListener('click', function(e) {
        const item = e.target.closest('.sample-group-item');
        if (!item || item.classList.contains('disabled')) return;
        addToSelected(item);
    });

    selectedGroups.addEventListener('click', function(e) {
        const item = e.target.closest('.selected-group-item');
        if (!item) return;
        removeFromSelected(item);
    });

    function addToSelected(item) {
        const groupId = item.dataset.groupId;
        const selectedItem = selectedGroups.querySelector(`.selected-group-item[data-group-id="${groupId}"]`);
        if (selectedItem) {
            selectedGroups.appendChild(selectedItem);
            selectedItem.classList.remove('d-none');
        }
        item.classList.add('disabled');
        emptyMessage.classList.add('d-none');
        updateVisibleClasses();
        updateSubmitButton();
    }

    function removeFromSelected(item) {
        const groupId = item.dataset.groupId;
        item.classList.add('d-none');
        const availableItem = availableGroups.querySelector(`[data-group-id="${groupId}"]`);
        if (availableItem) {
            availableItem.classList.remove('disabled');
        }
        const visibleItems = selectedGroups.querySelectorAll('.selected-group-item:not(.d-none)');
        if (visibleItems.length === 0) {
            emptyMessage.classList.remove('d-none');
        }
        updateVisibleClasses();
        updateSubmitButton();
    }

    function updateSubmitButton() {
        const selectedCount = selectedGroups.querySelectorAll('.selected-group-item:not(.d-none)').length;
        submitBtn.disabled = selectedCount < 2;
    }

    function updateVisibleClasses() {
        selectedGroups.querySelectorAll('.selected-group-item').forEach(item => {
            item.classList.remove('first-visible', 'last-visible');
        });
        const visibleItems = selectedGroups.querySelectorAll('.selected-group-item:not(.d-none)');
        if (visibleItems.length > 0) {
            visibleItems[0].classList.add('first-visible');
            visibleItems[visibleItems.length - 1].classList.add('last-visible');
        }
    }

    form.addEventListener('submit', function(e) {
        form.querySelectorAll('input[name="sample_group_ids[]"]').forEach(input => input.remove());
        const items = selectedGroups.querySelectorAll('.selected-group-item:not(.d-none)');
        items.forEach(item => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'sample_group_ids[]';
            input.value = item.dataset.groupId;
            form.appendChild(input);
        });
    });

    updateVisibleClasses();
    updateSubmitButton();

    @if($errors->any())
        const errorAlert = document.getElementById('errorAlert');
        if (errorAlert) {
            const elementPosition = errorAlert.getBoundingClientRect().top + window.pageYOffset;
            const offsetPosition = elementPosition - 80;
            window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
        }
    @endif
});
</script>

</x-layouts.submission>