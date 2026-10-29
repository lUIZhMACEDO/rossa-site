<?php

namespace App\Http\Controllers;

use App\Models\AnalysisUpload;
use App\Support\StorageFileResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    public function download(Request $request, $upload_id)
    {
        $upload = AnalysisUpload::findOrFail($upload_id);
        Log::info('Download requested for upload ID: '.$upload_id);

        // @TODO authorize here
        // @TODO log download

        return StorageFileResponse::download($upload->path, $upload->original_filename, $upload->mime_type);
    }
}
