<?php

namespace App\Console\Commands;

use App\Models\OfficeHoursSession;
use Illuminate\Console\Command;

class PruneExpiredOfficeHoursSessions extends Command
{
    protected $signature = 'office-hours:prune';

    protected $description = 'Soft-delete office hours sessions whose date has passed';

    public function handle(): void
    {
        $count = OfficeHoursSession::where('session_date', '<', today())->delete();

        $this->info("Pruned {$count} expired office hours session(s).");
    }
}
