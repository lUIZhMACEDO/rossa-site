@if($upload->exists && $analysisType)
    <div class="card mb-3" style="border-left: 4px solid #0D3B52;">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap align-items-center gap-4">
                <div>
                    <small class="d-block fw-semibold" style="color: #0D3B52;">Analysis Type</small>
                    <span class="fw-semibold">{{ $analysisType->name }}</span>
                </div>

                @switch($analysisType->code)
                    @case('mechanical_testing')
                        @php
                            $details = $upload->mechanicalTestingDetails;
                        @endphp
                        @if($details)
                            <div class="vr"></div>
                            <div>
                                <small class="d-block fw-semibold" style="color: #0D3B52;">Test Method</small>
                                <span>{{ ucwords(str_replace('_', ' ', $details->test_method ?? 'Not Set')) }}</span>
                            </div>
                            <div class="vr"></div>
                            <div>
                                <small class="d-block fw-semibold" style="color: #0D3B52;">Bone</small>
                                <span>{{ ucfirst($details->bone_tested ?? 'Not Set') }}</span>
                            </div>
                            <div class="vr"></div>
                            <div>
                                <small class="d-block fw-semibold" style="color: #0D3B52;">Orientation</small>
                                <span>{{ ucwords(str_replace(['-', '_'], ' ', $details->orientation ?? 'Not Set')) }}</span>
                            </div>
                        @endif
                        @break

                    @case('clinical_chemistry')
                        <div class="vr"></div>
                        <div>
                            <small class="d-block fw-semibold" style="color: #0D3B52;">Specimen Type</small>
                            <span>{{ $upload->specimenType->name ?? 'Not Selected' }}</span>
                        </div>
                        @break

                    @case('dexa')
                    @case('micro_ct')
                    @case('bone_histo')
                        <div class="vr"></div>
                        <div>
                            <small class="d-block fw-semibold" style="color: #0D3B52;">Bone</small>
                            <span>{{ $upload->specimenType->name ?? 'Not Selected' }}</span>
                        </div>
                        @if($upload->region_id)
                            <div class="vr"></div>
                            <div>
                                <small class="d-block fw-semibold" style="color: #0D3B52;">Region</small>
                                <span>{{ $upload->region->label ?? 'Not Selected' }}</span>
                            </div>
                        @endif
                        @break

                    @default
                        @if($upload->specimenType)
                            <div class="vr"></div>
                            <div>
                                <small class="d-block fw-semibold" style="color: #0D3B52;">Specimen Type</small>
                                <span>{{ $upload->specimenType->name }}</span>
                            </div>
                        @endif
                        @if($upload->region)
                            <div class="vr"></div>
                            <div>
                                <small class="d-block fw-semibold" style="color: #0D3B52;">Region</small>
                                <span>{{ $upload->region->label }}</span>
                            </div>
                        @endif
                @endswitch

                @if($upload->path)
                    <div class="vr"></div>
                    <div>
                        <small class="d-block fw-semibold" style="color: #0D3B52;">File</small>
                        <span title="{{ $upload->original_filename }}">
                            {{ $upload->original_filename }}
                        </span>
                    </div>
                @endif

                @if($analysisType->code === 'mechanical_testing')
                    <div class="vr"></div>
                    <button class="btn btn-sm btn-link ms-auto p-0" type="button" 
                            data-input-unlock
                            data-bs-toggle="collapse" data-bs-target="#mechanicalConfigDetails"
                            aria-expanded="false" aria-controls="mechanicalConfigDetails">
                        <i class="bi-chevron-down"></i> More details
                    </button>
                @endif
            </div>

            {{--mechanical testing expanded details --}}
            @if($analysisType->code === 'mechanical_testing' && $upload->mechanicalTestingDetails)
                @php
                    $details = $upload->mechanicalTestingDetails;
                @endphp
                <div class="collapse mt-3 pt-3 border-top" id="mechanicalConfigDetails">
                    <div class="row row-cols-2 row-cols-md-4 g-3">
                        <div class="col">
                            <small class="d-block fw-semibold" style="color: #0D3B52;">Storage Medium</small>
                            <span>{{ ucwords(str_replace(['-', '_'], ' ', $details->storage_medium ?? 'Not Set')) }}</span>
                        </div>
                        <div class="col">
                            <small class="d-block fw-semibold" style="color: #0D3B52;">Storage Temp</small>
                            <span>{{ $details->storage_temperature ? $details->storage_temperature . '°C' : 'Not Set' }}</span>
                        </div>
                        <div class="col">
                            <small class="d-block fw-semibold" style="color: #0D3B52;">Test Conditioning</small>
                            <span>{{ ucwords(str_replace(['-', '_'], ' ', $details->test_conditioning ?? 'Not Set')) }}</span>
                        </div>
                        <div class="col">
                            <small class="d-block fw-semibold" style="color: #0D3B52;">Testing Temp</small>
                            <span>{{ $details->testing_temperature ? $details->testing_temperature . '°C' : 'Not Set' }}</span>
                        </div>
                        @if($details->use_preload)
                            <div class="col">
                                <small class="d-block fw-semibold" style="color: #0D3B52;">Preload</small>
                                <span>{{ $details->preload_value }} {{ $details->preload_unit ?? 'N' }}</span>
                            </div>
                        @endif
                        @if($details->support_span_3pt)
                            <div class="col">
                                <small class="d-block fw-semibold" style="color: #0D3B52;">Support Span</small>
                                <span>{{ $details->support_span_3pt }} mm</span>
                            </div>
                        @endif
                        @if($details->loading_rate_bending)
                            <div class="col">
                                <small class="d-block fw-semibold" style="color: #0D3B52;">Loading Rate</small>
                                <span>{{ $details->loading_rate_bending }} mm/s</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif