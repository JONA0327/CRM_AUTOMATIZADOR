<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenidaUsuario extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $plainPassword,
        public readonly string $rol,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Bienvenido a ' . config('app.name') . '! — tus credenciales de acceso',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bienvenida',
        );
    }
}
