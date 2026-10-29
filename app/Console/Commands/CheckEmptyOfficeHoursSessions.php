<?php

namespace App\Console\Commands;

use App\Mail\OfficeHoursEmptySession;
use App\Models\OfficeHoursSession;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckEmptyOfficeHoursSessions extends Command
{
    protected $signature = 'office-hours:check-empty';

    protected $description = 'Notify admin if tomorrow\'s office hours session has no registrations';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $session = OfficeHoursSession::where('session_date', $tomorrow)
            ->where('status', 'active')
            ->first();

        if (! $session) {
            $this->info('No active session tomorrow. Skipping.');

            return;
        }

        $count = $session->registrations()
            ->whereNull('cancelled_at')
            ->count();

        if ($count === 0) {
            try {
                Mail::to(config('mail.rossa_admin_mass_email'))
                    ->send(new OfficeHoursEmptySession($session));
                $this->info('Empty session notification sent.');
            } catch (\Throwable $e) {
                Log::error('Failed to send empty session notification: '.$e->getMessage());
                $this->error('Failed to send notification: '.$e->getMessage());
            }
        } else {
            $this->info("Session has {$count} registrations. No notification needed.");
        }
    }
}
