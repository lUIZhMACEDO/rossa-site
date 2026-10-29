<?php

namespace App\Http\Controllers;

use App\Models\Study;
use App\Models\StudyFigure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudyFigureController extends Controller
{
    public function store(Request $request, Study $study)
    {
        $this->assertPhenotypeEditable($study);

        $validated = $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'caption' => ['required', 'string', 'max:500'],
        ]);

        $file = $request->file('image');

        if (! $study->canStoreBytes((int) $file->getSize())) {
            return redirect()
                ->route('study.scan-datasets.index', ['study' => $study, 'image_type' => 'figures'])
                ->with('error', 'This study has reached the storage limit.');
        }

        $path = $file->store("study_figures/{$study->id}");

        try {
            StudyFigure::create([
                'study_id' => $study->id,
                'path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'caption' => $validated['caption'],
            ]);
        } catch (\Throwable $e) {
            Storage::delete($path);
            throw $e;
        }

        if ($study->skips_image_data) {
            $study->skips_image_data = false;
            $study->save();
        }

        return redirect()
            ->route('study.scan-datasets.index', ['study' => $study, 'image_type' => 'figures'])
            ->with('success', 'Figure uploaded.');
    }

    public function update(Request $request, Study $study, StudyFigure $figure)
    {
        $this->assertBelongsToStudy($study, $figure);
        $this->assertPhenotypeEditable($study);

        $validated = $request->validate([
            'caption' => ['required', 'string', 'max:500'],
        ]);

        $figure->update([
            'caption' => $validated['caption'],
        ]);

        return redirect()
            ->route('study.scan-datasets.index', ['study' => $study, 'image_type' => 'figures'])
            ->with('success', 'Caption updated.');
    }

    public function destroy(Study $study, StudyFigure $figure)
    {
        $this->assertBelongsToStudy($study, $figure);
        $this->assertPhenotypeEditable($study);

        $figure->deleteStoredFile();
        $figure->delete();

        return redirect()
            ->route('study.scan-datasets.index', ['study' => $study, 'image_type' => 'figures'])
            ->with('success', 'Figure deleted.');
    }

    public function show(Study $study, StudyFigure $figure): StreamedResponse
    {
        $this->assertBelongsToStudy($study, $figure);

        if (! Storage::exists($figure->path)) {
            abort(404);
        }

        return Storage::response($figure->path, $figure->original_filename, [
            'Content-Type' => $figure->mime_type ?? 'application/octet-stream',
        ]);
    }

    protected function assertBelongsToStudy(Study $study, StudyFigure $figure): void
    {
        abort_unless((int) $figure->study_id === (int) $study->id, 404);
    }

    protected function assertPhenotypeEditable(Study $study): void
    {
        abort_if($study->isPhenotypeAnalysisLocked(auth()->user()), 403);
    }
}
