@extends('layout')

@section('title', 'Contributor Responsibilities & Agreement')
@section('description', 'Review contributor responsibilities, the depositor agreement, and disclaimer before submitting data to ROSSA.')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="text-center mb-3 pt-3">
        <h1 class="display-6 fw-bold text-dark mb-1">Responsibilities of Contributors</h1>
    </div>


    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Introduction Paragraph -->
            <p class="text-center mb-4">Researchers who contribute data to the ROSSA are instrumental in upholding the quality, integrity, and usefulness of this resource. Contributors are expected to provide data that is <strong>accurate, complete, and supported by appropriate metadata and documentation</strong>. This accompanying information is essential for enabling other users to interpret, validate, and effectively utilize the data within their own research.</p>
        </div>
    </div>

    <hr class="mb-4" style="margin-top: 3.5rem;">


    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Depositor Agreement Section -->
            <div>
                <h2 class="fs-3 fw-semibold mt-4 mb-3">Depositor Agreement and Disclaimer</h2>

                <p class="text-dark mb-0">By submitting data or copyrights to the <strong>Rodent Open Science Skeletal Archive (ROSSA)</strong> repository ("Repository"), the Depositor ("You") agree to the following terms and conditions:</p>

                <!-- Terms List -->
                <div class="d-grid gap-4 mt-4">
                    <!-- Term 1: License Grant -->
                    <div class="d-flex">
                        <x-circle-number class="me-4">1</x-circle-number>
                        <div>
                            <h3 class="h5 fw-semibold mb-2 mt-1">License Grant</h3>
                            <div>
                                <ol class="ps-3" style="list-style-type: lower-alpha;">
                                    <li class="mb-2">
                                        <strong>License to Repository:</strong> You hereby grant to the Repository a <strong>non-exclusive, worldwide, royalty-free, transferable license</strong> to store, reproduce, publish, distribute, publicly display, and otherwise make available the submitted data and/or associated copyrights (the "Data"). This license includes the right for the Repository to use the Data for the operation, maintenance, and promotion of the Repository.
                                    </li>
                                    <li class="mb-2">
                                        <strong>License to the Public:</strong> Except as otherwise provided herein, the Repository will make the Data available to the public under the terms of the <strong>Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License (CC BY-NC-SA 4.0)</strong>.
                                    </li>
                                    <li class="mb-0">
                                        <strong>Commercial Use:</strong> In addition to the CC BY-NC-SA 4.0 license, you grant to the Repository the right to manage, authorize, and enter into commercial use licenses or sublicenses of the Data. All revenues from such commercial use shall be applied toward the continued mission, maintenance, and sustainability of the Repository.
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>


                    <!-- Term 2: Commercial Licensing -->
                    <div class="d-flex">
                        <x-circle-number class="me-4">2</x-circle-number>
                        <div>
                            <h3 class="h5 fw-semibold mb-2 mt-1">Commercial Licensing</h3>
                            <p>
                                Contributors retain ownership of their individual Data and may, at their discretion, enter into separate agreements to license their Data directly to third parties. The Repository will not restrict or interfere with such individual arrangements.
                            </p>
                            <p>
                                To support the long-term sustainability of the Repository, the Repository reserves the right to negotiate and grant commercial licenses that cover aggregated collections of Data contributed by multiple users. Revenues from such licenses will be used to maintain and enhance Repository services. These aggregate licensing agreements are not intended to prevent contributors from separately licensing their own Data to commercial entities.
                            </p>
                        </div>
                    </div>


                    <!-- Term 3: Representations and Warranties -->
                    <div class="d-flex">
                        <x-circle-number class="me-4">3</x-circle-number>
                        <div>
                            <h3 class="h5 fw-semibold mb-2 mt-1">Representations and Warranties</h3>
                            <p class="text-dark mb-2">You represent and warrant that:</p>
                            <ul>
                                <li class="mb-1">
                                    You have full authority to deposit the Data under these terms
                                </li>
                                <li class="mb-1">
                                    You either own the Data or have obtained all necessary rights, permissions, and consents to license it under the CC BY-NC-SA 4.0
                                </li>
                                <li class="mb-1">
                                    To the best of your knowledge, the Data does not infringe or misappropriate any third-party rights, including intellectual property, privacy, or other legal rights
                                </li>
                                <li class="mb-1">
                                    The Data does not contain any confidential, proprietary, or restricted content that you are not authorized to disclose
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Term 4: Indemnification -->
                    <div class="d-flex">
                        <x-circle-number class="me-4">4</x-circle-number>
                        <div>
                            <h3 class="h5 fw-semibold mb-2 mt-1">Indemnification</h3>
                            <p>
                                You agree to indemnify and hold harmless the Repository and its host institution from any claims, damages, liabilities, costs, or expenses (including reasonable attorneys' fees) arising from your breach of these representations and warranties.
                            </p>
                        </div>
                    </div>

                    <!-- Term 5: Modification and Removal -->
                    <div class="d-flex">
                        <x-circle-number class="me-4">5</x-circle-number>
                        <div>
                            <h3 class="h5 fw-semibold mb-2 mt-1">Modification and Removal</h3>
                            <p>
                                The Repository reserves the right to reject, remove, or modify deposited Data at its discretion, particularly in response to legal claims or violations of applicable laws or policies.
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Agreement Confirmation -->
                <div x-data="{ agreed: false }">
                    <div class="d-flex align-items-center mt-4 form-control-lg bg-lightblue">
                        <input type="checkbox" id="agreement" class="form-check-input mt-0 me-3 border-primary" x-model="agreed" />
                        <label for="agreement" class="form-check-label fw-bold" style="font-size: 15px;">I acknowledge that I have read, understood, and agree to be bound by the terms and conditions outlined in this Depositor Agreement.</label>
                    </div>
                    
                    <div class="mt-4 mb-5" x-show="agreed">                          
                        <form action="{{ route('study.store') }}" method="POST" class="text-center">                             
                            @csrf                             
                            <button type="submit" id="submitStudyButton" class="btn btn-primary btn-lg px-5">Initiate Study Submission</button>
                        </form>                       
                    </div>
                </div>

            </div>
       
        </div>
    </div>
</div>
@endsection
