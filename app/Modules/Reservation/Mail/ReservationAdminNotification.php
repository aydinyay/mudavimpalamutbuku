<?php

namespace App\Modules\Reservation\Mail;

use App\Modules\Reservation\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Yeni Rezervasyon — ' . $this->reservation->guest_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation.admin-notification',
        );
    }
}
