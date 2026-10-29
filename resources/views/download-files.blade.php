@extends('layout')

@section('title', 'Download Template Files')
@section('description', 'Download spreadsheet templates for preparing data submissions to ROSSA.')

@section('content')
<style>
    /* Adjust the content styling to match the layout */
    .content-container {
        padding: 20px;
    }

    /* Add vertical spacing between download options */
    .content-container ul {
        list-style-type: none;
        padding-left: 0;
    }

    .content-container ul li {
        margin: 20px 0;  /* Increased vertical spacing */
        text-align: left; /* Align text to the left */
    }

    .content-container ul li a {
        color: #3498db;
        text-decoration: none;
        font-size: 1.1rem;
        padding: 8px 16px;
        border: 2px solid #3498db;
        border-radius: 4px;
        transition: background-color 0.3s, color 0.3s;
    }

    .content-container ul li a:hover {
        background-color: #3498db;
        color: #fff;
    }

</style>
<div class="container">
    <div class="content-container">
        <h1>Spreadsheet Data Template Files</h1>
        <p>The template files provided below are optional spreadsheet files that you can use to upload your data.</p>

        <ul>
            <li>
                <a href="{{ asset('templates/Dexa.xlsx') }}" download>DEXA Data Template File</a>
            </li>
            <li>
                <a href="{{ asset('templates/Microcomputed_Tomography_Template_V2.xlsx') }}" download>Microcomputed Tomography Data Template File</a>
            </li>
            <li>
                <a href="{{ asset('templates/Bone_Histo_Template_V2.xlsx') }}" download>Bone Histomorphometry Data Template File</a>
            </li>
            <li>
                <a href="{{ asset('templates/Mechanical Testing Upload Template_V2.xlsx') }}" download>Mechanical Strength Data Template File</a>
            </li>
        </ul>
    </div>
</div>
@endsection
