@php
    $modalPayload = $this->modalPayload;
    $hasModalPayload = $modalPayload !== null && empty($modalPayload['error']);
    $modalMeasurement = $modalPayload['measurement'] ?? null;
    $modalTableData = $modalPayload['tableData'] ?? [];
    $modalSampleGroups = $modalPayload['sampleGroups'] ?? [];
    $modalAdditionalSampleGroups = $modalPayload['additionalSampleGroups'] ?? [];
    $modalContextDescription = $modalPayload['contextDescription'] ?? null;
    $modalContextDescriptionSlug = $modalPayload['contextDescriptionSlug'] ?? null;
    $cgMeasurements = $this->cgMeasurements;
@endphp

<div x-data="{
        measurementId: @entangle('measurementId').live,
        measurements: @entangle('cgMeasurements').live,
        isModalVisible: false,
        modalViewMode: 'plot',
        selectedAdditionalSampleGroupIds: [],
        _modalInstance: null,
        _eventCleanups: [],
        
        init() {
            this.$nextTick(() => {
                const modalEl = this.$refs.measurementModal;
                if (!modalEl) {
                    console.warn('MeasurementModal: modalEl ref not found');
                    return;
                }

                const existingModal = bootstrap.Modal.getInstance(modalEl);
                if (existingModal && modalEl.classList.contains('show')) {
                    this._modalInstance = existingModal;
                    this.isModalVisible = true;
                } else {
                    if (existingModal) {
                        existingModal.dispose();
                    }
                    this._modalInstance = new bootstrap.Modal(modalEl);
                }
                
                modalEl.addEventListener('hidden.bs.modal', () => {
                    this.isModalVisible = false;
                    this.modalViewMode = 'plot';
                    this.selectedAdditionalSampleGroupIds = [];
                    @this.close();
                });

                this._eventCleanups.forEach((cleanup) => cleanup());
                this._eventCleanups = [
                    Livewire.on('openMeasurementModal', () => this.showModal()),
                    Livewire.on('show-measurement-modal', () => this.showModal()),
                ];
            });
        },

        showModal() {
            if (!this._modalInstance) {
                return;
            }
            if (!this.isModalVisible) {
                this._modalInstance.show();
                this.isModalVisible = true;
            }
        },
        
        destroy() {
            this._eventCleanups.forEach((cleanup) => cleanup());
            this._eventCleanups = [];
            if (this._modalInstance && !this.isModalVisible) {
                this._modalInstance.dispose();
                this._modalInstance = null;
            }
            if (!document.querySelector('.modal.show')) {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }
        },
        
        getCurrentIndex() {
            if (!this.measurements || !Array.isArray(this.measurements) || this.measurements.length === 0) {
                return 0;
            }
            const idx = this.measurements.findIndex(m => m.id === this.measurementId);
            return idx >= 0 ? idx : 0;
        },
        
        canGoPrev() {
            return this.getCurrentIndex() > 0;
        },
        
        canGoNext() {
            if (!this.measurements || !Array.isArray(this.measurements)) return false;
            return this.getCurrentIndex() < this.measurements.length - 1;
        },
        
        nextMeasurement() {
            if (this.canGoNext()) {
                this.modalViewMode = 'plot';
                this.selectedAdditionalSampleGroupIds = [];
                @this.next();
            }
        },
        
        prevMeasurement() {
            if (this.canGoPrev()) {
                this.modalViewMode = 'plot';
                this.selectedAdditionalSampleGroupIds = [];
                @this.prev();
            }
        },
        
        downloadCSV() {
            const modal = this.$refs.measurementModal;
            const visibleTable = modal.querySelector('table');
            
            if (!visibleTable) {
                console.error('No table found');
                return;
            }
            
            const headers = Array.from(visibleTable.querySelectorAll('thead th'))
                .map(th => th.textContent.trim());
            
            const tbody = visibleTable.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'))
                .map(tr => {
                    return Array.from(tr.querySelectorAll('td'))
                        .map(td => {
                            let text = td.textContent.trim();
                            if (text.includes(',')) {
                                text = String.fromCharCode(34) + text + String.fromCharCode(34);
                            }
                            return text;
                        });
                });
            
            let csvContent = headers.join(',') + '\r\n';
            rows.forEach(row => {
                csvContent += row.join(',') + '\r\n';
            });
            
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            
            const measurement = this.measurements[this.getCurrentIndex()];
            const measurementName = measurement ? (measurement.display_name || measurement.name || 'data') : 'data';
            const measurementSlug = measurementName.replace(/[^a-z0-9]/gi, '_').toLowerCase();
            const contextSlug = modal.querySelector('[data-context-slug]')?.getAttribute('data-context-slug') || '';
            
            const filenameParts = [contextSlug, measurementSlug].filter(part => part);
            const filename = filenameParts.join('_') + '.csv';
            
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }
    }"
>
    <div class="modal fade" 
        id="measurementModal"
        x-ref="measurementModal" 
        tabindex="-1" 
        aria-hidden="true"
        wire:ignore.self
    >
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content position-relative">
                <div
                    wire:loading.flex
                    wire:target="open,next,prev"
                    class="position-absolute top-0 start-0 w-100 h-100 align-items-center justify-content-center bg-white bg-opacity-75"
                    style="z-index: 5;"
                >
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                @if($hasModalPayload)
                    <div class="d-none" data-context-slug="{{ $modalContextDescriptionSlug }}"></div>
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">
                                {{ $modalMeasurement['display_name'] }}
                                @if($modalMeasurement['unit'])
                                    <span class="text-muted">({{ $modalMeasurement['unit'] }})</span>
                                @endif
                            </h5>
                            @if($modalContextDescription)
                                <small class="text-muted">{{ $modalContextDescription }}</small>
                            @endif
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Plot View --}}
                        <div x-show="modalViewMode === 'plot'">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="border rounded p-2">
                                        <small class="d-block text-muted mb-2">
                                            {{ collect($modalSampleGroups)->pluck('label')->join(' vs ') }}
                                        </small>
                                        @include('study.summary.partials.measurement-plot', [
                                            'measurement' => $modalMeasurement, 
                                            'analysisUploadIds' => $analysisUploadIds,
                                            'sampleGroupIds' => $modalPayload['sampleGroupIds'],
                                            'baseSampleGroupIds' => $modalPayload['sampleGroupIds'],
                                            'extraSampleGroupIds' => collect($modalAdditionalSampleGroups)->pluck('id')->toArray(),
                                            'plotPayload' => $modalPayload['plotPayload'],
                                        ])
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Table View --}}
                        <div x-show="modalViewMode === 'table'">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="border rounded p-2">
                                        <small class="d-block text-muted mb-2">
                                            {{ collect($modalSampleGroups)->pluck('label')->join(' vs ') }}
                                        </small>
                                        <div style="max-height: 500px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.25rem;">
                                            <table class="table table-striped table-hover table-sm mb-0">
                                                <thead class="table-light" style="position: sticky; top: 0; z-index: 10;">
                                                    <tr>
                                                        <th>Sample ID</th>
                                                        <th>Sample Group</th>
                                                        <th>Value</th>
                                                        <th>Sex</th>
                                                        <th>Age</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($modalTableData as $row)
                                                        <tr>
                                                            <td>{{ $row['sample_id'] ?? 'N/A' }}</td>
                                                            <td>{{ $row['sample_group_label'] }}</td>
                                                            <td>{{ is_numeric($row['value']) ? number_format((float)$row['value'], 4) : $row['value'] }}</td>
                                                            <td>{{ $row['sex'] }}</td>
                                                            <td>{{ $row['age'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="btn-group btn-group-sm" role="group" aria-label="Navigate measurements">
                                <button type="button" class="btn btn-outline-secondary"
                                        @click="prevMeasurement()"
                                        :disabled="!canGoPrev()"
                                        title="Previous measurement">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary"
                                        @click="nextMeasurement()"
                                        :disabled="!canGoNext()"
                                        title="Next measurement">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                            <div class="btn-group btn-group-sm" role="group" aria-label="View toggle">
                                <input type="radio" class="btn-check" name="modalViewMode" id="modalViewPlot" value="plot" x-model="modalViewMode">
                                <label class="btn btn-outline-secondary" for="modalViewPlot">
                                    <i class="bi bi-bar-chart"></i> Plot
                                </label>
                                <input type="radio" class="btn-check" name="modalViewMode" id="modalViewTable" value="table" x-model="modalViewMode">
                                <label class="btn btn-outline-secondary" for="modalViewTable">
                                    <i class="bi bi-table"></i> Table
                                </label>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    x-show="modalViewMode === 'table'"
                                    @click="downloadCSV()"
                                    title="Download as CSV">
                                <i class="bi bi-download"></i> CSV
                            </button>
                        </div>
                    </div>
                @else
                    {{-- Empty state when no payload --}}
                    <div class="modal-body text-center text-muted py-5">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
