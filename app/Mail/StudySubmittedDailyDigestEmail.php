<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class StudySubmittedDailyDigestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $adminStudiesUrl;

    public string $digestLabel;

    /**
     * Create a new message instance.
     */
    public function __construct(public Collection $studies)
    {
        $this->adminStudiesUrl = route('admin.studies');
        $this->digestLabel = $this->buildDigestLabel();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $studyCount = $this->studies->count();

        return new Envelope(
            subject: "New Study Submissions - {$studyCount} since {$this->digestLabel}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.study-submitted-daily-digest',
            with: [
                'studyCount' => $this->studies->count(),
                'studies' => $this->studies,
                'adminStudiesUrl' => $this->adminStudiesUrl,
                'digestLabel' => $this->digestLabel,
            ],
        );
    }

    protected function buildDigestLabel(): string
    {
        $firstSubmittedAt = $this->studies
            ->pluck('submitted_at')
            ->filter()
            ->sort()
            ->first();

        if (! $firstSubmittedAt) {
            return 'the last digest';
        }

        return $firstSubmittedAt->diffForHumans();
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
