<?php

namespace App\Jobs;

use App\Mail\EmbargoReminderEmail;
use App\Models\Study;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmbargoReminderEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public const REMINDER_INTERVAL_DAYS = 7;

    public int $tries = 3;

    /** @var int[] */
    public array $backoff = [60, 300];

    public int $uniqueFor = 43200;

    public function __construct(
        public int $studyId,
        public bool $ownerOnly = false,
    ) {}

    public function uniqueId(): string
    {
        return $this->studyId.':'.($this->ownerOnly ? 'owner' : 'all');
    }

    public static function reminderDueOn(): string
    {
        return now()->startOfDay()->subDays(self::REMINDER_INTERVAL_DAYS)->toDateString();
    }

    public function handle(): void
    {
        $study = Study::with('user', 'investigators')->findOrFail($this->studyId);

        // Scheduled runs skip if a reminder was sent in the last week.
        // Admin resend (ownerOnly) is allowed even when sent_at is already set.
        if (
            ! $this->ownerOnly
            && $study->embargo_reminder_sent_at?->toDateString() > self::reminderDueOn()
        ) {
            return;
        }

        $recipients = collect();

        if ($study->user?->email) {
            $recipients->push($study->user->email);
        }

        if (! $this->ownerOnly && $study->investigators->isNotEmpty()) {
            $recipients = $recipients->merge(
                $study->investigators
                    ->pluck('email')
                    ->filter()
            );
        }

        $recipients = $recipients
            ->filter()
            ->unique(fn (string $email) => strtolower(trim($email)))
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        foreach ($recipients as $email) {
            Mail::to($email)->sendNow(new EmbargoReminderEmail($this->studyId));
        }

        Study::withoutLocking(function () use ($study) {
            $study->forceFill([
                'embargo_reminder_sent_at' => now(),
            ])->save();
        });
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send embargo reminder email job.', [
            'study_id' => $this->studyId,
            'owner_only' => $this->ownerOnly,
            'exception' => $exception->getMessage(),
        ]);
    }
}
