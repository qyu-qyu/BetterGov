<?php

namespace App\Jobs;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int    $appointmentId,
        public readonly string $type = 'reminder' // 'reminder' | 'confirmation' | 'cancelled'
    ) {}

    public function handle(): void
    {
        $appointment = Appointment::with(['user', 'office', 'timeSlot'])
            ->find($this->appointmentId);

        if (!$appointment || !$appointment->user) {
            Log::warning('SendAppointmentReminder: appointment or user not found', [
                'appointment_id' => $this->appointmentId,
            ]);
            return;
        }

        $user = $appointment->user;

        // Respect email notification preference
        if (isset($user->email_notifications) && !$user->email_notifications) {
            return;
        }

        $office    = $appointment->office;
        $timeSlot  = $appointment->timeSlot;

        $date = $appointment->appointment_date_only
            ? \Carbon\Carbon::parse($appointment->appointment_date_only)->format('l, d F Y')
            : ($appointment->appointment_date
                ? \Carbon\Carbon::parse($appointment->appointment_date)->format('l, d F Y')
                : 'TBC');

        $time = $timeSlot
            ? \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i')
              . ' – '
              . \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i')
            : 'TBC';

        Mail::to($user->email)->send(new AppointmentReminderMail(
            recipientName:   $user->name ?? 'Citizen',
            officeName:      $office?->name      ?? 'Government Office',
            officeAddress:   $office?->address   ?? '',
            appointmentDate: $date,
            appointmentTime: $time,
            notes:           $appointment->notes ?? '',
            type:            $this->type,
            actionUrl:       url('/citizen/appointments'),
        ));
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendAppointmentReminder failed', [
            'appointment_id' => $this->appointmentId,
            'type'           => $this->type,
            'error'          => $e->getMessage(),
        ]);
    }
}