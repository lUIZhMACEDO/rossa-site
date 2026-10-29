<?php

namespace App\Mail;

use App\Models\Study;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class EmbargoReminderEmail extends Mailable
{
    protected Study $study;

    public string $studyUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(int $studyId)
    {
        $this->study = Study::findOrFail($studyId);
        $this->studyUrl = route('study.study-information', ['study' => $this->study->id]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Embargo Period Reached - {$this->study->identifier}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.embargo-reminder',
            with: [
                'study' => $this->study,
                'studyUrl' => $this->studyUrl,
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
