<?php

namespace App\Jobs;

use App\Enums\SubscriptionType;
use App\Mail\StudySubmittedDailyDigestEmail;
use App\Models\Study;
use App\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyStudySubmittedNotifications implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get studies that have been submitted but not yet notified to subscribers
        $studies = Study::query()
            ->whereNotNull('submitted_at')
            ->whereNull('subscribers_notified_at')
            ->whereNull('collection_id')
            ->orderBy('submitted_at', 'desc')
            ->get();

        if ($studies->isEmpty()) {
            return;
        }

        $subscription = Subscription::query()
            ->where('type', SubscriptionType::STUDY_SUBMITTED->value)
            ->first();

        if (! $subscription) {
            return;
        }

        $emails = $subscription->users()->pluck('email');

        if ($emails->isEmpty()) {
            return;
        }

        foreach ($emails as $email) {
            Mail::to($email)->send(new StudySubmittedDailyDigestEmail($studies));
        }

        Study::query()
            ->whereIn('id', $studies->pluck('id'))
            ->update(['subscribers_notified_at' => now()]);

        Log::info('Notified subscribers about '.$studies->count().' new study submissions.');
    }
}
