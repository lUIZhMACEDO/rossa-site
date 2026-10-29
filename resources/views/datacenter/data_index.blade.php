@extends('layout')
@section('content')
<div class="py-5 container">
    <div class="d-flex flex-column align-items-center mb-2">
        <h1 class="fw-bold">ROSSA Data Center</h1>
        <p class="text-muted">Open-access rodent skeletal phenotyping data for the research community</p>
    </div>
    <div class="d-flex justify-content-center">
        <form method="GET" action="{{ route('browser') }}" class="input-group mb-3 w-50 gap-3">
            <select name="type" class="form-select rounded" aria-label="Filter type" style="max-width: 150px;">
                <option value="all" selected>All</option>
                <option value="gene">Gene</option>
                <option value="drug">Drug</option>
                <option value="ontology">Ontology</option>
                <option value="parameter">Parameter</option>
            </select>

            <input type="text" name="query" class="form-control rounded" placeholder="Search by gene, drug, ontology, parameter, or keyword..." aria-label="Search data" aria-describedby="button-search" value="{{ request('query') }}">
            <button class="btn btn-outline-primary allow-rounded rounded" type="submit" id="button-search">Search</button>
        </form>
    </div>
    <div class="d-flex justify-content-center align-items-center gap-2 mb-4">
        <span>Try: </span>
        <a href="{{ route('browser', ['type' => 'gene', 'query' => 'Sost']) }}" class="btn btn-outline-secondary allow-rounded" style="border-radius: 15px">Sost</a>
        <a href="{{ route('browser', ['type' => 'drug', 'query' => 'PTH']) }}" class="btn btn-outline-secondary allow-rounded" style="border-radius: 15px">PTH</a>
        <a href="{{ route('browser', ['type' => 'ontology', 'query' => 'Osteoporosis', 'category' => 'disease']) }}" class="btn btn-outline-secondary allow-rounded" style="border-radius: 15px">Osteoporosis</a>
        <a href="{{ route('browser', ['type' => 'parameter', 'query' => 'BV/TV']) }}" class="btn btn-outline-secondary allow-rounded" style="border-radius: 15px">BV/TV</a>
        <a href="{{ route('search.index', ['q' => 'ROSSA:0042']) }}" class="btn btn-outline-secondary allow-rounded" style="border-radius: 15px">ROSSA:0042</a>
    </div>

    <div class="d-flex justify-content-center mb-3">
        <hr class="w-75" style="border-color: #727272;">
    </div>

    <div class="d-flex justify-content-center align-items-start gap-4 flex-column">
        <div class="w-100 p-3">
            <h6 class="text-uppercase text-muted fw-bold mb-1">Studies</h6>
            <div class="d-flex flex-row gap-4">
                <div class="d-flex border p-3 rounded bg-light w-100">
                    <div class="d-flex flex-column text-uppercase">
                        <h2 class="fw-bold text-primary">{{ $genetic_studies }}</h2>
                        Genetic Studies
                    </div>
                </div>
                <div class="d-flex border p-3 rounded bg-light w-100">
                    <div class="d-flex flex-column text-uppercase">
                        <h2 class="fw-bold text-danger">{{ $drug_treatment_studies }}</h2>
                        Drug Treatment Studies
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center align-items-start gap-4 flex-column">
        <div class="w-100 p-3">
            <h6 class="text-uppercase text-muted fw-bold mb-1">Datasets By Analysis Type</h6>
            <div class="d-flex flex-row gap-4">
                <div class="d-flex border p-3 rounded bg-light w-100">
                    <div class="d-flex flex-column"> {{-- No uppercase because it will transform µ => M --}}
                        <h2 class="fw-bold text-success">{{ $mct_datasets }}</h2>
                        µCT DATASETS
                    </div>
                </div>
                <div class="d-flex border p-3 rounded bg-light w-100">
                    <div class="d-flex flex-column text-uppercase">
                        <h2 class="fw-bold text-success">{{ $dexa_datasets }}</h2>
                        DEXA Datasets
                    </div>
                </div>
                <div class="d-flex border p-3 rounded bg-light w-100">
                    <div class="d-flex flex-column text-uppercase">
                        <h2 class="fw-bold text-success">{{ $histo_datasets }}</h2>
                        Histomorphometry Datasets
                    </div>
                </div>
                <div class="d-flex border p-3 rounded bg-light w-100">
                    <div class="d-flex flex-column text-uppercase">
                        <h2 class="fw-bold text-success">{{ $mech_datasets }}</h2>
                        Mechanical Testing Datasets
                    </div>
                </div>
                <div class="d-flex border p-3 rounded bg-light w-100">
                    <div class="d-flex flex-column text-uppercase">
                        <h2 class="fw-bold text-success">{{ $clinical_chem_datasets }}</h2>
                        Clinical Chemistry Datasets
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="d-flex justify-content-center align-items-start gap-4 flex-column">
        <div class="w-100 p-3">
            <h6 class="text-uppercase text-muted fw-bold mb-1">Discovery Entry Points</h6>
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
</div>

@endsection