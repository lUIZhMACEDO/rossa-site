@extends('layout')

@section('title', 'About')
@section('description', 'The Rodent Open Science Skeletal Archive (ROSSA) is a comprehensive resource designed to advance skeletal biology research through open data sharing, collaborative analysis, and global networking. ')

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="text-center mb-4">
        <h1 class="display-5 fw-normal text-dark mb-3">About ROSSA</h1>
        <p class="">Rodent Open Science Skeletal Archive</p>
        <div class="mx-auto bg-dark" style="width: 60px; height: 1px;"></div>
    </div>

    <!-- Introduction -->
    <div class="row justify-content-center mb-4">
        <div class="col-lg-10">
            <p class="fs-5 text-dark">
                The Rodent Open Science Skeletal Archive (ROSSA) is a comprehensive resource designed to advance
                skeletal biology research through open data sharing, collaborative analysis, and global networking.
                ROSSA brings together three essential components that address the critical needs of the skeletal research
                community: data preservation, discovery tools, and research infrastructure connectivity.
            </p>
        </div>
    </div>

    <!-- Rationale Section -->
   <!-- Rationale Section -->
<div class="mb-4">
    <div class="row justify-content-center">
        <div class="col">
            <h2 class="fw-normal text-dark mb-3">Rationale for Building ROSSA</h2>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col">
            <p class="mb-3">
                Rodent models have long been central to understanding human skeletal disease.
                The clinical phenotyping of most patients is highly limited, making it impossible
                to use patient data to identify the causal determinants of skeletal diseases. Even
                when DNA sequencing has been used to identify the causal gene in patients with rare
                monogenic skeletal diseases, once identified, rodents remain the go-to animal model to
                understand how genetic abnormalities lead to disease pathology and test the efficacy of
                candidate interventions. Unlike humans, the skeletal phenotyping performed on rodent animal
                models is comprehensive and impactful at elucidating mechanisms of skeletal regulation.
                Therefore, we believe a repository that archives and shares rodent skeletal phenotyping data
                will truly advance our knowledge of human skeletal diseases.
            </p>

            <p class="mb-1">
                Over the last 40 years, a vast range of experimental questions have been tested in rodents to
                understand mechanisms of human skeletal disease. The range of experimental questions include:
                1) genetic (global knockout, conditional knockout, disease model), 2) therapeutic impact (small
                molecules, biologics, probiotics, dietary changes, fecal transplants, stem cell transplantation),
                and 3) procedures that challenge the skeleton such as gonadectomy or unloading. The abundance of bone
                phenotyping data being generated combined with the diversity of experimental questions being tested
                makes for an incredibly rich data source.
            </p>
        </div>
    </div>
</div>
<!-- ROSSA Features Overview -->
<div class="mb-5">
    <div class="row justify-content-center">
        <div class="">
            <h2 class="fw-normal text-dark mb-3">Features of the ROSSA</h2>
            <p class="mb-4">In its very first iteration, the ROSSA contains three major features designed to advance skeletal phenotyping research and collaboration.</p>

            <div class="row g-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 me-3" style="width: 55px; height: 55px;">
                                    <span class="fs-4">📊</span>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">Data Submission Portal</h5>
                                    <p class="text-muted mb-0">Efficiently submit skeletal phenotyping datasets through our user-friendly interface</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 me-3" style="width: 55px; height: 55px;">
                                    <span class="fs-4">🔬</span>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">Advanced Data Center</h5>
                                    <p class="text-muted mb-0">Explore and analyze studies with powerful tools and advanced analytical approaches</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 me-3" style="width: 55px; height: 55px;">
                                    <span class="fs-4">🏢</span>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">Core Facility Registry</h5>
                                    <p class="text-muted mb-0">Connect with phenotyping facilities worldwide and access specialized expertise</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 me-3" style="width: 55px; height: 55px;">
                                    <span class="fs-4">🗄️</span>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">Database</h5>
                                    <p class="text-muted mb-0">Comprehensive storage and management of skeletal phenotyping research data</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Data Submission Portal Section -->
<div class="mt-5">
    <div class="row justify-content-center">
        <div class="col">

    <div class="mb-3">
        <h2 class="fw-normal text-dark mb-2">Data Submission</h2>

        <h3 class="h5 text-primary fw-bold mb-2">Current Experimental Approaches Being Accepted</h3>

            <div class="row g-4 mb-4">
            <div class="col-md-12">
                <span class="fw-bold">Genetic Animal Models:</span>
                <span class="">Skeletal phenotyping studies using genetically modified animal models are currently being
                    accepted.  The submission process seeks to collect data on a wide array of genetic mutants including global
                    knockouts, conditional knockouts, spontaneous, and disease models.
                </span>
                <div class="mt-2">
                <span class="fw-bold">Preclinical Drug Testing:</span>
                <span class=""> Skeletal phenotyping studies that test and compare individual drug treatments, drug cocktails,
                    or even drug treatments performed in a sequential manner are currently being accepted.
                </span>
                <div class="mt-2">
                <span class="fw-bold">Gonadectomy:</span>
                <span class=""> Skeletal phenotyping studies where a gonadectomy was performed on wild type animals or in conjunction
                    with a genetic mutant or a drug treatment.
                </span>
                 <div class="mt-2">
                <span class="fw-bold">Complex Combined Experimental Studies:</span>
                <span class=""> We understand how complicated animal studies can get and have designed the ingestion process in a
                    manner that allows investigators to combine genetic, drug, and gonadectomy variables in desired animal groupings.
                </span>

            </div>
        <div class="mt-4">
        <h3 class="h5 text-primary fw-bold mb-2">Future Experimental Approaches in the Pipeline</h3>
        <div class="mt-1">
            <span class=""> Our group is working hard to design the data submission process to be as comprehensive as possible.
                    Future plans include the experimental categories detailed below. If you desire an experimental category that currently
                    is not available, please contact us. We are here to support the skeletal community.
                </span>
                <div class="mt-2">
                <ul class="ms-3">
                    <li class="mb-1">Mechanical Loading/Unloading Studies</li>
                    <li class="mb-1">Comparing Animal Diets (Testing Nutrients, Phytochemicals, Probiotics)</li>
                    <li class="mb-1">Circadian Rythmn Studies (Comparing different light/dark cycles)</li>
               </ul>
        <h3 class="h5 text-primary fw-bold mb-2">Currently Accepted Skeletal Phenotyping Analyses Approaches</h3>
            <span class=""> We have focused on collecting phenotyping analysis approaches that are widely and
                routinely used by members of the scientific community (detailed below). Please contact us, if you believe we
                should include other analysis types.
            </span>
            <div class="mt-2">
            <div class="col-md-6">
                <ul class=" ms-3">
                    <li class="mb-1">Dual X-Ray Absorptiometry</li>
                    <li class="mb-1">Microcomputed Tomography</li>
                    <li class="mb-1">Bone Histomorphometry</li>
                    <li class="mb-1">Mechanical Strength Testing</li>
                    <li class="mb-1">Clinical Chemistry Analytes</li>
               </ul>
            </div>
        </div>

    </div>

    <!-- Data Center Section -->
    <div class="mt-5">
        <h2 class="fw-normal text-dark mb-2">Data Center</h2>
        <h3 class="h5 text-primary fw-bold mb-2">Discover, Explore, and Analyze Across Studies</h3>

        <p class="mb-4">
            Actively under construction, we are designing ROSSA's data center to enable advanced querying capabilities
            with specific focus areas such as genetic studies, drug studies, thematic topics in the field. The data
            center will be highly interoperable with other databases thereby allowing rapid learning of subject areas.
            The datacenter will provide users the ability to download the original data files contributed by investigators and
            the processed data in box plots and table views.  Studies across different analysis types will be experimental to
          control ratio normalized and viewed as a scatter plot or table view with options to change measurment paramaters.
            This powerful tool will transform how researchers interact with and derive insights from skeletal phenotyping studies.
        </p>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <h5 class="h6 fw-bold text-dark mb-2">Individual Study Exploration</h5>
                <ul class="ms-3">
                    <li class="mb-1">Access to Original Data Files</li>
                    <li class="mb-1">Study Synopis Page</li>
                    <li class="mb-1">Downloadable Datasets with Proper Attribution</li>
                </ul>
            </div>

            <div class="col-md-4">
                <h5 class="h6 fw-bold text-dark mb-2">Cross-Study Analysis Capabilities</h5>
                <ul class="ms-3">
                    <li class="mb-1">Multi-study data visualization through interactive scatter plots</li>
                    <li class="mb-1">Comparative analysis tools across different conditions</li>
                    <li class="mb-1">Meta-analysis support with harmonized data standards</li>
                  <ul>
            </div>

            <div class="col-md-4">
                <h5 class="h6 fw-bold text-dark mb-2">Advanced Discovery Features</h5>
                <ul class="ms-3">
                    <li class="mb-1">Search functionality across studies by phenotype</li>
                    <li class="mb-1">Filter studies by experimental parameters</li>
                    <li class="mb-1">Identify similar studies and experimental replicates</li>
              </ul>
            </div>
        </div>

    </div>

    <!-- Core Facility Registry Section -->
    <div class="my-5">
        <h2 class="fw-normal text-dark mb-2">Core Facility Registry</h2>
        <h3 class="h5 text-primary fw-bold mb-2">Connecting Skeletal Phenotyping Expertise with Investigators</h3>

        <p class="mb-4">
            ROSSA's Core Facility Registry addresses a critical need in the skeletal research community:
            connecting researchers with specialized bone phenotyping expertise and equipment. Core facilities can
            showcase their unique capabilities, while researchers can easily identify the right partners for their
            specific phenotyping needs. This registry strengthens the global skeletal research network and ensures
            that cutting-edge phenotyping capabilities are accessible to researchers worldwide.
        </p>
        </p>
        <div class="row g-4 mb-4">
            <div class="col-md-5">
                <h5 class="h6 fw-bold text-dark mb-2">Facility Profiles Include:</h5>
                <ul class="ms-3">
                    <li class="mb-1">Contact information and institutional affiliations</li>
                    <li class="mb-1">Phenotyping Service offerings and collaborative opportunities</li>
                </ul>
            </div>

            <div class="col-md-6">
                <h5 class="h6 fw-bold text-dark mb-2">Benefits for Research Community:</h5>
                <ul class="ms-3">
                    <li class="mb-1">Democratizes access to specialized equipment</li>
                    <li class="mb-1">Facilitates collaborations between institutions</li>
                    <li class="mb-1">Reduces barriers to comprehensive phenotyping</li>
                    <li class="mb-1">Promotes standardization of analytical approaches</li>
                    <li class="mb-1">Supports researchers with limited resources</li>
                </ul>
            </div>
        </div>


    </div>
     <!-- Database Description Section -->
    <div class="mb-5">
        <h2 class="fw-normal text-dark mb-2">Data Storage</h2>
        <h3 class="h5 text-primary fw-bold mb-2">Your Data is Safe with Us</h3>

        <p class="mb-2">
            The data submitted to ROSSA will be stored and managed by staff at The University of Connecticut's High Performance
            Computing Facility (HPC). This is a state of the art facility directed by Dr. Ion Moraru, who has experience at
            managing repositories for a variety of national consortia. The data will be securely backed up in three geolocations.
            </p>
        <p class="mb-4">
            ROSSA will store your data in two ways. First, all of your original data files (spreadsheet and image files)
            are uploaded into ROSSA and stored as object storage in their original form. This is to ensure authenticity of
            the original data and provide a crucial reference for reading the data into database tables. Spreadsheet data
            will also be read into database tables (PostgreSQL database) to enable efficient searching via the datacenter
            and through API access by those interested.
         </p>
    <div class="row g-4 mb-4">
    <div class="col-md-4">
        <h5 class="h6 fw-bold text-dark mb-2">Security & Reliability:</h5>
        <ul class="ms-3">
            <li class="mb-1">Triple redundancy with secure backups across three geographic locations</li>
            <li class="mb-1">Enterprise-grade security protocols and access controls</li>
            <li class="mb-1">Disaster recovery and business continuity planning</li>
        </ul>
    </div>

    <div class="col-md-4">
        <h5 class="h6 fw-bold text-dark mb-2">Data Accessibility:</h5>
        <ul class="ms-3">
            <li class="mb-1">Efficient searching through the integrated datacenter interface</li>
            <li class="mb-1">API access for programmatic data retrieval</li>
            <li class="mb-1">Original files always available as authoritative reference</li>
            <li class="mb-1">Structured database queries for complex research needs</li>
        </ul>
    </div>

    <div class="col-md-4">
        <h5 class="h6 fw-bold text-dark mb-2">Performance Benefits:</h5>
        <ul class="ms-3">
            <li class="mb-1">High-performance computing infrastructure</li>
            <li class="mb-1">Optimized database queries for large datasets</li>
            <li class="mb-1">Scalable architecture to grow with research needs</li>
            <li class="mb-1">Fast data retrieval and analysis capabilities</li>
        </ul>
    </div>
</div>

    </div>

    <!-- Vision Section -->
    <div class="bg-light p-4 rounded-3">
        <h2 class="fw-normal text-dark mb-3">Vision for the Future</h2>
        <p class="mb-3">
            ROSSA represents a paradigm shift toward open, collaborative skeletal research. By integrating data submission, analysis tools, and research infrastructure in a single platform, we're creating an ecosystem that accelerates discovery and fosters innovation. Our commitment to open science ensures that valuable research data remains accessible, driving progress in understanding skeletal biology and developing new treatments for bone diseases.
        </p>
        <p class="mb-0 fw-bold">
            Join us in building the future of skeletal research—submit your data, explore our growing repository, and connect with the global community of researchers dedicated to advancing bone health.
        </p>
    </div>
</div>
@endsection




