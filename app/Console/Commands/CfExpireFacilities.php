<?php

namespace App\Console\Commands;

use App\Enums\CoreFacilityStatus;
use App\Models\CoreFacility;
use Illuminate\Console\Command;

class CfExpireFacilities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cf:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire core facilities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting facility expiration...');

        $facilities = CoreFacility::where('expires_at', '<=', now()->startOfDay())
            ->where('status', CoreFacilityStatus::ACCEPTED->value)
            ->cursor();

        $count = 0;
        foreach ($facilities as $facility) {
            $facility->status = CoreFacilityStatus::EXPIRED;
            $facility->save();
            $count++;
        }

        $this->info("Expired {$count} facilities.");

        return Command::SUCCESS;
    }
}
