<?php

namespace App\Http\Controllers;

class GuidesController extends Controller
{
    public function dataIngestion()
    {
        return view('guides.data-ingestion.index');
    }
}
