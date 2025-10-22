<?php

namespace App\Mail;

use App\Models\Publication;
use App\Models\User;
use App\Models\University\University;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewPublicationNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Publication $publication;
    public User $user;
    public University $university;

    /**
     * Create a new message instance.
     */
    public function __construct(Publication $publication, User $user, University $university)
    {
        $this->publication = $publication;
        $this->user = $user;
        $this->university = $university;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📚 Nova Publicação - UCM',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-publication-notification',
            with: [
                'publication' => $this->publication,
                'user' => $this->user,
                'university' => $this->university,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
