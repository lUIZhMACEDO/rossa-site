<x-layouts.sidebar :$study>
    <x-slot:header>
        <x-study-share :study="$study" />
    </x-slot:header>
    <x-slot:sidebar>
        <div class="sidebar-nav ps-0 pe-0 pt-0">
            <nav class="tree-nav">
                {{-- <div class="tree-nav-group">
                    <button @class(["tree-nav-group-toggle", "active" => Route::is('study.study-information')]) type="button" data-bs-toggle="collapse" data-bs-target="#studyInformationCollapse" aria-expanded="true" aria-controls="studyInformationCollapse">Study Information</button>
                    <div id="studyInformationCollapse" class="collapse show tree-nav-group-content">
                        <a href="{{ route('study.study-information', $study) }}" @class(['tree-nav-link', 'active' => Route::is('study.study-information')])><span>Study Information</span></a>
                        <a href="{{ route('study.study-information', $study) }}" @class(['tree-nav-link', 'active' => Route::is('study.publication-information')])><span>Publication Information</span></a>
                    </div>
                </div> --}}

                @if (!$study->isLockedForEditing(auth()->user()) || auth()->user()->hasPermission('manage-studies'))
                    <div class="tree-nav-group">
                        <a @class(["tree-nav-group-toggle", "active" => Route::is('study.study-information')]) href="{{ route('study.study-information', $study) }}">Study Information
                            @if ($study->progressStudyInformation() === 2)
                                <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                            @elseif ($study->progressStudyInformation() === 1)
                                <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                            @else
                                <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                            @endif
                        </a>
                    </div>
                    <div class="tree-nav-group">
                        <a @class(["tree-nav-group-toggle", "active" => Route::is('study.subject-areas')]) href="{{ route('study.subject-areas', $study) }}">
                            Subject Areas
                            @if ($study->progressSubjectAreas() === 2)
                                <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                            @elseif ($study->progressSubjectAreas() === 1)
                                <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                            @else
                                <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                            @endif
                        </a>
                    </div>
                    <div class="tree-nav-group">
                        <a @class(["tree-nav-group-toggle", "active" => Route::is('study.investigators')]) href="{{ route('study.investigators', $study) }}">Investigators
                            @if ($study->progressInvestigators() === 2)
                                <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                            @elseif ($study->progressInvestigators() === 1)
                                <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                            @else
                                <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                            @endif
                        </a>
                    </div>
                   
                    <div class="tree-nav-group">
                       <button @class(["tree-nav-group-toggle", "active" => Route::is('study.animal-experimentation*')]) type="button">Animal Experimentation
                            @if ( $study->progressAllAnimalExperimentation() === 2)
                                <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                            @elseif ( $study->progressAllAnimalExperimentation() === 1)
                                <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                            @else
                                <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                            @endif
                        </button>
                        <div class="tree-nav-group-content">

                            <a href="{{ route('study.animal-experimentation.setup', $study) }}" @class(['tree-nav-link', 'active' => Route::is('study.animal-experimentation.setup*')])><span>Setup
                                @if ($study->progressAnimalExperimentation() === 2)
                                    <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                                @elseif ($study->progressAnimalExperimentation() === 1)
                                    <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                @else
                                    <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                                @endif
                                </span>
                            </a>
                            <a href="{{ route('study.animal-experimentation.animal-models', $study) }}" @class(['tree-nav-link', 'active' => Route::is('study.animal-experimentation.animal-models*')])><span>Animal Models
                                @if ($study->progressAnimalModels() === 2)
                                    <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                                @elseif ($study->progressAnimalModels() === 1)
                                    <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                @else
                                    <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                                @endif
                                </span>
                            </a>

                            @if ($study->performs_drug_treatment)
                                <a href="{{ route('study.animal-experimentation.drug-treatment', $study) }}" @class(['tree-nav-link', 'active' => Route::is('study.animal-experimentation.drug-treatment*')])><span>Drug Treatments
                                    @if ($study->progressDrugTreatments() === 2)
                                        <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                                    @elseif ($study->progressDrugTreatments() === 1)
                                        <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                    @else
                                        <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                                    @endif
                                    </span>
                                </a>
                            @else
                                <span class="tree-nav-link disabled"><span>Drug Treatments</span></span>
                            @endif
                            @if ($study->performs_gonadectomy)
                                <a href="{{ route('study.animal-experimentation.gonadectomy-procedure', $study) }}" @class(['tree-nav-link', 'active' => Route::is('study.animal-experimentation.gonadectomy-procedure*')])><span>Gonadectomy Procedure
                                    @if ($study->progressGonadectomyProcedures() === 2)
                                        <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                                    @elseif ($study->progressGonadectomyProcedures() === 1)
                                        <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                    @else
                                        <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                                    @endif
                                    </span>
                                </a>
                            @else
                                <span class="tree-nav-link disabled"><span>Gonadectomy Procedure</span></span>
                            @endif
                            @if ($study->performs_other_intervention)
                                <a href="{{ route('study.animal-experimentation.other-intervention', $study) }}" @class(['tree-nav-link', 'active' => Route::is('study.animal-experimentation.other-intervention*')])><span>Other Interventions
                                    @if ($study->progressOtherInterventions() === 2)
                                        <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                                    @elseif ($study->progressOtherInterventions() === 1)
                                        <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                    @else
                                        <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                                    @endif
                                    </span>
                                </a>
                            @else
                                <span class="tree-nav-link disabled"><span>Other Interventions</span></span>
                            @endif
                        </div>
                    </div>

                    <div class="tree-nav-group">
                        <button @class(["tree-nav-group-toggle", "active" => Route::is('study.phenotype-analysis*', 'study.comparison-groups*', 'study.sample-groups*', 'study.statistical-analysis*', 'study.scan-datasets*')]) type="button">Phenotype Analysis
                            @if( $study->progressAllPhenotypeAnalysis() === 2)
                                <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                            @elseif( $study->progressAllPhenotypeAnalysis() === 1)
                                <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                            @else
                                <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                            @endif
                        </button>
                        <div class="tree-nav-group-content">
                            <a href="{{ route('study.sample-groups', $study) }}" @class(['tree-nav-link', 'active' => Route::is('study.sample-groups*')])><span>Sample Groups
                                @if ($study->progressSampleGroups() === 2)
                                    <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                                @elseif ($study->progressSampleGroups() === 1)
                                    <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                @else
                                    <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                                @endif    
                            </span></a>
                            <a href="{{ route('study.comparison-groups.index', $study) }}" @class(['tree-nav-link', 'active' => Route::is('study.comparison-groups*')])><span>Comparison Groups
                                @if ($study->progressComparisonGroups() === 2)
                                    <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                                @elseif ($study->progressComparisonGroups() === 1)
                                    <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                @else
                                    <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                                @endif
                            </span></a>
                            <a href="{{ route('study.phenotype-analysis', $study) }}" @class(['tree-nav-link', 'active' => Route::is('study.phenotype-analysis*')])><span>Upload Spreadsheet Data
                                @if ($study->progressAnalysisUploads() === 2)
                                    <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                                @elseif ($study->progressAnalysisUploads() === 1)
                                    <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                @else
                                    <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                                @endif    
                            </span></a>
                            <a href="{{ route('study.scan-datasets.index', $study) }}" @class(['tree-nav-link', 'active' => Route::is('study.scan-datasets*')])><span>Upload Image Data
                                @if ($study->progressImageData() === 2)
                                    <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i></span>
                                @elseif ($study->progressImageData() === 1)
                                    <span class="ms-2 text-darkyellow"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                @else
                                    <span class="ms-2 text-secondary"><i class="bi bi-check-circle"></i></span>
                                @endif
                            </span></a>
                        </div>
                    </div>
                @endif

                <div class="tree-nav-group">
                    {{-- <a @class(["tree-nav-group-toggle", "active" => Route::is('study.summary')]) href="{{ route('study.summary', $study) }}">Summary</a> --}}
                    <a @class(["tree-nav-group-toggle", "active" => Route::is('study.summary*')]) href="{{ route('study.summary', $study) }}">Report</a>
                </div>

                <div class="tree-nav-group">
                    <a @class(["tree-nav-group-toggle", "active" => Route::is('study.submission')]) href="{{ route('study.submission', $study) }}">Submission</a>
                </div>
            </nav>
        </div>
    </x-slot:sidebar>


    <div class="px-2 pt-0 position-relative">
         @if($study->isLockedForEditing(auth()->user()))
            <div class="alert alert-warning small mt-2" style="margin-bottom: 0px!important;">
                <i class="bi bi-lock me-1"></i>
                This study is currently {{ strtolower($study->status->label()) }} and all edit forms are read-only.
            </div>
        @endif
        {{ $slot }}
    </div>



<style>
.sidebar-header {
    background: #0D3B52;
    padding: 10px 16px 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.sidebar-header-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    color: #FFFFFF;
    text-transform: uppercase;
    margin: 0 0 4px;
}

.sidebar-header-study {
    font-size: 12px;
    color: #7FBCD4;
    margin: 0;
}

.sidebar-header-help-btn {
    font-weight: 600;
    white-space: nowrap;
}

.tree-nav-group {
    margin-bottom: 0;
    border-bottom: 2px solid #D0D8E0;
}

.tree-nav-group-toggle {
    border: none;
    display: block;
    text-align: left;
    font-weight: 600;
    width: 100%;
    padding: 10px 16px;
    cursor: pointer;
    border-radius: 0;
    position: relative;
    background-color: transparent;
    z-index: 1;
    color: #333333;
    text-decoration: none;
    font-size: 14px;
}

.tree-nav-group-toggle:hover {
    color: #1A7DB5;
    background-color: #EEF6FC;
}

.tree-nav-group-toggle.active {
    color: #FFFFFF;
    background-color: #1A7DB5;
}
.tree-nav-group-toggle.active > span {
    color: #EEF6FC!important;
}

.tree-nav-group-toggle::after,
a.tree-nav-group-toggle::after,
button.tree-nav-group-toggle::after {
    content: none;
}

.tree-nav-link {
    position: relative;
    display: block;
    padding-left: 20px;
    margin-left: 20px;
    margin-bottom: 0;
    color: #555555;
    text-decoration: none;
    padding-top: 2px;
    padding-bottom: 2px;
}

.tree-nav-group-content > .tree-nav-link:not(:last-child) {
    margin-bottom: 3px;
}

.tree-nav-link::before,
.tree-nav-link::after {
    content: none;
}

.tree-nav-link > span {
    display: block;
    border-left: 5px solid #1A7DB5;
    border-radius: 0;
    padding: 6px 16px 8px 20px;
    font-size: 13px;
    color: #333333;
    font-weight: 600;
}

.tree-nav-link:hover > span {
    background-color: #EEF6FC;
    color: #1A7DB5;
}

.tree-nav-link.active > span {
    background-color: #EEF6FC;
    color: #1A7DB5;
    font-weight: 600;
}

.tree-nav-link.disabled {
    opacity: 0.35;
    pointer-events: none;
    cursor: default;
}

.tree-nav-group-content {
    margin-bottom: 0;
    margin-left: 0;
    background-color: #FFFFFF;
    border-left: none;
    padding-bottom: 0;
}

</style>

<x-guide-modal />
</x-layouts.sidebar>
