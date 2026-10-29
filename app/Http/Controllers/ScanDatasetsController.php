<?php

namespace App\Http\Controllers;

use App\Models\Study;
use Illuminate\Http\Request;

class ScanDatasetsController extends Controller
{
    /**
     * Image types that can be uploaded on this page.
     * Add a key here (and a matching branch in the view) when a new
     * image type is supported.
     */
    protected const IMAGE_TYPES = ['micro_ct', 'figures'];

    public function index(Request $request, Study $study)
    {
        $sampleGroups = $study->sampleGroups()
            ->orderBy('label')
            ->get(['id', 'label']);

        $mediaUploads = $study->mediaUploads()
            ->with('sampleGroup')
            ->orderByDesc('id')
            ->get();

        $mediaUploads->each->ensureMetadata();

        $studyFigures = $study->studyFigures()
            ->orderByDesc('id')
            ->get();

        // null shows the image type picker; a valid value shows that type's form
        $imageType = $request->query('image_type');

        if (! in_array($imageType, self::IMAGE_TYPES, true)) {
            $imageType = null;
        }

        return view('study.scan-datasets.index', [
            'study' => $study,
            'sampleGroups' => $sampleGroups,
            'mediaUploads' => $mediaUploads,
            'studyFigures' => $studyFigures,
            'chunkSize' => config('media_upload.chunk_size'),
            'maxFileSize' => config('media_upload.max_file_size'),
            'allowedExtensions' => config('media_upload.allowed_extensions', ['isq', 'dcm', 'aim', 'zip']),
            'imageType' => $imageType,
        ]);
    }

    public function continue(Study $study)
    {
        if ($study->progressImageData() === 1) {
            return redirect()
                ->route('study.scan-datasets.index', $study)
                ->with('error', 'Finish or delete incomplete image uploads before continuing. Failed or abandoned uploads must be deleted.');
        }

        if ($study->mediaUploads()->count() === 0 && $study->studyFigures()->count() === 0) {
            $study->skips_image_data = true;
            $study->save();
        }

        return redirect()->route('study.summary', $study);
    }
}
