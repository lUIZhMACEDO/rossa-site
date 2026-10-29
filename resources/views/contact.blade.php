@extends('layout')

@section('title', 'Contact Us')
@section('description', 'Have questions or feedback? We\'d love to hear from you. Contact the ROSSA team with questions, feedback, or collaboration inquiries about the archive.')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="text-center mb-1; pb-1; pt-3">
        <h1 class="display-5 fw-normal text-dark mb-1">Contact Us</h1>
        <div class="mx-auto bg-dark mb-4" style="width: 60px; height: 1px;"></div>
    </div>

    <!-- Main Content -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <p class="fs-5 text-center">
                    Have questions or feedback? We'd love to hear from you. Fill out the form below and we'll get back to you as soon as possible.
                </p>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('contact.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea
                                class="form-control @error('message') is-invalid @enderror"
                                id="message"
                                name="message"
                                rows="6"
                                required
                            >{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
