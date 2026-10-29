<div class="py-5">
    <div class="container">
        <div class="row min-vh-75 align-items-center">
            <!-- Registration Form Column - Shows first on mobile -->
            <div class="col-lg-7 order-1 order-lg-2 mb-5 mb-lg-0">
                {{ $slot }}
            </div>

            <!-- Mission/Display Copy Column - Shows second on mobile -->
            <div class="col-lg-5 order-2 order-lg-1 pe-lg-5">
                <div class="mb-4 mb-lg-0">
                    <h1 class="display-5 mb-4">Welcome to ROSSA</h1>
                    <p class="lead mb-4">
                        The Rodent Open Science Skeletal Archive is a comprehensive repository for rodent skeletal research data, 
                        promoting open science and collaboration in the research community.
                    </p>
                    
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-input-cursor-text text-primary fs-4"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-2">Data Submission Portal</h5>
                                    <p class="text-muted mb-0">
                                        Efficiently submit skeletal phenotyping datasets through our user-friendly interface
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-bar-chart-line-fill text-primary fs-4"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-2">Advanced Data Center</h5>
                                    <p class="text-muted mb-0">
                                        Explore and analyze studies with powerful tools and advanced analytical approaches
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-people-fill text-primary fs-4"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-2">Core Facility Registry</h5>
                                    <p class="text-muted mb-0">
                                        Connect with phenotyping facilities worldwide and access specialized expertise
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-database-fill text-primary fs-4"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-2">Database</h5>
                                    <p class="text-muted mb-0">
                                        Comprehensive storage and management of skeletal phenotyping research data
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>