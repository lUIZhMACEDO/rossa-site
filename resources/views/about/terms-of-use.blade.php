@extends('layout')

@section('title', 'Terms of Use')
@section('description', 'Repository policies for ROSSA, including data licensing, commercial use, contributor responsibilities, and disclaimers.')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="text-center mb-4 pt-3">
        <h1 class="display-5 fw-normal text-dark mb-1">Repository Policies and Terms of Use</h1>
        <div class="mx-auto bg-dark" style="width: 60px; height: 1px;"></div>
    </div>
    
   <div class="">
        <div >
            <h2 class="mt-2 mb-2">Licensing and Usage</h2>
            <p>The data and copyrights provided through this repository are 
                licensed under the Creative Commons Attribution-NonCommercial-ShareAlike 4.0 
                International License (CC BY-NC-SA 4.0).</p>
            
            <p class="">You are free to share and adapt the data, provided that you:</p>
            
            <ul class="ms-4">
                <li class="mb-1">Attribute the source appropriately. Proper attribution formats are provided for each 
                    dataset to facilitate compliance;</li>
                <li class="mb-1">Do not use the data for commercial purposes; and</li>
                <li class="mb-1">Distribute any derivative works under the same license.</li>
            </ul>

            <h3 class="mt-4 mb-2">Commercial Use Prohibited Without License</h3>
            <p class="">Use of the data or copyrights, in whole or in part, for commercial 
                purposes is strictly prohibited without prior written permission. If you wish to use 
                the data or copyrights for any commercial application—including, but not limited to, 
                incorporation into a commercial product or service, use in a for-profit setting, or 
                resale—you must obtain a separate commercial license.</p>
            
            <p class=" mb-2">To inquire about commercial licensing, please contact:</p>
            <ul class="ms-4">
                <li class="mb-1"><a href="mailto:rossa@uchc.edu" class="text-decoration-none">rossa@uchc.edu</a></li>
                <li class="mb-1"><a href="mailto:techcomm@uconn.edu" class="text-decoration-none">techcomm@uconn.edu</a></li>
                <li class="mb-1"><a href="https://innovation.uconn.edu/" class="text-decoration-none" target="_blank">https://innovation.uconn.edu/</a></li>
            </ul>
            <h3 class="mt-4 mb-2">Disclaimer of Warranties</h3>
            <p class="">The data and copyrights are provided "as is", without any express or implied warranties. 
                The ROSSA makes no representations or warranties of any kind, including, but not limited, to the accuracy, 
                completeness, legality, reliability, or fitness for a particular purpose of the data. Users are solely responsible 
                for their use of the data and are encouraged to independently verify its suitability for their specific purposes.</p>

            <h3 class="mt-4 mb-2">Policy Updates</h3>
            <p class="">The ROSSA operates within a dynamic constantly evolving landscape of open science and data sharing. 
                To ensure remain continued relevancet and effectiveness, these terms of use may be updated periodically. 
                Any modificationschanges will be announced on the repository's website, and users are encouraged to review 
                the terms regularly.</p>

            <h3 class="mt-4 mb-2">Contact Information</h3>
            <p class="">For questions, feedback, or further clarification regarding these terms of use or the ROSSA repository, 
                please <a href="" class="text-decoration-none">contact us</a>.</p>
        </div>
    </div>
</div>

   
@endsection
