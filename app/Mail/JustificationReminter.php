<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JustificationReminter extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $assistance;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $assistance)
    {
        $this->user = $user;
        $this->assistance = $assistance;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio de Justificación Pendiente',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.justification',
            with:[
                'nombreAprendiz' => $this->user->name,
                'fechaInasistencia' => $this->assistance->updated_at,
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
