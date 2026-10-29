<?php

namespace App\Jobs;

use App\Models\AnalysisUpload;
use App\Models\StatisticalAnalysis;
use App\Services\StatisticalAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStatisticalAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // create a new job instance
    public function __construct(
        public AnalysisUpload $upload,
        public string $dataHash
    ) {}

    public function handle(StatisticalAnalysisService $service): void
    {
        // START CHECK: Abort if data already changed (efficiency)
        $this->upload->refresh();

        if ($this->upload->data_hash !== $this->dataHash) {
            Log::info('Analysis job aborted (stale at start)', [
                'upload_id' => $this->upload->id,
                'job_hash' => $this->dataHash,
                'current_hash' => $this->upload->data_hash,
            ]);

            return;
        }

        // run analysis
        $analysis = $service->analyzeUpload($this->upload, $this->dataHash);

        // END CHECK: Discard if data changed during processing (correctness)
        $this->upload->refresh();

        if ($this->upload->data_hash !== $this->dataHash) {
            Log::info('Analysis job discarded (stale at end)', [
                'upload_id' => $this->upload->id,
                'analysis_id' => $analysis->id,
            ]);
            $analysis->delete();

            return;
        }

        // finalize
        $analysis->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Log::info('Statistical analysis completed', [
            'upload_id' => $this->upload->id,
            'analysis_id' => $analysis->id,
            'measurements' => $analysis->total_measurements,
            'groups' => $analysis->total_groups,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Statistical analysis job failed', [
            'upload_id' => $this->upload->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // mark any processing analysis as failed
        StatisticalAnalysis::where('analysis_upload_id', $this->upload->id)
            ->where('status', 'processing')
            ->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
    }
}
