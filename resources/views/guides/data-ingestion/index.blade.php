@extends('layout')

@section('title', 'Data Ingestion Guide')
@section('description', 'Guide to submitting skeletal phenotyping data to ROSSA, including accepted experimental approaches and analysis types.')

@section('content')
<div class="py-5">
    <div class="container">

        <x-guide-modal />

        <div class="row g-5 me-0">

            {{-- Table of Contents sidebar --}}
            

            {{-- Main content --}}
            <div class="col"
                 data-bs-spy="scroll"
                 data-bs-target="#toc"
                 data-bs-offset="80"
                 data-bs-smooth-scroll="true"
                 tabindex="0">
                @include('guides.data-ingestion._content')
            </div>

            <div class="d-none d-lg-block" style="width: 350px;">
                <nav id="toc" class="sticky-top small z-1" style="top: 1.5rem; max-height: calc(100vh - 3rem); overflow-y: auto;">
                    <p class="fw-bold text-uppercase text-muted mb-2" style="font-size: .7rem; letter-spacing: .06em;">Table of Contents</p>
                    <nav class="nav flex-column">

                        <a class="nav-link py-1 px-0 fw-semibold" href="#current-experimental-approaches">Current Experimental Approaches Being Accepted</a>
                        <nav class="nav flex-column ms-3">
                            <a class="nav-link py-1 px-0" href="#genetic-animal-models">Genetic Animal Models</a>
                            <a class="nav-link py-1 px-0" href="#preclinical-drug-testing">Preclinical Drug Testing</a>
                            <a class="nav-link py-1 px-0" href="#gonadectomy">Gonadectomy</a>
                            <a class="nav-link py-1 px-0" href="#complex-combined">Complex Combined Experimental Studies</a>
                            <a class="nav-link py-1 px-0" href="#future-approaches">Future Experimental Approaches in the Pipeline</a>
                        </nav>

                        <a class="nav-link py-1 px-0 fw-semibold" href="#accepted-analyses">Currently Accepted Skeletal Phenotyping Analyses Approaches</a>

                        <a class="nav-link py-1 px-0 fw-semibold" href="#preparing-data">Preparing Your Data for Upload</a>
                        <nav class="nav flex-column ms-3">
                            <a class="nav-link py-1 px-0" href="#data-file-types">Data File Types</a>
                            <a class="nav-link py-1 px-0" href="#preparing-spreadsheet">Preparing Your Personal Spreadsheet Files</a>
                            <a class="nav-link py-1 px-0" href="#preconfigured-measurements">ROSSA Accepts Preconfigured &amp; Custom Measurements</a>
                            <a class="nav-link py-1 px-0" href="#data-templates">Optional Use of Data Template Spreadsheet Files</a>
                        </nav>

                        <a class="nav-link py-1 px-0 fw-semibold" href="#data-submission">Data Submission into ROSSA</a>
                        <nav class="nav flex-column ms-3">
                            <a class="nav-link py-1 px-0" href="#user-account">User Account / Login</a>
                            <a class="nav-link py-1 px-0" href="#submission-portal">Data Submission Portal</a>
                        </nav>

                        <a class="nav-link py-1 px-0 fw-semibold" href="#form-sections">Sections of the Data Submission Form</a>
                        <nav class="nav flex-column ms-3">
                            <a class="nav-link py-1 px-0" href="#section-1">Section 1: Study Information</a>
                            <a class="nav-link py-1 px-0" href="#section-2">Section 2: Subject Areas</a>
                            <a class="nav-link py-1 px-0" href="#section-3">Section 3: Investigators</a>
                            <a class="nav-link py-1 px-0" href="#section-4">Section 4: Animal Experimentation</a>
                            <nav class="nav flex-column ms-3">
                                <a class="nav-link py-1 px-0" href="#animal-experimentation-setup">Setup</a>
                                <a class="nav-link py-1 px-0" href="#animal-models">Animal Models</a>
                                <a class="nav-link py-1 px-0" href="#drug-treatments">Drug Treatments</a>
                                <a class="nav-link py-1 px-0" href="#drug-administration-plan">Drug Administration Plans</a>
                                <a class="nav-link py-1 px-0" href="#gonadectomy-procedure">Gonadectomy Procedure</a>
                            </nav>
                            <a class="nav-link py-1 px-0" href="#section-5">Section 5: Phenotype Analysis</a>
                            <nav class="nav flex-column ms-3">
                                <a class="nav-link py-1 px-0" href="#sample-groups">Sample Groups</a>
                                <a class="nav-link py-1 px-0" href="#comparison-groups">Comparison Groups</a>
                                <a class="nav-link py-1 px-0" href="#upload-data">Upload Data</a>
                                <nav class="nav flex-column ms-3">
                                    <a class="nav-link py-1 px-0" href="#upload-map-measurements">Map Measurements</a>
                                    <a class="nav-link py-1 px-0" href="#upload-group-samples">Group Samples</a>
                                    <a class="nav-link py-1 px-0" href="#upload-review">Review</a>
                                </nav>
                            </nav>
                            <a class="nav-link py-1 px-0" href="#section-6">Section 6: Report</a>
                            <nav class="nav flex-column ms-3">
                                <a class="nav-link py-1 px-0" href="#report-information">Study Information</a>
                                <a class="nav-link py-1 px-0" href="#report-data-exploration">Data Exploration</a>
                                <a class="nav-link py-1 px-0" href="#report-download">Download Original Data</a>
                            </nav>
                            <a class="nav-link py-1 px-0" href="#study-submission">Study Submission</a>
                        </nav>

                    </nav>
                </nav>
            </div>

        </div>

    </div>
</div>
@endsection