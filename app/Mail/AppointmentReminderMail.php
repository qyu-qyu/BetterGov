<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $officeName;
    public string $officeAddress;
    public string $appointmentDate;
    public string $appointmentTime;
    public string $notes;
    public string $type; // 'reminder' | 'confirmation' | 'cancelled'
    public string $actionUrl;

    public function __construct(
        string $recipientName,
        string $officeName,
        string $officeAddress,
        string $appointmentDate,
        string $appointmentTime,
        string $notes,
        string $type = 'reminder',
        string $actionUrl = ''
    ) {
        $this->recipientName   = $recipientName;
        $this->officeName      = $officeName;
        $this->officeAddress   = $officeAddress;
        $this->appointmentDate = $appointmentDate;
        $this->appointmentTime = $appointmentTime;
        $this->notes           = $notes;
        $this->type            = $type;
        $this->actionUrl       = $actionUrl ?: url('/citizen/appointments');
    }

    public function envelope(): Envelope
    {
        $subjects = [
            'reminder'     => 'Reminder: Your appointment is tomorrow',
            'confirmation' => 'Appointment confirmed — BetterGov',
            'cancelled'    => 'Appointment cancelled — BetterGov',
        ];

        return new Envelope(
            subject: $subjects[$this->type] ?? 'Appointment update — BetterGov',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment',
            with: [
                'recipientName'   => $this->recipientName,
                'officeName'      => $this->officeName,
                'officeAddress'   => $this->officeAddress,
                'appointmentDate' => $this->appointmentDate,
                'appointmentTime' => $this->appointmentTime,
                'notes'           => $this->notes,
                'type'            => $this->type,
                'actionUrl'       => $this->actionUrl,
                // Resolved values for the template
                'barColor'        => $this->type === 'cancelled' ? '#ef4444' : ($this->type === 'confirmation' ? '#059669' : '#1a56db'),
                'pillBg'          => $this->type === 'cancelled' ? '#fee2e2' : ($this->type === 'confirmation' ? '#d1fae5' : '#dbeafe'),
                'typeLabel'       => $this->type === 'cancelled' ? 'Appointment Cancelled' : ($this->type === 'confirmation' ? 'Appointment Confirmed' : 'Appointment Reminder'),
                'typeIcon'        => $this->type === 'cancelled' ? '[x]' : ($this->type === 'confirmation' ? '[ok]' : '[!]'),
                'actionLabel'     => 'View My Appointments',
            ],
        );
    }
}