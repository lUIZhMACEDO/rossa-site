<?php

namespace App\Console\Commands;

use App\Models\OfficeHoursSession;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateWeeklyOfficeHoursSessions extends Command
{
    protected $signature = 'office-hours:generate';

    protected $description = 'Generate upcoming Thursday office hours sessions rolling 8 weeks out';

    // Update these to match your actual Teams link and time
    const TEAMS_LINK = 'https://teams.microsoft.com/meet/219889237729656?p=u7gUE3juTQLWlXBr3a';

    const START_TIME = '14:00:00';

    const END_TIME = '15:00:00';

    const WEEKS_AHEAD = 8;

    public function handle()
    {
        $generated = 0;

        for ($i = 0; $i < self::WEEKS_AHEAD; $i++) {
            $thursday = Carbon::now()->next('Thursday')->addWeeks($i);

            // Skip if session already exists for this date (include soft-deleted to avoid re-creating pruned sessions)
            $exists = OfficeHoursSession::withTrashed()->where('session_date', $thursday->toDateString())->exists();

            if (! $exists) {
                OfficeHoursSession::create([
                    'session_date' => $thursday->toDateString(),
                    'start_time' => self::START_TIME,
                    'end_time' => self::END_TIME,
                    'teams_link' => self::TEAMS_LINK,
                    'status' => 'active',
                ]);
                $generated++;
            }
        }

        $this->info("Generated {$generated} new office hours sessions.");
    }
}
