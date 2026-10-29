<?php

namespace App\Mail;

use App\Models\Study;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudyApprovalEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Study $study;

    public string $studyUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(int $studyId)
    {
        $this->study = Study::findOrFail($studyId);
        $this->studyUrl = route('search.show', ['study' => $this->study]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Study Approved',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.study-approval',
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
