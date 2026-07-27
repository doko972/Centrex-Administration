<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $oldEmail,
        public string $newEmail
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "L'adresse email de votre compte a été modifiée",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-changed',
        );
    }
}
