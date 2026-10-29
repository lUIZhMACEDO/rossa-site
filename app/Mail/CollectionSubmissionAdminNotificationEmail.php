<?php

namespace App\Mail;

use App\Models\Collection;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CollectionSubmissionAdminNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $collection;

    public string $adminCollectionStudiesUrl;

    public int $studyCount;

    public function __construct(int $collectionId)
    {
        $this->collection = Collection::withCount('studies')->findOrFail($collectionId);
        $this->studyCount = $this->collection->studies_count;
        $this->adminCollectionStudiesUrl = route('admin.collections.studies', $this->collection);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Collection Review Needed: {$this->collection->identifier}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.collection-submission-admin-notification',
            with: [
                'collection' => $this->collection,
                'studyCount' => $this->studyCount,
                'adminCollectionStudiesUrl' => $this->adminCollectionStudiesUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
