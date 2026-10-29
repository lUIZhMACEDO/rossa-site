<div>
    @if($this->studyFigures->isNotEmpty())
        <div class="d-flex justify-content-end mb-3">
            <button type="button"
                    class="btn btn-outline-secondary btn-sm"
                    onclick="document.getElementById('representative-figures')?.scrollIntoView({ behavior: 'smooth', block: 'start' })">
                <i class="bi bi-images"></i> View representative figures
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card report-section mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Study Datasets</h5>
                </div>
                <div class="card-body">
                    @if(empty($this->availableContexts))
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> No datasets available for this study yet.
                        </div>
                    @else
                        @foreach($this->availableContexts as $contextData)
                            <div class="py-2 {{ !$loop->first ? 'border-top' : '' }}" 
                                style="cursor: pointer; transition: background-color 0.15s ease-in-out;"
                                onmouseover="this.style.backgroundColor='rgba(0,0,0,0.05)'"
                                onmouseout="this.style.backgroundColor='transparent'"
                                onclick="window.__dePerfUI && window.__dePerfUI.start('context_select', { source: 'dataset_row_click' })"
                                wire:click="$set('selectedContext', '{{ $contextData['contextJson'] }}')">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="contextSelection" 
                                            id="context_{{ $loop->index }}"
                                            value="{{ $contextData['contextJson'] }}"
                                            wire:model.live="selectedContext">
                                        <label class="form-check-label" for="context_{{ $loop->index }}"></label>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($contextData['incompleteCount'] > 0)
                                                <i class="bi bi-exclamation-triangle-fill text-warning"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Incomplete uploads"></i>
                                            @endif
                                            <strong>{{ $contextData['upload']->analysisType->name ?? '-' }}</strong>
                                            @if($contextData['completeCount'] >= 1)
                                                <span class="badge bg-primary">{{ $contextData['completeCount'] }} uploads</span>
                                            @endif
                                        </div>
                                       @if($contextData['description'])
                                            <small class="text-muted">
                                                @foreach($contextData['contextParts'] as $part)
                                                    <span class="me-3"><strong>{{ $part['label'] }}:</strong> {{ $part['value'] }}</span>
                                                @endforeach
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
                <div class="card report-section mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Sample Groups</h5>
                </div>
                <div class="card-body">
                    @if($study->sampleGroups->count() > 0)
                        @foreach($study->sampleGroups as $group)
                            <div class="py-2 {{ !$loop->first ? 'border-top' : '' }}">
                                <div class="d-flex align-items-center">
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
                                            $drugCount = count($drugNames);
                                        @endphp
                                        • <strong>DT:</strong> {{ implode(', ', $drugNames) }}
                                    @endif
                                </small>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> No sample groups available.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <hr>

    @if($selectedContext)
        @if($this->filteredMeasurements->count() > 0)
            @if($this->filterContext)
                @php
                    $comparisonGroups = $this->comparisonGroups;
                    $activeComparisonGroupId = $activeComparisonGroupId ?? $comparisonGroups->first()?->id;
                    $activeComparisonGroup = $comparisonGroups->firstWhere('id', $activeComparisonGroupId) ?? $comparisonGroups->first();
                @endphp
                    @if($comparisonGroups->isEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No comparison groups with completed statistics available for this study.
                        </div>
                    @else
                        @php
                            $contextUpload = $this->filteredAnalysisUploads->first();
                        @endphp
                        @if($contextUpload)
                            <p class="fs-5 mb-2">
                                @foreach($contextUpload->getContextParts(withAnalysisType: true) as $part)
                                    <span class="me-3"><strong>{{ $part['label'] }}:</strong> {{ $part['value'] }}</span>
                                @endforeach
                            </p>
                        @endif
                        <!-- Toolbar: Data Type Switcher + Comparison Group Tabs -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <!-- Comparison Group Tabs -->
                            <ul class="nav nav-tabs cg-tabs mb-0" role="tablist">
                                @foreach($comparisonGroups as $index => $cg)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link @if($activeComparisonGroup && $activeComparisonGroup->id === $cg->id) active @endif"
                                            onclick="window.__dePerfUI && window.__dePerfUI.start('comparison_group_switch', { comparison_group_id: {{ $cg->id }} })"
                                            wire:click="setActiveComparisonGroup({{ $cg->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="setActiveComparisonGroup"
                                            type="button" 
                                            role="tab" 
                                            aria-selected="{{ $activeComparisonGroup && $activeComparisonGroup->id === $cg->id ? 'true' : 'false' }}">
                                            {{ $cg->sampleGroups()->map(fn($sg) => $sg->label)->join(' vs ') }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <!-- Data Type Switcher + Export -->
                            <div class="d-flex align-items-center gap-2">
                                @if($dataType === 'groupStats' && $activeComparisonGroup)
                                    <a href="{{ route('study.export.stats-csv', [
                                        'study' => $study,
                                        'comparison_group_id' => $activeComparisonGroup->id,
                                        'context' => $selectedContext,
                                    ]) }}" 
                                       class="btn btn-outline-secondary btn-sm"
                                       title="Download statistics as CSV">
                                        <i class="bi bi-download"></i> Download CSV
                                    </a>
                                @endif
                                <div class="btn-group btn-group-sm" role="group" aria-label="Data view type">
                                    <button type="button" 
                                        class="btn btn-outline-primary {{ $dataType === 'groupStats' ? 'active' : '' }}"
                                        wire:click="$set('dataType', 'groupStats')">
                                        <i class="bi bi-table me-1"></i> Statistics
                                    </button>
                                    <button type="button" 
                                        class="btn btn-outline-primary {{ $dataType === 'boxPlots' ? 'active' : '' }}"
                                        wire:click="$set('dataType', 'boxPlots')">
                                        <i class="bi bi-bar-chart me-1"></i> Box Plots
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Content Card -->
                        <div class="card mb-4" style="border-left: 4px solid var(--bs-primary);">
                            <div class="card-body">
                                @if($activeComparisonGroup)
                                    @if ($dataType === 'groupStats')
                                        @include('study.summary.partials.cg-stats-table', [
                                            'comparisonGroup' => $activeComparisonGroup,
                                            'analysisUploadIds' => $this->filteredAnalysisUploads->pluck('id'),
                                            'filterContext' => $this->filterContext,
                                            'study' => $study,
                                            'publicAccess' => $publicAccess,
                                            'modality' => $contextUpload?->analysisType?->name,
                                        ])
                                    @elseif ($dataType === 'boxPlots')
                                        @include('study.summary.partials.cg-box-plots', [
                                            'comparisonGroup' => $activeComparisonGroup,
                                            'analysisUploadIds' => $this->filteredAnalysisUploads->pluck('id'),
                                            'measurements' => $this->filteredMeasurements,
                                            'study' => $study,
                                            'publicAccess' => $publicAccess,
                                            'modality' => $contextUpload?->analysisType?->name,
                                        ])
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Unable to load statistics for this dataset.
                </div>
            @endif
        @else
            <div class="text-center text-muted py-4">
                <i class="bi bi-database fs-1"></i>
                <p class="mt-3">No measurements found for this dataset. Return to the <a href="{{ route('study.phenotype-analysis', ['study' => $study->id]) }}">Upload Data</a> page to complete your uploads.</p>
            </div>
        @endif
    @endif

    {{-- Measurement Modal - child component --}}
    @php
        $modalComparisonGroupId = $activeComparisonGroupId ?? $this->comparisonGroups->first()?->id;
        $modalKey = 'modal-' . $modalComparisonGroupId . '-' . md5((string) $selectedContext);
    @endphp
    @if($modalComparisonGroupId && $this->filterContext)
        <livewire:measurement-modal
            :comparison-group-id="$modalComparisonGroupId"
            :analysis-upload-ids="$this->filteredAnalysisUploads->pluck('id')"
            :filter-context="$this->filterContext"
            :key="$modalKey"
        />
    @endif

    @if($this->studyFigures->isNotEmpty())
        <div class="card report-section mb-0" id="representative-figures">
            <div class="card-header">
                <h5 class="card-title mb-0">Representative Figures</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @foreach($this->studyFigures as $figure)
                        <div class="col-md-6 col-lg-4">
                            <figure class="mb-0">
                                <img src="{{ route($publicAccess ? 'search.figures.show' : 'study.figures.show', [$study, $figure]) }}"
                                     alt="{{ $figure->caption }}"
                                     class="img-fluid rounded border w-100 figure-preview-thumb"
                                     style="max-height: 16rem; object-fit: contain; cursor: zoom-in; background: #f8f9fa;"
                                     role="button"
                                     tabindex="0"
                                     title="Click to view full screen"
                                     data-bs-toggle="modal"
                                     data-bs-target="#figureFullscreenModal"
                                     data-figure-src="{{ route($publicAccess ? 'search.figures.show' : 'study.figures.show', [$study, $figure]) }}"
                                     data-figure-caption="{{ $figure->caption }}">
                                <figcaption class="small mt-2" style="color: #2C2C2A;">
                                    {{ $figure->caption }}
                                </figcaption>
                            </figure>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="modal fade" id="figureFullscreenModal" tabindex="-1" aria-labelledby="figureFullscreenCaption" aria-hidden="true" wire:ignore>
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content bg-dark">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-white text-truncate me-3" id="figureFullscreenCaption"></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex align-items-center justify-content-center p-2 p-md-4">
                        <img id="figureFullscreenImg"
                             src=""
                             alt=""
                             class="img-fluid"
                             style="max-height: calc(100vh - 5rem); max-width: 100%; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    (() => {
        const modal = document.getElementById('figureFullscreenModal');
        if (!modal || modal.dataset.figureModalBound) return;
        modal.dataset.figureModalBound = '1';

        const img = document.getElementById('figureFullscreenImg');
        const caption = document.getElementById('figureFullscreenCaption');

        modal.addEventListener('show.bs.modal', (event) => {
            const thumb = event.relatedTarget;
            if (!thumb) return;
            img.src = thumb.dataset.figureSrc || thumb.src;
            img.alt = thumb.dataset.figureCaption || thumb.alt || '';
            caption.textContent = thumb.dataset.figureCaption || '';
        });

        modal.addEventListener('hidden.bs.modal', () => {
            img.src = '';
            img.alt = '';
            caption.textContent = '';
        });

        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            const thumb = e.target.closest?.('.figure-preview-thumb');
            if (!thumb) return;
            e.preventDefault();
            thumb.click();
        });
    })();
</script>
@endscript

@if(env('DATA_EXPLORATION_PROFILING', false))
    <script>
        window.__dePerfUI = window.__dePerfUI || {
            marks: {},
            start(name, meta = {}) {
                this.marks[name] = { t: performance.now(), meta };
            },
            end(name, meta = {}) {
                const mark = this.marks[name];
                if (!mark) return;
                const duration = performance.now() - mark.t;
                const payload = {
                    event: name,
                    duration_ms: Math.round(duration * 100) / 100,
                    ...mark.meta,
                    ...meta,
                };
                console.info('[DE_PERF_UI]', payload);
                delete this.marks[name];
            }
        };

        document.addEventListener('livewire:init', () => {
            if (window.__dePerfUIHooked) {
                return;
            }
            window.__dePerfUIHooked = true;

            Livewire.hook('request', ({ respond, succeed, fail }) => {
                const pending = { t: performance.now() };

                respond(() => {
                    const duration = performance.now() - pending.t;
                    console.info('[DE_PERF_UI]', {
                        event: 'livewire_request',
                        duration_ms: Math.round(duration * 100) / 100,
                    });
                });

                succeed(() => {
                    if (window.__dePerfUI) {
                        window.__dePerfUI.end('context_select');
                        window.__dePerfUI.end('comparison_group_switch');
                    }
                });

                fail(() => {
                    if (window.__dePerfUI) {
                        window.__dePerfUI.end('context_select', { failed: true });
                        window.__dePerfUI.end('comparison_group_switch', { failed: true });
                    }
                });
            });
        });
    </script>
@endif
