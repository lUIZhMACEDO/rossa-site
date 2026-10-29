<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaUpload;
use Illuminate\Http\Request;

class MediaUploadAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = MediaUpload::query()
            ->failedScan()
            ->with(['study.user']);

        if ($request->filled('status')) {
            $query->where('malware_scan_status', $request->get('status'));
        }

        $mediaUploads = $query->orderByDesc('malware_scanned_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->appends($request->query());

        return view('admin.media-uploads.index', compact('mediaUploads'));
    }
}
