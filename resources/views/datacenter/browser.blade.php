@extends('layout')
@section('content')
<div class="py-5 container">
    {{-- If we didn't choose a browser type, please choose one!!1! --}}
    @if (!collect(['gene', 'drug', 'ontology', 'parameter'])->contains($type))
    <div class="d-flex flex-column align-items-center mb-2">
        <h1 class="fw-bold">Data Browser</h1>
        <p class="text-muted">Explore the data in ROSSA</p>
    </div>
        <div class="d-flex justify-content-center align-items-start gap-4 flex-column">
            <div class="w-100 p-3">
                <h6 class="text-uppercase text-muted fw-bold mb-1">Pick a browser</h6>
                <div class="d-flex flex-column gap-4 flex-wrap">
                    <div class="d-flex flex-row gap-4">
                        <a href="{{ route('browser', ['type' => 'gene']) }}" class="d-flex flex-column border p-3 rounded w-100 text-decoration-none text-dark" style="cursor: pointer; transition: all 0.3s;">
                            <div class="d-flex flex-row align-items-center justify-content-between w-100 mb-3">
                                <div class="p-2 bg-light">
                                    <i class="bi bi-person text-primary"></i>
                                </div>
                                <div>
                                    <i class="bi bi-box-arrow-up-right text-primary"></i>
                                </div>
                            </div>
                            <h6 class="fw-bold">Gene Browser</h6>
                            <p class="text-muted">Search by gene to explore all knockout and transgenic studies, available analyses, and linked pharmacological targets.</p>
                            <div>
                                <span class="badge bg-primary allow-rounded rounded-pill px-2 me-1 mb-1">Global KO</span>
                                <span class="badge bg-primary allow-rounded rounded-pill px-2 me-1 mb-1">Conditional KO</span>
                                <span class="badge bg-primary allow-rounded rounded-pill px-2 me-1 mb-1">Transgenic</span>
                            </div>
                        </a>
                        <a href="{{ route('browser', ['type' => 'drug']) }}" class="d-flex flex-column border p-3 rounded w-100 text-decoration-none text-dark" style="cursor: pointer; transition: all 0.3s;">
                            <div class="d-flex flex-row align-items-center justify-content-between w-100 mb-3">
                                <div class="p-2 bg-light">
                                    <i class="bi bi-lock text-danger"></i>
                                </div>
                                <div>
                                    <i class="bi bi-box-arrow-up-right text-danger"></i>
                                </div>
                            </div>
                            <h6 class="fw-bold">Drug Browser</h6>
                            <p class="text-muted">Search by drug compound to view skeletal phenotyping outcomes, doing regimens, and gene target linkages via ChEMBL.</p>
                            <div>
                                <span class="badge bg-danger allow-rounded rounded-pill px-2 me-1 mb-1">ChEMBL linked</span>
                                <span class="badge bg-danger allow-rounded rounded-pill px-2 me-1 mb-1">Gene targets</span>
                                <span class="badge bg-danger allow-rounded rounded-pill px-2 me-1 mb-1">Dose response</span>
                            </div>
                        </a>
                    </div>
                    <div class="d-flex flex-row gap-4">
                        <a href="{{ route('browser', ['type' => 'ontology']) }}" class="d-flex flex-column border p-3 rounded w-100 text-decoration-none text-dark" style="cursor: pointer; transition: all 0.3s;">
                            <div class="d-flex flex-row align-items-center justify-content-between w-100 mb-3">
                                <div class="p-2 bg-light">
                                    <i class="bi bi-grid text-success"></i>
                                </div>
                                <div>
                                    <i class="bi bi-box-arrow-up-right text-success"></i>
                                </div>
                            </div>
                            <h6 class="fw-bold">Ontology Browser</h6>
                            <p class="text-muted">Browse studies by thematic area, tissue interaction, cell type, or disease using standardized MONDO ontology terms.</p>
                            <div>
                                <span class="badge bg-success allow-rounded rounded-pill px-2 me-1 mb-1">Thematic areas</span>
                                <span class="badge bg-success allow-rounded rounded-pill px-2 me-1 mb-1">Cell types</span>
                                <span class="badge bg-success allow-rounded rounded-pill px-2 me-1 mb-1">MONDO disease</span>
                            </div>
                        </a>
                        <a href="{{ route('browser', ['type' => 'parameter']) }}" class="d-flex flex-column border p-3 rounded w-100 text-decoration-none text-dark" style="cursor: pointer; transition: all 0.3s;">
                            <div class="d-flex flex-row align-items-center justify-content-between w-100 mb-3">
                                <div class="p-2 bg-light">
                                    <i class="bi bi-person-fill text-info"></i>
                                </div>
                                <div>
                                    <i class="bi bi-box-arrow-up-right text-info"></i>
                                </div>
                            </div>
                            <h6 class="fw-bold">Parameter Browser</h6>
                            <p class="text-muted">Select a measurement parameter to visualize normalized test/control ratios across all studies as an interactive volcano-type plot.</p>
                            <div>
                                <span class="badge bg-info allow-rounded rounded-pill px-2 me-1 mb-1">μCT</span>
                                <span class="badge bg-info allow-rounded rounded-pill px-2 me-1 mb-1">Histomorphometry</span>
                                <span class="badge bg-info allow-rounded rounded-pill px-2 me-1 mb-1">DXA</span>
                                <span class="badge bg-info allow-rounded rounded-pill px-2 me-1 mb-1">Mechanical</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @switch($type)
        @case('gene')
            @livewire('datacenter.browser.gene-browser')
            @break
        @case('drug')
            @livewire('datacenter.browser.drug-browser')
            @break
        @case('ontology')
            @livewire('datacenter.browser.ontology-browser')
            @break
        @case('parameter')
            @livewire('datacenter.browser.parameter-browser')
            @break
        @default
    @endswitch

</div>
@endsection