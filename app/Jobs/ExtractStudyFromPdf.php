<?php

namespace App\Jobs;

use App\Enums\AiExtractionStatus;
use App\Models\AiExtraction;
use App\Services\Ai\PaperExtractionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Background job: read the stored PDF, ask Claude for a draft, save it.
 * Runs on the queue because a single call can take up to a minute.
 */
class ExtractStudyFromPdf implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(public AiExtraction $extraction) {}

    public function handle(PaperExtractionService $service): void
    {
        $this->extraction->update(['status' => AiExtractionStatus::Processing]);

        try {
            $pdf = Storage::disk($this->extraction->disk)->get($this->extraction->pdf_path);
            if ($pdf === null) {
                throw new \RuntimeException('Stored PDF not found.');
            }

            $result = $service->extract($pdf);

            $this->extraction->update([
                'status' => AiExtractionStatus::Ready,
                'extracted' => $result['extracted'],
                'evidence' => $result['evidence'],
                'usage' => $result['usage'],
                'model' => $result['model'],
                'error' => null,
                'completed_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('AI PDF extraction failed', [
                'ai_extraction_id' => $this->extraction->id,
                'error' => $e->getMessage(),
            ]);

            $this->extraction->update([
                'status' => AiExtractionStatus::Failed,
                'error' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }
}
