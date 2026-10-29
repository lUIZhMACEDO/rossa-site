<div class="card" x-data="pageTabManager()" x-init="init()">
    <div class="card-header pb-0">
        <!-- tab navigation -->
        <ul class="nav nav-tabs border-0" id="summaryTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button id="basic-info-tab"
                    class="nav-link"
                    :class="{ 'active': activeTab === 'basic-info' }"
                    type="button"
                    role="tab"
                    aria-controls="basic-info"
                    :aria-selected="activeTab === 'basic-info'"
                    x-on:click="switchTab('basic-info')">
                    Information
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button id="data-exploration-tab"
                    class="nav-link"
                    :class="{ 'active': activeTab === 'data-exploration' }"
                    type="button"
                    role="tab"
                    aria-controls="data-exploration"
                    :aria-selected="activeTab === 'data-exploration'"
                    x-on:click="switchTab('data-exploration')">
                    Data Exploration
            </button>
            </li>
            <li class="nav-item" role="presentation">
                <button id="data-download-tab"
                    class="nav-link"
                    :class="{ 'active': activeTab === 'data-download' }"
                    type="button"
                    role="tab"
                    aria-controls="data-download"
                    :aria-selected="activeTab === 'data-download'"
                    x-on:click="switchTab('data-download')">
                    Download Original Data
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <!-- tab content -->
        <div class="tab-content" id="summaryTabContent">
            <!-- tab 1: basic information -->
            <div class="tab-pane fade"
                :class="{ 'show active': activeTab === 'basic-info' }"
                id="basic-info"
                role="tabpanel"
                aria-labelledby="basic-info-tab"
                x-show="activeTab === 'basic-info'"
                x-transition>
                <div class="accordion" id="studyAccordion" style="--bs-accordion-active-bg: var(--bs-gray-300);">
                    {{-- study information --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingStudyInfo">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudyInfo" aria-expanded="true" aria-controls="collapseStudyInfo">
                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                    <span class="fs-5">Study Information</span>
                                    <a href="{{ route('study.study-information', $study) }}"
                                        class="btn btn-primary btn-sm"
                                        data-bs-toggle="tooltip"
                                        title="Edit Study Information"
                                        onclick="event.stopPropagation()">
                                        <i class="bi bi-pencil"></i>
                                    </a>
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
                                        <a href="{{ route('study.subject-areas', $study) }}"
                                            class="btn btn-primary btn-sm"
                                            data-bs-toggle="tooltip"
                                            title="Edit Subject Areas"
                                            onclick="event.stopPropagation()">
                                            <i class="bi bi-pencil"></i>
                                        </a>
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
                                    <a href="{{ route('study.investigators', $study) }}"
                                        class="btn btn-primary btn-sm"
                                        data-bs-toggle="tooltip"
                                        title="Edit Investigators"
                                        onclick="event.stopPropagation()">
                                        <i class="bi bi-pencil"></i>
                                    </a>
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
                                    <a href="{{ route('study.animal-experimentation.animal-models', $study) }}"
                                        class="btn btn-primary btn-sm"
                                        data-bs-toggle="tooltip"
                                        title="Edit Animal Models"
                                        onclick="event.stopPropagation()">
                                        <i class="bi bi-pencil"></i>
                                    </a>
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
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingDrugTreatments">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDrugTreatments" aria-expanded="true" aria-controls="collapseDrugTreatments">
                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                    <span class="fs-5">Drug Treatments</span>
                                    <a href="{{ route('study.animal-experimentation.drug-treatment', $study) }}"
                                        class="btn btn-primary btn-sm"
                                        data-bs-toggle="tooltip"
                                        title="Edit Drug Treatments"
                                        onclick="event.stopPropagation()">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </button>
                        </h2>
                        <div id="collapseDrugTreatments" class="accordion-collapse collapse show" aria-labelledby="headingDrugTreatments" data-bs-parent="#studyAccordion">
                            <div class="accordion-body">
                                @include('study.summary.partials.drug-treatments')
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- tab 2: tabular data --}}
            <div class="tab-pane fade"
                :class="{ 'show active': activeTab === 'data-exploration' }"
                id="data-exploration"
                role="tabpanel"
                aria-labelledby="data-exploration-tab"
                x-show="activeTab === 'data-exploration'"
                x-transition>
                <template x-if="tabsLoaded.exploration">
                    @livewire('data-exploration', ['study' => $study], key('data-exploration-' . $study->id))
                </template>
                <template x-if="!tabsLoaded.exploration">
                    <x-loading-skeleton message="Loading data exploration..." />
                </template>
            </div>

            {{-- tab 3: plotted data --}}
            <div class="tab-pane fade"
                :class="{ 'show active': activeTab === 'data-download' }"
                id="data-download"
                role="tabpanel"
                aria-labelledby="data-download-tab"
                x-show="activeTab === 'data-download'"
                x-transition>
                @php
                $uploads = $study->analysisUploads()
                    ->with('measurements')
                    ->get();
                @endphp
                @foreach($uploads as $upload)
                    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 p-3 mb-3 border rounded bg-light">
                        <div class="w-100" style="min-width: 0;">
                            <h6 class="mb-1 text-break">{{ $upload->original_filename }}</h6>
                            @if($upload->analysis_type)
                                <small class="text-muted">{{ ucfirst($upload->analysis_type) }} Data</small>
                            @endif
                            <div class="mt-2">
                                <span class="badge bg-secondary me-1">{{ $upload->analysisType->name }}</span>
                                <span class="badge bg-secondary me-1">{{ App\Models\SpecimenType::find($upload->specimen_type_id)->name }}</span>
                                <span class="badge bg-secondary me-1">{{ $upload->region->label ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <form action="{{ route('upload.download', $upload) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-download me-1"></i>
                                Download
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function pageTabManager() {
    return {
        activeTab: 'basic-info',
        tabsLoaded: {
            info: true, //info tab always loaded
            exploration: false,
            download: false
        },

        init() {
            // read from URL hash first
            const hash = window.location.hash.replace('#', '');
            const validTabs = ['basic-info', 'data-exploration', 'data-download'];

            if (validTabs.includes(hash)) {
                this.activeTab = hash;
            } else {
                // fallback to localStorage
                const savedTab = localStorage.getItem('study-active-tab');
                if (savedTab && validTabs.includes(savedTab)) {
                    this.activeTab = savedTab;
                }
            }

            // mark initial tab as loaded
            this.markTabLoaded(this.activeTab);

            // listen for browser back/forward
            window.addEventListener('hashchange', () => {
                const newHash = window.location.hash.replace('#', '');
                if (validTabs.includes(newHash)) {
                    this.activeTab = newHash;
                    this.markTabLoaded(newHash);
                }
            });

            // resize Plotly plots when tabs are shown
            this.$watch('activeTab', (newTab) => {
                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                }, 100);
            });
        },

        switchTab(tabId) {
            this.activeTab = tabId;
            this.markTabLoaded(tabId);

            // update URL hash
            window.location.hash = tabId;

            // save to localStorage
            localStorage.setItem('study-active-tab', tabId);
        },

        markTabLoaded(tabId) {
            if (tabId === 'basic-info') {
                this.tabsLoaded.info = true;
            } else if (tabId === 'data-exploration') {
                this.tabsLoaded.exploration = true;
            } else if (tabId === 'data-download') {
                this.tabsLoaded.download = true;
            }
        }
    };
}
</script>
