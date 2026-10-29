<?php

namespace App\Mail;

use App\Models\Study;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudySubmissionAdminNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Study $study;

    public string $adminStudiesUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(int $studyId)
    {
        $this->study = Study::findOrFail($studyId);
        $this->adminStudiesUrl = route('admin.studies', [
            'status' => 'pending',
            'search' => $this->study->identifier,
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Study Review Needed: {$this->study->identifier}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.study-submission-admin-notification',
            with: [
                'study' => $this->study,
                'adminStudiesUrl' => $this->adminStudiesUrl,
            ],
        );
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
