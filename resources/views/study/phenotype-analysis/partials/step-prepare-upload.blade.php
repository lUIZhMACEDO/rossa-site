<input type="hidden" name="analysis_type_id" value="{{ $analysisType->id }}">

<div class="mb-4">
    <div class="card" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h5 class="mb-0">Additional Information</h5>
        </div>
        <div class="card-body">
            {{-- Analysis type specific fields (specimen type, region, etc.) --}}
            @includeIf('study.phenotype-analysis.forms.' . $analysisType->code, [
                    'upload' => $upload,
                    'analysisType' => $analysisType,
                ])
        </div>
    </div>
</div>

{{-- File Upload Section --}}
<div class="mb-4" x-data="{
    uploading: false,
    selectedFile: null
}">
    <div class="card" style="border-radius: 0.375rem; overflow: hidden; border-left: 4px solid #0D3B52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0D3B52;">
            <h5 class="mb-0">Upload Data File</h5>
        </div>
        <div class="card-body">

            @if ($upload->exists && $upload->path)
                {{-- Existing file display --}}
                <div class="alert alert-success mb-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-earmark-spreadsheet me-2" style="font-size: 24px;"></i>
                        <div>
                            <strong>Currently uploaded file:</strong> {{ $upload->original_filename }}
                            <span class="text-muted small ms-2">({{ number_format($upload->size / 1024, 1) }} KB)</span>
                        </div>
                    </div>
                </div>

                {{-- Replace file upload area --}}
                <div 
                    class="border border-dashed rounded p-4 text-center" 
                    style="min-height: 150px; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #f8f9fa;"
                >
                    <div>
                        <i class="bi bi-cloud-arrow-up text-muted mb-3" style="font-size: 48px;"></i>
                        <p class="mb-2"><strong>Replace File (Optional)</strong></p>
                        <label for="file" class="btn btn-outline-primary">
                            Choose File
                        </label>
                        <input 
                            type="file"
                            id="file"
                            name="file"
                            class="d-none"
                            accept=".csv"
                            x-on:change="selectedFile = $event.target.files[0]?.name || null"
                        >
                    </div>
                </div>

                <div class="alert alert-warning alert-dismissible fade show mt-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Uploading a new file will clear all column mappings and sample groupings.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @else
                {{-- New file upload area --}}
                <div 
                    class="border border-dashed rounded p-4 text-center @error('file') border-danger @enderror" 
                    style="min-height: 200px; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #f8f9fa;"
                >
                    <div>
                        <i class="bi bi-cloud-arrow-up text-muted mb-3" style="font-size: 48px;"></i>
                        <p class="mb-2"><span class="text-danger fw-semibold">*</span> <strong>Upload CSV File</strong></p>
                        <label for="file" class="btn btn-outline-primary">
                            Choose File
                        </label>
                        <input 
                            type="file"
                            id="file"
                            name="file"
                            class="d-none"
                            accept=".csv"
                            x-on:change="selectedFile = $event.target.files[0]?.name || null"
                        >
                    </div>
                </div>

                <div class="form-text mt-2">
                    Supported format: .csv. File must contain a "Sample Name" column header.
                </div>
            @endif

            <div class="alert alert-info mt-3 mb-0">
                <div class="d-flex mb-2">
                    <i class="bi bi-info-circle me-2"></i>
                    <div>
                        <strong>Add "Sample Name" to your spreadsheet: </strong>
                        For your personal datafile(s) to be properly read, please change the header column above your sample ids to <strong>Sample Name</strong> and in the same row as your measurement parameters as shown in the example below. 
                    </div>
                </div>

                <div class="d-flex justify-content-center">
                    <figure class="mb-0 col-md-6">
                        <img src="{{ asset('images/guides/data-ingestion/01_Datasheet.png')}}" alt="Sample Name in A1" class="img-fluid mb-3 border">
                    </figure>
                </div>
            </div>

            {{-- Loading indicator --}}
            <div x-show="uploading" x-cloak class="text-muted mt-3">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Uploading...
            </div>

            {{-- Selected file display --}}
            <div x-show="selectedFile" x-cloak class="alert mt-3 mb-0" style="background-color: #0D3B52; color: #fff; display: flex; align-items: center;">
                <i class="bi bi-check-circle-fill me-2" style="font-size: 1.25rem;"></i>
                <span><strong>Selected file:</strong> <span x-text="selectedFile"></span></span>
            </div>

            @error('file')
                <div class="alert alert-danger alert-dismissible fade show mt-3 mb-0">
                    {{ $message }}
                    <div class="small mt-1">Please re-select your file.</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @enderror

            @if ($errors->any() && !$errors->has('file') && !($upload->exists && $upload->path))
                <div class="alert alert-warning alert-dismissible fade show mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Please re-select your file after correcting the errors above.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </div>
</div>