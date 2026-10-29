<?php

namespace App\Http\Controllers;

class FileDownloadController extends Controller
{
    public function index()
    {
        return view('download-files');
    }
}
