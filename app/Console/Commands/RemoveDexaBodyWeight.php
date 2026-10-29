<?php

namespace App\Console\Commands;

use App\Enums\AnalysisTypeEnum;
use App\Models\AnalysisType;
use App\Models\Measurement;
use Illuminate\Console\Command;

class RemoveDexaBodyWeight extends Command
{
    protected $signature = 'rossa:remove-dexa-bw {--force : Skip confirmation prompt}';

    protected $description = 'Remove the Body Weight (bw) measurement from the DEXA analysis type';

    public function handle()
    {
        $dexa = AnalysisType::firstWhere('code', AnalysisTypeEnum::DEXA->value);

        if (! $dexa) {
            $this->error('DEXA analysis type not found.');

            return Command::FAILURE;
        }

        $measurement = Measurement::where('name', 'bw')
            ->where('analysis_type_id', $dexa->id)
            ->where('study_id', null)
            ->first();

        if (! $measurement) {
            $this->info('DEXA Body Weight (bw) measurement not found — nothing to remove.');

            return Command::SUCCESS;
        }

        $valueCount = $measurement->measurementValues()->count();

        $this->line("Found measurement: <options=bold>{$measurement->display_name}</> (id: {$measurement->id})");

        if ($valueCount > 0) {
            $this->warn("This measurement has {$valueCount} associated measurement value(s) that will also be deleted.");
        }

        if (! $this->option('force') && ! $this->confirm('Are you sure you want to delete this measurement?')) {
            $this->info('Aborted.');

            return Command::SUCCESS;
        }

        $measurement->measurementValues()->delete();
        $measurement->delete();

        $this->info('DEXA Body Weight measurement deleted successfully.');

        return Command::SUCCESS;
    }
}
