<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $context,
        public bool $sessionsRevoked = false
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre mot de passe a été modifié',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-changed',
        );
    }
}
