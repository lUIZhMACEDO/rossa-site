<div
    class="container"
    x-data="{ showSuccess: @json(session()->has('message')) }"
    {{-- This effect will scroll down to the next steps after you submit the form for mech-testing details --}}
    x-effect="if(showSuccess) { setTimeout(() => document.getElementById('step-map-measurements')?.scrollIntoView({ behavior: 'smooth' }), 100) }"
>

    @if (session()->has('message'))
        <div class="alert alert-success" role="alert">
            {{ session('message') }}
        </div>
    @endif
    
    {{-- rest of the file... --}}
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    {{-- show validation data --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Test Configuration Card --}}
        <div class="card mb-4" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h5 class="mb-0">Test Configuration</h5>
        </div>
        <div class="card-body">
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="row">
                        {{-- Test Method Selection --}}
                        <div class="col-md-6 mb-4">
                            <label for="test-method" class="form-label required">Test Method</label>
                            <select id="test-method" class="form-select" wire:model.live="activeSubTab">
                                <option value="">Select Test Method</option>
                                @foreach($availableSubTabs as $k => $tab)
                                    <option value="{{ $k }}">{{ ucwords(str_replace('_', ' ', $tab)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Bone Tested Selection --}}
                        <div class="col-md-6 mb-4">
                            <label for="bone-tested" class="form-label required">Bone Tested</label>
                            <select id="bone-tested" class="form-select" wire:model.live="boneTested">
                                <option value="">Select Bone Tested</option>
                                @if ($activeSubTab === 'compression')
                                    <option value="vertebra">Vertebra</option>
                                    <option value="forelimb">Forelimb (radius + ulna)</option>
                                @else
                                    <option value="femur">Femur</option>
                                    <option value="humerus">Humerus</option>
                                @endif
                                <option value="ulna">Ulna</option>
                                <option value="radius">Radius</option>
                                <option value="tibia">Tibia</option>
                            </select>
                            @error('boneTested')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if ($activeSubTab)
                        {{-- Orientation --}}
                        @if ($activeSubTab === 'torsion')
                            {{-- Anatomical Reference (feedback #67) --}}
                            {{-- <div class="mb-0">
                                <label for="anatomical-reference" class="form-label required">Anatomical Reference</label>
                                <input type="text" id="anatomical-reference" class="form-control" placeholder="e.g., proximal end fixed, distal rotated" wire:model.live="anatomicalReference">
                            </div> --}}
                        @elseif ($activeSubTab === 'compression')
                            <div class="mb-0">
                                <label for="orientation" class="form-label required">Load Orientation</label>
                                <select id="orientation" class="form-select" wire:model.live="orientation">
                                    <option value="">Select Orientation</option>
                                    <option value="axial">Axial</option>
                                    <option value="off-axis">Off-axis</option>
                                </select>
                                @if ($orientation === 'off-axis')
                                    <div class="mt-2">
                                        <label for="off-axis-angle" class="form-label required">Off-axis Angle (degrees)</label>
                                        <input type="number" id="off-axis-angle" class="form-control" placeholder="Enter angle" wire:model.live="offAxisAngle" min="0" max="90" step="0.1">
                                    </div>
                                @endif
                                @error('orientation')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                                @error('offAxisAngle')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <div class="mb-0">
                                <label for="orientation" class="form-label required">Which side was in tension?</label>
                                <select id="orientation" class="form-select" wire:model.live="orientation">
                                    <option value="">Select Orientation</option>
                                    <option value="anterior">Anterior</option>
                                    <option value="posterior">Posterior</option>
                                    <option value="medial">Medial</option>
                                    <option value="lateral">Lateral</option>
                                    <option value="other">Other (specify)</option>
                                </select>
                                @if ($orientation === 'other')
                                    <input type="text" class="form-control mt-2" placeholder="Specify orientation" wire:model.live="orientationOther">
                                @endif
                                @error('orientation')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                                @error('orientationOther')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Test Method Diagram --}}
                @if($activeSubTab)
                    <div class="col-lg-4">
                        <div class="text-center">
                            @if($activeSubTab === 'bending_3pt')
                                <img src="{{ asset('images/mechanical-testing/3pt-bending.png') }}" alt="3-Point Bending Test Diagram" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                            @elseif($activeSubTab === 'bending_4pt')
                                <img src="{{ asset('images/mechanical-testing/4pt-bending.png') }}" alt="4-Point Bending Test Diagram" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                            @elseif($activeSubTab === 'compression')
                                <img src="{{ asset('images/mechanical-testing/compression.png') }}" alt="Compression Test Diagram" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                            @elseif($activeSubTab === 'torsion')
                                <img src="{{ asset('images/mechanical-testing/torsion.png') }}" alt="Torsion Test Diagram" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sample Preparation Card --}}
        <div class="card mb-4" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h5 class="mb-0">Sample Preparation</h5>
        </div>
        <div class="card-body">
            
            <div class="row">
                {{-- Storage Medium --}}
                <div class="col-md-6 mb-4">
                    <label for="storage-medium" class="form-label required">Storage Medium</label>
                    <select id="storage-medium" class="form-select" wire:model.live="storageMedium">
                        <option value="">Select Storage Medium</option>
                        <option value="pbs">Wrapped in PBS Soaked Gauze</option>
                        <option value="saline">Wrapped in Saline Soaked Gauze</option>
                        <option value="ethanol">In 70% ethanol</option>
                        <option value="formalin">In 10% formalin</option>
                        <option value="other">Other (specify)</option>
                    </select>
                    @if ($storageMedium === 'other')
                        <input type="text" class="form-control mt-2" placeholder="Specify storage medium" wire:model.live="storageMediumOther">
                    @endif
                    @error('storageMedium')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                    @error('storageMediumOther')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Storage Temperature --}}
                <div class="col-md-6 mb-4">
                    <label for="storage-temperature" class="form-label required">Storage Temperature</label>
                    <select id="storage-temperature" class="form-select" wire:model.live="storageTemperature">
                        <option value="">Select Storage Temperature</option>
                        <option value="fresh">Fresh (never frozen)</option>
                        <option value="-20">-20Â°C</option>
                        <option value="-80">-80Â°C</option>
                        <option value="4">4Â°C</option>
                        <option value="room">Room temperature</option>
                        <option value="other">Other (specify)</option>
                    </select>
                    @if ($storageTemperature === 'other')
                        <input type="text" class="form-control mt-2" placeholder="Specify storage temperature" wire:model.live="storageTemperatureOther">
                    @endif
                    @error('storageTemperature')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                    @error('storageTemperatureOther')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                {{-- Test Conditioning (Hydration) --}}
                <div class="col-md-6 mb-4">
                    <label for="test-conditioning" class="form-label required">Test Conditioning (Hydration)</label>
                    <select id="test-conditioning" class="form-select" wire:model.live="testConditioning">
                        <option value="">Select Test Conditioning</option>
                        <option value="hyrdrated">Kept hydrated with storage medium before test, then tested in air</option>
                        <option value="submerged">Testing Submerged (in fluid bath)</option>
                        <option value="other">Other (specify)</option>
                    </select>
                    @if ($testConditioning === 'other')
                        <input type="text" class="form-control mt-2" placeholder="Specify test conditioning" wire:model.live="testConditioningOther">
                    @endif
                    @error('testConditioning')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                    @error('testConditioningOther')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Testing Temperature --}}
                <div class="col-md-6 mb-0">
                    <label for="testing-temperature" class="form-label required">Testing Temperature</label>
                    <select id="testing-temperature" class="form-select" wire:model.live="testingTemperature">
                        <option value="">Select Testing Temperature</option>
                        <option value="room">Room temperature (~20-23Â°C)</option>
                        <option value="37">37Â°C (body temperature)</option>
                        <option value="4">4Â°C</option>
                        <option value="other">Other (specify)</option>
                    </select>
                    @if ($testingTemperature === 'other')
                        <input type="text" class="form-control mt-2" placeholder="Specify testing temperature" wire:model.live="testingTemperatureOther">
                    @endif
                    @error('testingTemperature')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                    @error('testingTemperatureOther')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    @if($activeSubTab)
    {{-- Testing Parameters, Specimen Geometry & Loading Rate Card --}}
    <div class="card mb-4" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h5 class="mb-0">Testing Parameters</h5>
        </div>
        <div class="card-body">
            
            {{-- Preload --}}
            <div class="mb-4">
                <label class="form-label required">Was a preload implemented?</label>
                <div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="preload-yes" name="usePreload" value="1" wire:model.live="usePreload">
                        <label class="form-check-label" for="preload-yes">
                            Yes
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="preload-no" name="usePreload" value="0" wire:model.live="usePreload">
                        <label class="form-check-label" for="preload-no">
                            No
                        </label>
                    </div>
                </div>
            </div>

            @if($usePreload)
            <div class="mb-4">
                @if ($activeSubTab === 'torsion')
                    <label for="preload-torque" class="form-label required">Preload Torque</label>
                    <div class="input-group">
                        <input type="number" id="preload-torque" class="form-control" placeholder="Enter Preload Torque" wire:model.live="customTorsionValue" step="0.1">
                        <span class="input-group-text">Nmm</span>
                    </div>
                    @error('customTorsionValue')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                @else
                    <select id="preload-value" class="form-select" wire:model.live="preloadValue">
                        <option value="">Select Preload</option>
                        <option value="0.1">0.1 N</option>
                        <option value="0.5">0.5 N</option>
                        <option value="1.0">1.0 N</option>
                        <option value="custom">Custom (specify)</option>
                    </select>
                    @if ($preloadValue === 'custom')
                        <div class="input-group mt-2">
                            <input type="number" class="form-control" placeholder="Enter Preload Value" wire:model.live="customPreloadValue" step="0.01">
                            <span class="input-group-text">N</span>
                        </div>
                    @endif
                    @error('preloadValue')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                    @error('customPreloadValue')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                @endif
            </div>
            @endif

            <hr class="my-4">

            <div class="row">
                {{-- Specimen Geometry Column --}}
                <div class="col-lg-6">
                    <h5 class="mb-4">Specimen Geometry</h5>

                    @switch($activeSubTab)
                        @case('bending_3pt')
                            <div class="row">
                                <div class="col-12 mb-0">
                                    <label for="support-span-3pt" class="form-label required">Support Span (mm)</label>
                                    <input type="number" id="support-span-3pt" class="form-control" wire:model.live="supportSpan3pt" placeholder="Enter Support Span">
                                    @error('supportSpan3pt')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @break
                        @case('bending_4pt')
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="support-span-4pt" class="form-label required">Support Span (mm)</label>
                                    <input type="number" id="support-span-4pt" class="form-control" wire:model.live="supportSpan4pt" placeholder="Enter Support Span">
                                    @error('supportSpan4pt')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 mb-0">
                                    <label for="loading-span-4pt" class="form-label required">Loading Span (mm)</label>
                                    <input type="number" id="loading-span-4pt" class="form-control" wire:model.live="loadingSpan4pt" placeholder="Enter Loading Span">
                                    @error('loadingSpan4pt')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @break
                        @case('torsion')
                            <div class="row">
                                <div class="col-12 mb-0">
                                    <label for="gage-length-torsion" class="form-label required">Gage Length (mm)</label>
                                    <input type="number" id="gage-length-torsion" class="form-control" wire:model.live="gageLengthTorsion" placeholder="Enter Gage Length">
                                    @error('gageLengthTorsion')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @break
                        @case('compression')
                            <div class="row">
                                <div class="col-12 mb-0">
                                    <label for="gage-length-compression" class="form-label required">Gage/Original Length (mm)</label>
                                    <input type="number" id="gage-length-compression" class="form-control" wire:model.live="gageLengthCompression" placeholder="Enter Gage Length">
                                    @error('gageLengthCompression')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @break
                        @default
                            <p class="text-muted mb-0">Please select a test method to view the form.</p>
                    @endswitch
                </div>

                {{-- Loading Rate Column --}}
                <div class="col-lg-6">
                    <h5 class="mb-4">Loading Rate</h5>

                    @switch($activeSubTab)
                        @case('bending_3pt')
                        @case('bending_4pt')
                            <div class="row">
                                <div class="col-12 mb-0">
                                    <label for="loading-rate-bending" class="form-label required">Loading Rate</label>
                                    <div class="input-group">
                                        <input type="number" id="loading-rate-bending" class="form-control" placeholder="Enter Loading Rate" wire:model.live="loadingRateBending" step="0.01" min="0">
                                        <span class="input-group-text">mm/s</span>
                                    </div>
                                    @error('loadingRateBending')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @break
                        @case('compression')
                            <div class="row">
                                <div class="col-12 mb-0">
                                    <label for="loading-rate-compression" class="form-label required">Loading Rate</label>
                                    <div class="input-group">
                                        <input type="number" id="loading-rate-compression" class="form-control" placeholder="Enter Loading Rate" wire:model.live="loadingRateCompression" step="0.01" min="0">
                                        <select class="form-select" wire:model.live="loadingRateCompressionUnit">
                                            <option value="mm/s">mm/s</option>
                                            <option value="%/s">%/s (strain rate)</option>
                                        </select>
                                    </div>
                                    @error('loadingRateCompression')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                    @error('loadingRateCompressionUnit')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @break
                        @case('torsion')
                            <div class="row">
                                <div class="col-12 mb-0">
                                    <label for="loading-rate-torsion" class="form-label required">Loading Rate</label>
                                    <div class="input-group">
                                        <input type="number" id="loading-rate-torsion" class="form-control" placeholder="Enter Loading Rate" wire:model.live="loadingRateTorsion" step="0.01" min="0">
                                        <select class="form-select" wire:model.live="loadingRateTorsionUnit">
                                            <option value="degrees/s">degrees/s</option>
                                            <option value="Nmm/s">Nmm/s (torque rate)</option>
                                        </select>
                                    </div>
                                    @error('loadingRateTorsion')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                    @error('loadingRateTorsionUnit')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @break
                    @endswitch
                </div>
            </div>
        </div>
    </div>

    {{-- File uploader --}}
    <div class="card mb-4" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h5 class="mb-0">Upload CSV</h5>
        </div>
        <div class="card-body">
            
            <div class="border border-dashed rounded p-4 text-center" style="min-height: 200px; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #f8f9fa;">
                <div>
                    <i class="bi bi-cloud-arrow-up text-muted mb-3" style="font-size: 48px;"></i>
                    <p class="mb-2"><strong>Upload CSV file</strong></p>
                    @if($study->isPhenotypeAnalysisLocked(auth()->user()))
                        <button type="button" class="btn btn-outline-secondary">
                            Choose File
                        </button>
                    @else
                        <label for="csv-file" class="btn btn-outline-primary">
                            Choose File
                        </label>
                    @endif
                    <input 
                        type="file"
                        id="csv-file"
                        class="d-none"
                        wire:model.live="csvFile"
                        accept=".csv,text/csv"
                    >
                </div>
            </div>

            <div wire:loading wire:target="csvFile" class="text-muted mt-3">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Uploading...
            </div>

            <div class="form-text mt-2">
                Supported format: .csv. File must contain a "Sample Name" column header.
            </div>

            <div class="alert alert-info mt-3 mb-0">
                <div class="d-flex mb-2">
                    <i class="bi bi-info-circle me-2"></i>
                    <div>
                        <strong>Add â€œSample Nameâ€ to your spreadsheet: </strong>
                        For your personal datafile(s) to be properly read, please change the header column above your sample ids to <strong>Sample Name</strong> and in the same row as your measurement parameters as shown in the example below. 
                    </div>
                </div>

                <div class="d-flex justify-content-center">
                    <figure class="mb-0 col-md-6">
                        <img src="{{ asset('images/guides/data-ingestion/01_Datasheet.png')}}" alt="Sample Name in A1" class="img-fluid mb-3 border">
                    </figure>
                </div>
            </div>

            @if (is_object($csvFile) && method_exists($csvFile, 'getClientOriginalName'))
                <div class="alert alert-info mt-3 mb-0">
                    <strong>Selected file:</strong> {{ $csvFile->getClientOriginalName() }}
                </div>
            @elseif(!empty($existingFileName))
                <div class="alert alert-success mt-3 mb-0">
                    <strong>Currently uploaded file:</strong> {{ $existingFileName }}
                </div>
            @endif

            @if (!empty($fileMessage))
                <div class="alert alert-success mt-3 mb-0">{{ $fileMessage }}</div>
            @endif

            @if (!empty($fileError))
                <div class="alert alert-danger mt-3 mb-0">{{ $fileError }}</div>
            @endif

            @error('csvFile')
                <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="my-3">
        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="csvFile">
            Save Mechanical Testing Details
        </button>
    </div>
    @endif
</div>


