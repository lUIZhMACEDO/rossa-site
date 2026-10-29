@extends('study.summary.layout')

@section('tab-content')
<div class="accordion" id="studyAccordion" style="--bs-accordion-active-bg: var(--bs-gray-300);">
    {{-- study information --}}
    @php
        $showEditButton = !$study->isLockedForEditing(auth()->user());
    @endphp
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingStudyInfo">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudyInfo" aria-expanded="true" aria-controls="collapseStudyInfo">
                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                    <span class="fs-5">Study Information</span>
                    @if ($showEditButton)
                        <a href="{{ route('study.study-information', $study) }}"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="tooltip"
                            title="Edit Study Information"
                            onclick="event.stopPropagation()">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endif
                </div>
            </button>
        </h2>
        <div id="collapseStudyInfo" class="accordion-collapse collapse show" aria-labelledby="headingStudyInfo" data-bs-parent="#studyAccordion">
            <div class="accordion-body">
                @include('study.summary.partials.study-information')
            </div>
        </div>
    </div>

    @if ($study->hasSubjectAreaContent())
        {{-- subject areas --}}
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSubjectAreas">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubjectAreas" aria-expanded="true" aria-controls="collapseSubjectAreas">
                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                        <span class="fs-5">Subject Areas</span>
                        @if ($showEditButton)
                            <a href="{{ route('study.subject-areas', $study) }}"
                                class="btn btn-primary btn-sm"
                                data-bs-toggle="tooltip"
                                title="Edit Subject Areas"
                                onclick="event.stopPropagation()">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endif
                    </div>
                </button>
            </h2>
            <div id="collapseSubjectAreas" class="accordion-collapse collapse show" aria-labelledby="headingSubjectAreas" data-bs-parent="#studyAccordion">
                <div class="accordion-body">
                    @include('study.summary.partials.subject-areas')
                </div>
            </div>
        </div>
    @endif

    {{-- investigators --}}
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingInvestigators">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInvestigators" aria-expanded="true" aria-controls="collapseInvestigators">
                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                    <span class="fs-5">Investigators</span>
                    @if ($showEditButton)
                        <a href="{{ route('study.investigators', $study) }}"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="tooltip"
                            title="Edit Investigators"
                            onclick="event.stopPropagation()">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endif
                </div>
            </button>
        </h2>
        <div id="collapseInvestigators" class="accordion-collapse collapse show" aria-labelledby="headingInvestigators" data-bs-parent="#studyAccordion">
            <div class="accordion-body">
                @include('study.summary.partials.investigators')
            </div>
        </div>
    </div>

    {{-- animal models --}}
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingAnimalModels">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAnimalModels" aria-expanded="true" aria-controls="collapseAnimalModels">
                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                    <span class="fs-5">Animal Models</span>
                    @if ($showEditButton)
                        <a href="{{ route('study.animal-experimentation.animal-models', $study) }}"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="tooltip"
                            title="Edit Animal Models"
                            onclick="event.stopPropagation()">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endif
                </div>
            </button>
        </h2>
        <div id="collapseAnimalModels" class="accordion-collapse collapse show" aria-labelledby="headingAnimalModels" data-bs-parent="#studyAccordion">
            <div class="accordion-body">
                @include('study.summary.partials.animal-models')
            </div>
        </div>
    </div>

    {{-- drug treatments --}}
    @if ( $study->performs_drug_treatment )
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingDrugTreatments">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDrugTreatments" aria-expanded="true" aria-controls="collapseDrugTreatments">
                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                    <span class="fs-5">Drug Treatments</span>
                    @if ($showEditButton)
                        <a href="{{ route('study.animal-experimentation.drug-treatment', $study) }}"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="tooltip"
                            title="Edit Drug Treatments"
                            onclick="event.stopPropagation()">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endif
                </div>
            </button>
        </h2>
        <div id="collapseDrugTreatments" class="accordion-collapse collapse show" aria-labelledby="headingDrugTreatments" data-bs-parent="#studyAccordion">
            <div class="accordion-body">
                @include('study.summary.partials.drug-treatments')
            </div>
        </div>
    </div>
    @endif


    {{-- gonadectomy procedures --}}
    @if ( $study->performs_gonadectomy )
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingGonadectomyProcedures">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGonadectomyProcedures" aria-expanded="true" aria-controls="collapseGonadectomyProcedures">
                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                    <span class="fs-5">Gonadectomy Procedures</span>
                    @if ($showEditButton)
                        <a href="{{ route('study.animal-experimentation.gonadectomy-procedure', $study) }}"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="tooltip"
                            title="Edit Gonadectomy Procedures"
                            onclick="event.stopPropagation()">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endif
                </div>
            </button>
        </h2>
        <div id="collapseGonadectomyProcedures" class="accordion-collapse collapse show" aria-labelledby="headingGonadectomyProcedures" data-bs-parent="#studyAccordion">
            <div class="accordion-body">
                @include('study.summary.partials.gonadectomy-procedures')
            </div>
        </div>
    </div>
    @endif


    {{-- other interventions --}}
    @if ( $study->otherInterventionPlans()->count() > 0 )
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingOtherInterventions">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOtherInterventions" aria-expanded="true" aria-controls="collapseOtherInterventions">
                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                    <span class="fs-5">Interventions</span>
                    @if ($showEditButton)
                        <a href="{{ route('study.animal-experimentation.other-intervention', $study) }}"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="tooltip"
                            title="Edit Other Interventions"
                            onclick="event.stopPropagation()">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endif
                </div>
            </button>
        </h2>
        <div id="collapseOtherInterventions" class="accordion-collapse collapse show" aria-labelledby="headingOtherInterventions" data-bs-parent="#studyAccordion">
            <div class="accordion-body">
                @include('study.summary.partials.other-interventions')
            </div>
        </div>
    </div>
    @endif


    {{-- Sample Groups --}}
    @if ( $study->sampleGroups()->count() > 0 )
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingSampleGroups">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSampleGroups" aria-expanded="true" aria-controls="collapseSampleGroups">
                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                    <span class="fs-5">Sample Groups</span>
                    @if ($showEditButton)
                        <a href="{{ route('study.sample-groups', $study) }}"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="tooltip"
                            title="Edit Sample Groups"
                            onclick="event.stopPropagation()">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endif
                </div>
            </button>
        </h2>
        <div id="collapseSampleGroups" class="accordion-collapse collapse show" aria-labelledby="headingSampleGroups" data-bs-parent="#studyAccordion">
            <div class="accordion-body">
                @include('study.sample-groups.partials.tree', ['study' => $study, 'viewonly' => true])
            </div>
        </div>
    </div>
    @endif


    {{-- Comparisons --}}
    @if ( $study->comparisonGroups()->count() > 0 )
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingComparisons">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseComparisons" aria-expanded="true" aria-controls="collapseComparisons">
                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                    <span class="fs-5">Comparisons</span>
                    @if ($showEditButton)
                        <a href="{{ route('study.comparison-groups.index', $study) }}"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="tooltip"
                            title="Edit Comparison Groups"
                            onclick="event.stopPropagation()">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endif
                </div>
            </button>
        </h2>
        <div id="collapseComparisons" class="accordion-collapse collapse show" aria-labelledby="headingComparisons" data-bs-parent="#studyAccordion">
            <div class="accordion-body">
                @include('study.comparison-groups.partials.comparison-table', ['study' => $study, 'viewonly' => true])
            </div>
        </div>
    </div>
    @endif

    {{-- Analysis Metadata --}}
    @if ( $study->analysisUploads()->count() > 0 )
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingAnalysisMetadata">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAnalysisMetadata" aria-expanded="true" aria-controls="collapseAnalysisMetadata">
                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                    <span class="fs-5">Analysis Metadata</span>
                    @if ($showEditButton)
                        <a href="{{ route('study.phenotype-analysis', $study) }}"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="tooltip"
                            title="Edit Analysis Metadata"
                            onclick="event.stopPropagation()">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endif
                </div>
            </button>
        </h2>
        <div id="collapseAnalysisMetadata" class="accordion-collapse collapse show" aria-labelledby="headingAnalysisMetadata" data-bs-parent="#studyAccordion">
            <div class="accordion-body">
                @include('study.summary.partials.analysis-metadata')
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
