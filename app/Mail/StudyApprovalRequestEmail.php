<?php

namespace App\Mail;

use App\Models\Study;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudyApprovalRequestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Study $study;

    public string $requesterName;

    public string $recipientEmail;

    public bool $hasAccount;

    public string $studyUrl;

    public function __construct(int $studyId, int $requesterId, string $recipientEmail)
    {
        $this->study = Study::findOrFail($studyId);
        $requester = User::findOrFail($requesterId);

        $this->requesterName = $requester->display_name ?: $requester->email;
        $this->recipientEmail = $recipientEmail;
        $this->hasAccount = User::whereRaw('lower(email) = ?', [strtolower($recipientEmail)])->exists();
        $this->studyUrl = route('study.submission', ['study' => $this->study->id]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'A study is ready for your review and submission',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.study-approval-request',
            with: [
                'study' => $this->study,
                'requesterName' => $this->requesterName,
                'recipientEmail' => $this->recipientEmail,
                'hasAccount' => $this->hasAccount,
                'studyUrl' => $this->studyUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
