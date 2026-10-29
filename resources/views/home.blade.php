@extends('layout')

@section('content')
    <section class="hero-section text-white py-5">
        <div class="container">
            <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold">Welcome to the Rodent Open Science Skeletal Archive</h1>
                <p class="fs-5">An open-access resource to archive and share rodent skeletal phenotyping data.</p>
            </div>

            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="d-grid gap-3">
                <a href="{{ route('data-submission') }}" class="btn btn-lg fs-5" style="background: #003f5c; color: #ffffff;">Start Your Data Submission</a>
                <a href="{{ route('core-facility.home') }}" class="btn btn-lg fs-5" style="background: #0077b6; color: #ffffff;">Register Your Core Facility</a>
                <a href="{{ route('about') }}" class="btn btn-lg fs-5" style="background: transparent; color: #efaf49; border: 2px solid #efaf49;;">Learn More</a>
                </div>
            </div>
            </div>
        </div>
    </section>


    <div class="container-fluid p-0">

    {{-- Get Involved band --}}
    <div class="get-involved-band py-4">
        <div class="container">

            <div class="row g-4">
                <!-- Feature 1: Contribute Your Data -->
                <div class="col-md-4">
                     <div class="card h-100 feature-card shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h3 class="card-title mb-3">Contribute Your Data</h3>
                            <p class="card-text">
                                Sharing skeletal phenotyping data has never been easier.
                            </p>
                            <a href="{{ route('data-submission') }}" class="mt-auto">
                                Learn more about data submission <i class="bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Feature 2: Explore and Share Data -->
                <div class="col-md-4">
                     <div class="card h-100 feature-card shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h3 class="card-title mb-3">Explore and Share Data</h3>
                            <p class="card-text">
                                Coming Soon! A robust data center that will allow researchers to explore skeletal phenotyping data.
                            </p> 
                            <a href="{{ route('search.index') }}" class="mt-auto">
                                Search public datasets <i class="bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Feature 3: Core Facility Registry -->
                <div class="col-md-4">
                     <div class="card h-100 feature-card shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h3 class="card-title mb-3">Core Facility Registry</h3>
                            <p class="card-text">
                                The <a href="{{ route('core-facility.home') }}">Core Facility Registry</a> connects researchers with specialized skeletal phenotyping facilities.
                            </p>                      
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- News & Updates section --}}
    @if ($newsPosts->isNotEmpty())
        <section class="news-section py-5" aria-labelledby="latest-news-heading">
            <div class="container">
                <span class="eyebrow d-block mb-2">News &amp; Updates</span>
                <div class="news-heading-row d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <h2 id="latest-news-heading" class="mb-0">Latest News</h2>
                    <a href="{{ route('news.index') }}">View all news <i class="bi-arrow-right" aria-hidden="true"></i></a>
                </div>
 
                <div class="d-flex flex-column gap-3">
                    @foreach ($newsPosts as $newsPost)
                        @include('news._blast', [
                            'newsPost' => $newsPost,
                            'headingTag' => 'h3',
                            'variant' => 'card',
                        ])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
@endsection