@extends('layout')

@section('title', 'Data Submission')
@section('description', 'Submit your study ROSSA. Review the depositor agreement and start a new study submission.')

@section('content')
<div class="container py-5">

    {{-- Header --}}
    <div class="text-center mb-3 pt-3">
        <h1 class="display-6 fw-bold text-dark mb-2">Data Submission Portal</h1>
        <p class="fs-6 text-muted mb-0">Thank you for helping advance research in the skeletal sciences.</p>
    </div>

    {{-- Auth Block --}}
    <div class="row justify-content-center my-4">
        <div class="col-md-8">
            @auth
                <div class="rounded-3 p-4 text-center" style="background-color: #eaf3fb; border: 2px solid #185FA5;">
                    <p class="fw-semibold mb-1" style="color: #185FA5;">You're logged in and ready to go.</p>
                    <p class="small text-muted mb-3">Review and approve the depositor agreement to begin your data submission.</p>
                    <a href="{{ route('depositor-agreement') }}" class="btn btn-primary btn-lg px-5">
                        Start Your Data Submission
                    </a>
                </div>
            @else
                <div class="rounded-3 p-4 text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">
                    <p class="fw-semibold mb-1" style="color: #212529;">Ready to submit your data?</p>
                    <p class="small text-muted mb-3">Create an account or log in to begin. You'll return here automatically after signing in.</p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="{{ route('login.redirect') }}" class="btn btn-primary btn-lg px-4">Log In</a>
                    </div>
                </div>
            @endauth
        </div>
    </div>



    {{-- Before You Begin --}}
    <div class="row justify-content-center mb-5">
        <div class="col-md-10">
            <div class="rounded-3 p-4" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">
                <h2 class="h5 fw-bold mb-3" style="color: #212529;">
                    <i class="bi bi-list-check me-2" style="color: #185FA5;"></i>Before You Begin
                </h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <div class="me-3 mt-1" style="min-width: 20px;">
                                <i class="bi bi-person-circle" style="color: #185FA5; font-size: 1rem;"></i>
                            </div>
                            <div>
                                <a href="#section-account" class="fw-semibold text-decoration-none" style="color: #185FA5; font-size: 0.9rem;">Account &amp; Login</a>
                                <p class="mb-0 small text-muted">A ROSSA account is required to submit data.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <div class="me-3 mt-1" style="min-width: 20px;">
                                <i class="bi bi-file-earmark-spreadsheet" style="color: #185FA5; font-size: 1rem;"></i>
                            </div>
                            <div>
                                <a href="#prepare-data" class="fw-semibold text-decoration-none" style="color: #185FA5; font-size: 0.9rem;">Planning for Submission</a>
                                <p class="mb-0 small text-muted">File format requirements and templates before you start.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <div class="me-3 mt-1" style="min-width: 20px;">
                                <i class="bi bi-card-checklist" style="color: #185FA5; font-size: 1rem;"></i>
                            </div>
                            <div>
                                <a href="#section-scope" class="fw-semibold text-decoration-none" style="color: #185FA5; font-size: 0.9rem;">Submission Scope &amp; Embargo Policy</a>
                                <p class="mb-0 small text-muted">What types of studies we accept and publication rules.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <div class="me-3 mt-1" style="min-width: 20px;">
                                <i class="bi bi-book" style="color: #185FA5; font-size: 1rem;"></i>
                            </div>
                            <div>
                                <a href="#section-guides" class="fw-semibold text-decoration-none" style="color: #185FA5; font-size: 0.9rem;">Quick &amp; Detailed Guides</a>
                                <p class="mb-0 small text-muted">Step-by-step guides with screenshots are available.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <div class="me-3 mt-1" style="min-width: 20px;">
                                <i class="bi bi-diagram-3" style="color: #185FA5; font-size: 1rem;"></i>
                            </div>
                            <div>
                                <a href="#section-approaches" class="fw-semibold text-decoration-none" style="color: #185FA5; font-size: 0.9rem;">Experimental Approaches Accepted</a>
                                <p class="mb-0 small text-muted">Genetic, drug, gonadectomy, and combined study designs.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <div class="me-3 mt-1" style="min-width: 20px;">
                                <i class="bi bi-graph-up" style="color: #185FA5; font-size: 1rem;"></i>
                            </div>
                            <div>
                                <a href="#section-analyses" class="fw-semibold text-decoration-none" style="color: #185FA5; font-size: 0.9rem;">Phenotyping Analyses Accepted</a>
                                <p class="mb-0 small text-muted">DEXA, microCT, histomorphometry, mechanical testing, and more.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        {{-- Office Hours Callout --}}
    <div class="row justify-content-center mb-4">
        <div class="col-md-10">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 rounded-3 px-4 py-3" style="background-color: #185FA5;">
                <div class="text-white">
                    <div class="fw-bold mb-1" style="font-size: 1.25rem;">
                        <i class="bi bi-camera-video me-2"></i>Need help with your submission?
                    </div>
                    <span style="font-size: 0.95rem;">Join our weekly office hours every Thursday at 2:00 PM Eastern Time for live, drop-in support from the ROSSA team.</span>
                </div>
                <a href="{{ route('office-hours.index') }}" class="btn btn-light btn-sm text-nowrap fw-semibold" style="color: #185FA5;">Sign Up for Office Hours</a>
            </div>
        </div>
    </div>
    <hr class="my-5">

    {{-- Overview of the Submission Process --}}
    <div class="mb-2">
        <h2 class="h4 fw-bold mb-3 px-3 py-2 rounded" style="background-color: #1a2e44; color: #fff;">Overview of the Submission Process</h2>
        <p class="text-muted mb-4" style="font-size: 0.95rem;">Follow these steps in order. You can save your progress and return at any time.</p>
    </div>

    {{-- Steps + nav tree --}}
    <div class="row mb-4">
        <div class="row justify-content-center">

            {{-- Left column: steps --}}
            <div class="col">
                @php
                    $content = [
                        [
                            'title' => 'Complete Metadata Sections (in order)',
                            'description' => 'After approving the depositor agreement, a unique Study ID (ROSSA...) will be generated. Use this ID in publications to reference your archived data.',
                            'highlight' => false,
                            'notes' => [
                                '<strong>Study Information:</strong> Provide Study Summary and Publication Details',
                                '<strong>Subject Areas:</strong> Specify Research Domains',
                                '<strong>Investigators:</strong> A Corresponding investigator is required. Mark anyone else as Contributing.',
                            ]
                        ],
                        [
                            'title' => 'Animal Experimentation',
                            'description' => '',
                            'highlight' => false,
                            'notes' => [
                                '<strong>Set up:</strong> Select Relevant Experimental Variables (Genetic, Drug, Gonadectomy)',
                                '<strong>Animal Models:</strong> Detail Your Animal Models using the Preconfigured Categories',
                                '<strong>Drug Treatments:</strong> Add Drug Treatment Plans and Events (if performed)',
                                '<strong>Gonadectomy:</strong> Add Surgical Procedures (if performed)'
                            ]
                        ],
                        [
                            'title' => 'Phenotype Analysis',
                            'description' => '',
                            'highlight' => false,
                            'notes' => [
                                '<strong>Sample Groups:</strong> Define your Sample Groups using the Sample Groups Builder',
                                '<strong>Comparison Groups:</strong> Detail what Sample Groups will be Compared to Each Other',
                                '<strong>Upload Data:</strong> Select Preconfigured Categories to Upload Spreadsheets'
                            ]
                        ],
                        [
                            'title' => 'Report',
                            'description' => 'Please review each tab within the report to confirm the accuracy of information is consistent with your study outcomes. This report will be publicly shared with the broader research community, so accuracy is essential. Make edits as needed by navigating back to the relevant sections.',
                            'highlight' => false,
                            'notes' => [
                                '<strong>Information</strong>',
                                '<strong>Data Exploration</strong>',
                                '<strong>Download Original Data</strong>'
                            ]
                        ],
                        [
                            'title' => 'Submission',
                            'description' => '',
                            'highlight' => false,
                            'notes' => [
                                'Please confirm the information provided is accurate and complete.',
                                'Select "Submit Study" after confirmation or alternatively "Return to Form" to make edits.',
                                'After submission your study will be reviewed by our team and you will be notified of any necessary edits or if your study is approved for publication in the datacenter.'
                            ]
                        ]
                    ];
                @endphp

                @foreach($content as $index => $item)
                <div class="mb-3">
                    <div class="d-flex">
                        <x-circle-number class="me-4">{{ $index + 1 }}</x-circle-number>
                        <div class="d-flex flex-column @if($item['highlight']) bg-lightblue p-2 py-1 rounded w-100 @endif">
                            <strong class="mb-1 d-block fw-semibold" style="font-size: 1.1rem;">{{ $item['title'] }}</strong>
                            @if($item['description'])
                                <p class="mb-1">{{ $item['description'] }}</p>
                            @endif
                            @if(!empty($item['notes']))
                                <ul>
                                    @foreach($item['notes'] as $note)
                                        <li class="mb-1">{!! $note !!}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Right column: nav tree --}}
            <div class="col-auto d-flex justify-content-center align-items-start">
                <div class="card" style="border-radius: 0.375rem !important; width: 320px; border: 1px solid #dee2e6;">

                    {{-- Header --}}
                    <div class="p-3" style="background-color: #1a2e44; border-radius: 0.375rem 0.375rem 0 0;">
                        <div class="text-uppercase fw-bold" style="color: #fff; font-size: 0.7rem; letter-spacing: 0.08em;">Form Sections</div>
                        <div style="color: #7eb3d8; font-size: 0.75rem;">Study ID: ROSSA-20260514-46B2</div>
                    </div>

                    {{-- Nav --}}
                    <nav class="p-0 pb-2">

                        {{-- Study Information --}}
                        <div style="border-bottom: 1px solid #e9ecef;">
                            <div class="d-flex align-items-center justify-content-between px-3 py-2" style="background-color: #2b6cb0; color: #fff; font-size: 0.85rem; font-weight: 600;">
                                <span>Study Information</span>
                                <i class="bi bi-check-circle-fill" style="color: #48bb78;"></i>
                            </div>
                            <div style="border-left: 3px solid #2b6cb0; margin-left: 1rem;">
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Title</span>
                                    <i class="bi bi-check-circle" style="color: #adb5bd;"></i>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Summary</span>
                                    <i class="bi bi-check-circle" style="color: #adb5bd;"></i>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1 mb-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Publication</span>
                                    <i class="bi bi-check-circle" style="color: #adb5bd;"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Subject Areas --}}
                        <div style="border-bottom: 1px solid #e9ecef;">
                            <div class="d-flex align-items-center justify-content-between px-3 py-2" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">
                                <span>Subject Areas</span>
                                <i class="bi bi-check-circle-fill" style="color: #48bb78;"></i>
                            </div>
                            <div style="border-left: 3px solid #2b6cb0; margin-left: 1rem;">
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Thematic Areas</span>
                                    <i class="bi bi-check-circle" style="color: #adb5bd;"></i>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Tissue Interactions</span>
                                    <i class="bi bi-check-circle" style="color: #adb5bd;"></i>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Cell Types</span>
                                    <i class="bi bi-check-circle" style="color: #adb5bd;"></i>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1 mb-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Disease Search</span>
                                    <i class="bi bi-check-circle" style="color: #adb5bd;"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Investigators --}}
                        <div class="d-flex align-items-center justify-content-between px-3 py-2" style="border-bottom: 1px solid #e9ecef; font-size: 0.85rem; font-weight: 600; color: #2d3748;">
                            <span>Investigators</span>
                            <i class="bi bi-check-circle-fill" style="color: #48bb78;"></i>
                        </div>

                        {{-- Animal Experimentation --}}
                        <div style="border-bottom: 1px solid #e9ecef;">
                            <div class="d-flex align-items-center justify-content-between px-3 py-2" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">
                                <span>Animal Experimentation</span>
                                <i class="bi bi-exclamation-triangle-fill" style="color: #e6a817;"></i>
                            </div>
                            <div style="border-left: 3px solid #2b6cb0; margin-left: 1rem;">
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Setup</span>
                                    <i class="bi bi-check-circle-fill" style="color: #48bb78;"></i>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Animal Models</span>
                                    <i class="bi bi-check-circle" style="color: #adb5bd;"></i>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Drug Treatments</span>
                                    <i class="bi bi-check-circle-fill" style="color: #48bb78;"></i>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1 mb-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Gonadectomy Procedures</span>
                                    <i class="bi bi-check-circle-fill" style="color: #48bb78;"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Phenotype Analysis --}}
                        <div style="border-bottom: 1px solid #e9ecef;">
                            <div class="d-flex align-items-center justify-content-between px-3 py-2" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">
                                <span>Phenotype Analysis</span>
                                <i class="bi bi-check-circle" style="color: #adb5bd;"></i>
                            </div>
                            <div style="border-left: 3px solid #2b6cb0; margin-left: 1rem;">
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Sample Groups</span>
                                    <i class="bi bi-check-circle" style="color: #adb5bd;"></i>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Comparison Groups</span>
                                    <i class="bi bi-exclamation-triangle-fill" style="color: #e6a817;"></i>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1 mb-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Upload Data</span>
                                    <i class="bi bi-check-circle" style="color: #adb5bd;"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Report --}}
                        <div style="border-bottom: 1px solid #e9ecef;">
                            <div class="d-flex align-items-center justify-content-between px-3 py-2" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">
                                <span>Report</span>
                            </div>
                            <div style="border-left: 3px solid #2b6cb0; margin-left: 1rem;">
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Information</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Data Exploration</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1 mb-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Download Original Data</span>
                                </div>
                            </div>
                        </div>

                        {{-- Submission --}}
                        <div>
                            <div class="d-flex align-items-center justify-content-between px-3 py-2" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">
                                <span>Submission</span>
                            </div>
                            <div style="border-left: 3px solid #2b6cb0; margin-left: 1rem;">
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Ready to Submit?</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>Thank You for Your Contribution!</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-1 mb-1" style="font-size: 0.8rem; color: #4a5568;">
                                    <span>What Happens Next</span>
                                </div>
                            </div>
                        </div>

                    </nav>
                </div>
            </div>

        </div>
    </div>

    <hr class="my-5">

    {{-- Quick Tips --}}
    <div class="mb-5">
        <h2 class="h4 fw-bold mb-4 px-3 py-2 rounded" style="background-color: #1a2e44; color: #fff;">Quick Tips</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card px-2 h-100 bg-white shadow" style="border-bottom: 4px solid var(--bs-primary)">
                    <div class="card-body d-flex flex-column justify-content-between align-items-start">
                        <div class="pb-2">
                            <h5 class="card-title">Follow the order provided</h5>
                            <p class="card-text">But you may edit existing information at any time. If you go back and edit something, it may impact future sections and will need to be updated.</p>
                        </div>
                        <div class="d-flex align-items-center mt-3">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2" style="font-size: 1.2rem;"></i>
                            <small class="text-muted">This symbol indicates a warning</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card px-2 h-100 bg-white shadow" style="border-bottom: 4px solid var(--bs-primary)">
                    <div class="card-body d-flex flex-column justify-content-between align-items-start">
                        <div class="pb-2">
                            <h5 class="card-title">Save &amp; Continue</h5>
                            <p class="card-text">By clicking "Save and Continue" at the bottom of every page your work is saved to the database. This button will always look the same, keep an eye out for it!</p>
                        </div>
                        <button class="btn btn-primary">Save &amp; Continue</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card px-2 h-100 bg-white shadow" style="border-bottom: 4px solid var(--bs-primary)">
                    <div class="card-body d-flex flex-column justify-content-between align-items-start">
                        <div class="pb-2">
                            <h5 class="card-title">Pick up on Another Day</h5>
                            <p class="card-text">If submission is taking longer than expected, no worries you can pick up where you left off on another day. Simply login and go to my submissions and select "Continue Study."</p>
                        </div>
                        <button class="btn btn-warning">Continue Study</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    {{-- Account & Login --}}
    <div class="mb-5" id="section-account">
        <h2 class="h4 fw-bold mb-3 px-3 py-2 rounded" style="background-color: #1a2e44; color: #fff;">Account &amp; Login</h2>
        <p class="mb-0">A ROSSA account is required to submit data. If you don't have one, you can
            <a href="{{ route('register.redirect') }}" class="text-decoration-none">create an account here</a>.
            Already registered? <a href="{{ route('login.redirect') }}" class="text-decoration-none">Log in</a> and you'll return to this page automatically.
        </p>
    </div>

    <hr class="my-5">

    {{-- Submission Scope & Embargo --}}
    <div class="mb-5" id="section-scope">
        <h2 class="h4 fw-bold mb-4 px-3 py-2 rounded" style="background-color: #1a2e44; color: #fff;">Submission Scope &amp; Embargo Policy</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="rounded-3 p-3 h-100" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                    <p class="fw-bold mb-1" style="font-size: 0.95rem; color: #212529;">Submission Scope</p>
                    <p class="mb-0 small">Designed for individual skeletal phenotyping studies. For large multi-model screening programs, <a href="{{ route('contact') }}" class="text-decoration-none">contact us</a> for custom ingestion support.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="rounded-3 p-3 h-100" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                    <p class="fw-bold mb-1" style="font-size: 0.95rem; color: #212529;">Publication &amp; Embargo Policy</p>
                    <p class="mb-0 small">Only published studies are publicly displayed. Unpublished data is accepted with an embargo period while you seek publication.</p>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    {{-- Guides --}}
    <div class="mb-5" id="section-guides">
        <h2 class="h4 fw-bold mb-4 px-3 py-2 rounded" style="background-color: #1a2e44; color: #fff;">Quick &amp; Detailed Guides</h2>
        <div class="rounded-3 p-3" style="background-color: #eaf3fb; border: 1px solid #b5d4f4;">
            <p class="fw-semibold mb-1" style="font-size: 0.95rem;">
                <i class="bi bi-book me-2" style="color: #185FA5;"></i>Quick and detailed guides available
            </p>
            <p class="mb-0 small text-muted">
                We provide both a quick guide and a detailed step-by-step guide with screenshots. The submission form also includes a help module with instructions and tips for each section of the form.
            </p>
            <a href="{{ route('guides.data-ingestion') }}" class="btn btn-primary btn-sm mt-2">View Full Help Guide</a>
        </div>
    </div>

    <hr class="my-5">

    {{-- Experimental Approaches --}}
    <div class="mb-5" id="section-approaches">
        <h2 class="h4 fw-bold mb-4 px-3 py-2 rounded" style="background-color: #1a2e44; color: #fff;">Experimental Approaches Currently Accepted</h2>
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="rounded-3 p-3 h-100" style="background: #fff; border: 1px solid #e9ecef;">
                    <div class="rounded-2 d-flex align-items-center justify-content-center mb-3" style="width:36px; height:36px; background:#E6F1FB;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 2c0 3 8 3 8 6s-8 3-8 6M12 2c0 3-8 3-8 6s8 3 8 6" stroke="#185FA5" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="3" y1="6" x2="13" y2="6" stroke="#185FA5" stroke-width="1" stroke-linecap="round"/>
                            <line x1="3" y1="10" x2="13" y2="10" stroke="#185FA5" stroke-width="1" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <p class="fw-semibold mb-1" style="font-size: 1rem;">Genetic models</p>
                    <p class="text-muted mb-0" style="font-size: 0.875rem; line-height: 1.5;">Global knockouts, conditional knockouts, spontaneous and disease models.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="rounded-3 p-3 h-100" style="background: #fff; border: 1px solid #e9ecef;">
                    <div class="rounded-2 d-flex align-items-center justify-content-center mb-3" style="width:36px; height:36px; background:#EAF3DE;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="6" width="10" height="4" rx="2" stroke="#3B6D11" stroke-width="1.5"/>
                            <line x1="8" y1="6" x2="8" y2="10" stroke="#3B6D11" stroke-width="1.5"/>
                            <line x1="6" y1="3" x2="6" y2="6" stroke="#3B6D11" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="10" y1="3" x2="10" y2="6" stroke="#3B6D11" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="6" y1="10" x2="6" y2="13" stroke="#3B6D11" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="10" y1="10" x2="10" y2="13" stroke="#3B6D11" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <p class="fw-semibold mb-1" style="font-size: 1rem;">Preclinical drug testing</p>
                    <p class="text-muted mb-0" style="font-size: 0.875rem; line-height: 1.5;">Single agents, cocktails, or sequential treatment regimens.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="rounded-3 p-3 h-100" style="background: #fff; border: 1px solid #e9ecef;">
                    <div class="rounded-2 d-flex align-items-center justify-content-center mb-3" style="width:36px; height:36px; background:#FAEEDA;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="5" r="2.5" stroke="#BA7517" stroke-width="1.5"/>
                            <path d="M4 14c0-2.2 1.8-4 4-4s4 1.8 4 4" stroke="#BA7517" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="11" y1="8" x2="14" y2="8" stroke="#BA7517" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="12.5" y1="6.5" x2="12.5" y2="9.5" stroke="#BA7517" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <p class="fw-semibold mb-1" style="font-size: 1rem;">Gonadectomy</p>
                    <p class="text-muted mb-0" style="font-size: 0.875rem; line-height: 1.5;">Wild type animals or combined with genetic or drug variables.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="rounded-3 p-3 h-100" style="background: #fff; border: 1px solid #e9ecef;">
                    <div class="rounded-2 d-flex align-items-center justify-content-center mb-3" style="width:36px; height:36px; background:#EEEDFE;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="5" cy="5" r="2" stroke="#534AB7" stroke-width="1.5"/>
                            <circle cx="11" cy="5" r="2" stroke="#534AB7" stroke-width="1.5"/>
                            <circle cx="8" cy="11" r="2" stroke="#534AB7" stroke-width="1.5"/>
                            <line x1="7" y1="5" x2="9" y2="5" stroke="#534AB7" stroke-width="1" stroke-linecap="round"/>
                            <line x1="6.5" y1="6.7" x2="7.5" y2="9.3" stroke="#534AB7" stroke-width="1" stroke-linecap="round"/>
                            <line x1="9.5" y1="6.7" x2="8.5" y2="9.3" stroke="#534AB7" stroke-width="1" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <p class="fw-semibold mb-1" style="font-size: 1rem;">Combined studies</p>
                    <p class="text-muted mb-0" style="font-size: 0.875rem; line-height: 1.5;">Mix genetic, drug, and gonadectomy variables in custom animal groupings.</p>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    {{-- Phenotyping Analyses --}}
    <div class="mb-5" id="section-analyses">
        <h2 class="h4 fw-bold mb-2 px-3 py-2 rounded" style="background-color: #1a2e44; color: #fff;">Phenotyping Analyses Currently Accepted</h2>
        <p class="text-muted mb-4" style="font-size: 0.9rem;">
            The upload process accepts your personal spreadsheet data with quantitative measurements generated from individual samples.
        </p>
        <div class="row g-3">
            <div class="col-sm-6 col-lg">
                <div class="rounded-3 p-3 h-100" style="background: #fff; border: 1px solid #e9ecef;">
                    <div class="rounded-2 d-flex align-items-center justify-content-center mb-3" style="width:36px; height:36px; background:#E6F1FB;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="2" y="5" width="12" height="7" rx="1" stroke="#185FA5" stroke-width="1.5"/>
                            <line x1="5" y1="8.5" x2="11" y2="8.5" stroke="#185FA5" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="8" y1="2" x2="8" y2="5" stroke="#185FA5" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <p class="fw-semibold mb-1" style="font-size: 1rem;">Dual X-Ray Absorptiometry</p>
                    <p class="text-muted mb-0" style="font-size: 0.875rem; line-height: 1.5;">Whole body and regional bone mineral density and content.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg">
                <div class="rounded-3 p-3 h-100" style="background: #fff; border: 1px solid #e9ecef;">
                    <div class="rounded-2 d-flex align-items-center justify-content-center mb-3" style="width:36px; height:36px; background:#EAF3DE;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="4" stroke="#3B6D11" stroke-width="1.5"/>
                            <circle cx="8" cy="8" r="1.5" stroke="#3B6D11" stroke-width="1.5"/>
                            <line x1="8" y1="2" x2="8" y2="4" stroke="#3B6D11" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="8" y1="12" x2="8" y2="14" stroke="#3B6D11" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="2" y1="8" x2="4" y2="8" stroke="#3B6D11" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="12" y1="8" x2="14" y2="8" stroke="#3B6D11" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <p class="fw-semibold mb-1" style="font-size: 1rem;">Microcomputed Tomography</p>
                    <p class="text-muted mb-0" style="font-size: 0.875rem; line-height: 1.5;">Trabecular and cortical bone microstructure parameters.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg">
                <div class="rounded-3 p-3 h-100" style="background: #fff; border: 1px solid #e9ecef;">
                    <div class="rounded-2 d-flex align-items-center justify-content-center mb-3" style="width:36px; height:36px; background:#FAEEDA;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="3" width="10" height="10" rx="1" stroke="#BA7517" stroke-width="1.5"/>
                            <line x1="3" y1="7" x2="13" y2="7" stroke="#BA7517" stroke-width="1" stroke-linecap="round"/>
                            <line x1="3" y1="10" x2="13" y2="10" stroke="#BA7517" stroke-width="1" stroke-linecap="round"/>
                            <line x1="7" y1="3" x2="7" y2="13" stroke="#BA7517" stroke-width="1" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <p class="fw-semibold mb-1" style="font-size: 1rem;">Bone Histomorphometry</p>
                    <p class="text-muted mb-0" style="font-size: 0.875rem; line-height: 1.5;">Static and dynamic indices of bone formation and resorption.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg">
                <div class="rounded-3 p-3 h-100" style="background: #fff; border: 1px solid #e9ecef;">
                    <div class="rounded-2 d-flex align-items-center justify-content-center mb-3" style="width:36px; height:36px; background:#EEEDFE;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 2 L8 9" stroke="#534AB7" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M5 6 L8 9 L11 6" stroke="#534AB7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="3" y1="13" x2="13" y2="13" stroke="#534AB7" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <p class="fw-semibold mb-1" style="font-size: 1rem;">Mechanical Strength Testing</p>
                    <p class="text-muted mb-0" style="font-size: 0.875rem; line-height: 1.5;">Three-point bending, compression, and torsional strength measures.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg">
                <div class="rounded-3 p-3 h-100" style="background: #fff; border: 1px solid #e9ecef;">
                    <div class="rounded-2 d-flex align-items-center justify-content-center mb-3" style="width:36px; height:36px; background:#FBEAF0;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 2 L6 8 L3 13 L13 13 L10 8 L10 2" stroke="#993556" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="5" y1="5" x2="11" y2="5" stroke="#993556" stroke-width="1" stroke-linecap="round"/>
                            <circle cx="7.5" cy="10.5" r="1" fill="#993556"/>
                        </svg>
                    </div>
                    <p class="fw-semibold mb-1" style="font-size: 1rem;">Clinical Chemistry Analytes</p>
                    <p class="text-muted mb-0" style="font-size: 0.875rem; line-height: 1.5;">Serum and urine biomarkers relevant to skeletal metabolism.</p>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    {{-- Planning For Submission --}}
    <div class="mb-5" id="prepare-data">
        <h2 class="h4 fw-bold mb-3 px-3 py-2 rounded" style="background-color: #1a2e44; color: #fff;">Planning For Submission</h2>
        <p class="mb-4">
            A little preparation goes a long way. Here's what you can do ahead of time to make your submission as smooth and fast as possible.
        </p>

        <h3 class="h5 fw-bold mb-3 px-3 py-2 rounded" style="background-color: #E6F1FB; color: #185FA5;">Have These Ready Before You Start</h3>

        <div class="mb-3 p-3 border rounded" style="border-color: #dee2e6 !important;">
            <div class="d-flex align-items-start">
                <i class="bi bi-file-earmark-text-fill me-3 mt-1" style="color: #2b6cb0; font-size: 1.2rem;"></i>
                <div>
                    <strong class="d-block mb-1">Study Title, Summary, and Funding Information</strong>
                    <p class="mb-0 text-muted" style="font-size: 0.92rem;">You will be asked to provide a study title, a brief summary or abstract, and funding source information during the metadata sections of the submission form. Having these written out ahead of time will save you time.</p>
                </div>
            </div>
        </div>

        <div class="mb-3 p-3 border rounded" style="border-color: #dee2e6 !important;">
            <div class="d-flex align-items-start">
                <i class="bi bi-table me-3 mt-1" style="color: #2b6cb0; font-size: 1.2rem;"></i>
                <div>
                    <strong class="d-block mb-1">Your Data in CSV Format</strong>
                    <p class="mb-0 text-muted" style="font-size: 0.92rem;">ROSSA currently accepts spreadsheet data files in <strong>CSV format only</strong>. For database security purposes we cannot accept Excel file formats (.xlsx, .xls). If your data is in Excel, please convert your files to CSV before submission.</p>
                </div>
            </div>
        </div>

        <div class="mb-3 p-3 border rounded" style="border-color: #dee2e6 !important;">
            <div class="d-flex align-items-start">
                <i class="bi bi-plus-circle-fill me-3 mt-1" style="color: #2b6cb0; font-size: 1.2rem;"></i>
                <div>
                    <strong class="d-block mb-1">Add Body Weight Data as a Column in Your Spreadsheet</strong>
                    <p class="mb-0 text-muted" style="font-size: 0.92rem;">We strongly encourage including a body weight column in your data spreadsheet. Body weight is an important covariate in skeletal phenotyping studies and greatly enhances the interpretability and comparability of your data.</p>
                </div>
            </div>
        </div>

        <div class="mb-4 p-3 border rounded" style="border-color: #dee2e6 !important;">
            <div class="d-flex align-items-start">
                <i class="bi bi-download me-3 mt-1" style="color: #2b6cb0; font-size: 1.2rem;"></i>
                <div>
                    <strong class="d-block mb-1">Optional Use of Spreadsheet Templates</strong>
                    <p class="mb-0 text-muted" style="font-size: 0.92rem;">While ROSSA's data ingestion process is designed to work directly with your existing spreadsheet files, we also provide <a href="{{ route('download.index') }}" class="text-decoration-none">spreadsheet template files</a> that are pre-formatted for upload.</p>
                </div>
            </div>
        </div>

        <h3 class="h5 fw-bold mb-3 px-3 py-2 rounded" style="background-color: #E6F1FB; color: #185FA5;">How Your Files Are Stored and Shared</h3>

        @php
            $content2 = [
                [
                    'title' => 'Your Original Data Files Are Archived in Object Storage',
                    'description' => 'All uploaded data files are stored in their original form to ensure authenticity. Object storage keeps each file as an independent object — easily accessed, retrieved, and managed — without altering the original content.',
                ],
                [
                    'title' => 'Spreadsheet Data Is Also Written into a Database',
                    'description' => 'During upload, your spreadsheet data is also ingested into a PostgreSQL database. This enables efficient searching through the ROSSA datacenter and programmatic access via the API.',
                ]
            ];
        @endphp

        @foreach($content2 as $index => $item)
        <div class="mb-3">
            <div class="d-flex">
                <x-circle-number class="me-4">{{ $index + 1 }}</x-circle-number>
                <div>
                    <strong class="mb-1 d-block fw-semibold" style="font-size: 1.05rem;">{{ $item['title'] }}</strong>
                    @if($item['description'])
                        <p class="mb-1 text-muted" style="font-size: 0.92rem;">{{ $item['description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        <div class="mt-3 p-3 border rounded d-flex align-items-start" style="border-color: #f6c343 !important; background-color: #fffdf0;">
            <i class="bi bi-exclamation-triangle-fill text-warning me-3 mt-1" style="font-size: 1.2rem;"></i>
            <p class="my-auto mb-0" style="font-size: 0.92rem;"><strong>Your original data files will be publicly accessible</strong> to users browsing the datacenter. Please ensure the contents of your files are presentable and clearly organized before uploading.</p>
        </div>
    </div>

    <hr class="my-5">

    {{-- File Validation --}}
    <div class="mb-5" id="file-validation">
        <h2 class="h4 fw-bold mb-3 px-3 py-2 rounded" style="background-color: #1a2e44; color: #fff;">File Validation</h2>
        <p class="mb-4">
            To ensure your data is accurately read and annotated, each spreadsheet enters a validation viewer that provides human-in-the-loop review.
        </p>
        <div class="row g-4 align-items-start">
            <div class="col-12 col-lg-5">
                <p class="fw-semibold mb-2" style="font-size: 0.95rem;">During validation you will:</p>
                <ul style="font-size: 0.92rem; line-height: 1.8;">
                    <li>Confirm or correct matched measurement parameters</li>
                    <li>Annotate individual animals into their respective groupings (e.g. control vs. experimental)</li>
                </ul>
            </div>
            <div class="col-12 col-lg-7">
                <div class="p-3 rounded" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                    <p class="fw-semibold mb-3" style="font-size: 0.8rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.05em;">Example: Parameter Matching</p>
                    <table class="table table-borderless mb-0" style="font-size: 0.88rem;">
                        <thead>
                            <tr>
                                <th class="fw-semibold pb-2" style="width: 42%; color: #495057;">Spreadsheet Header</th>
                                <th style="width: 8%;"></th>
                                <th class="fw-semibold pb-2" style="color: #495057;">ROSSA Match</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-2">Tt.Ar mm^2</td>
                                <td><i class="bi bi-chevron-right text-primary"></i></td>
                                <td><div class="border rounded px-2 py-1 bg-white">Total Area (mm²) <i class="bi bi-chevron-down float-end me-1"></i></div></td>
                            </tr>
                            <tr>
                                <td class="py-2">Ct.Ar mm^2</td>
                                <td><i class="bi bi-chevron-right text-primary"></i></td>
                                <td><div class="border rounded px-2 py-1 bg-white">Cortical Area (mm²) <i class="bi bi-chevron-down float-end me-1"></i></div></td>
                            </tr>
                            <tr>
                                <td class="py-2">Ps.Pm mm</td>
                                <td><i class="bi bi-chevron-right text-primary"></i></td>
                                <td><div class="border rounded px-2 py-1 bg-white">Periosteal Perimeter (mm) <i class="bi bi-chevron-down float-end me-1"></i></div></td>
                            </tr>
                            <tr>
                                <td class="py-2">Po.N</td>
                                <td><i class="bi bi-chevron-right text-primary"></i></td>
                                <td><div class="border rounded px-2 py-1 bg-white">Pore Number (n) <i class="bi bi-chevron-down float-end me-1"></i></div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom CTA --}}
    <hr class="my-5">

    <div class="row mb-5">
        <div class="col-12 text-center">
            <p class="fw-semibold mb-1" style="font-size: 1.1rem;">Ready to get started?</p>
            <p class="text-muted mb-4" style="font-size: 0.95rem;">The process is straightforward and you can save your progress at any time.</p>
            @auth
                <a href="{{ route('depositor-agreement') }}" class="btn btn-primary btn-lg px-5">Start Your Data Submission</a>
            @else
                <a href="{{ route('login.redirect') }}" class="btn btn-primary btn-lg px-5">Log In to Begin Submission</a>
            @endauth
        </div>
    </div>

</div>
@endsection
