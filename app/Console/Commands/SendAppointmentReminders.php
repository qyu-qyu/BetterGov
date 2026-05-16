<?php

namespace App\Console\Commands;

use App\Jobs\SendAppointmentReminder;
use App\Models\Appointment;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature   = 'appointments:send-reminders';
    protected $description = 'Send reminder emails for appointments scheduled for tomorrow';

    public function handle(): int
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $appointments = Appointment::with(['user', 'office', 'timeSlot'])
            ->where('appointment_date_only', $tomorrow)
            ->where('status', 'confirmed')
            ->get();

        if ($appointments->isEmpty()) {
            $this->info("No confirmed appointments for tomorrow ({$tomorrow}).");
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($appointments as $appointment) {
            // Skip if user has opted out
            if (isset($appointment->user->email_notifications)
                && !$appointment->user->email_notifications) {
                continue;
            }

            SendAppointmentReminder::dispatch($appointment->id, 'reminder')
                ->onQueue('emails');

            // ── In-app bell notification ────────────────────────────────────
            $officeName = $appointment->office?->name ?? 'the office';
            $date       = Carbon::parse($appointment->appointment_date_only)->format('d M Y');
            $time       = $appointment->timeSlot
                ? Carbon::parse($appointment->timeSlot->start_time)->format('H:i')
                : '';
            $msg = "Reminder: You have an appointment at {$officeName} tomorrow"
                . ($time ? " at {$time}" : '') . " ({$date}).";
            NotificationService::notify(
                $appointment->user_id,
                0,
                $msg,
                'status_change'
            );

            $count++;
        }

        $this->info("Dispatched {$count} reminder(s) for appointments on {$tomorrow}.");
        return self::SUCCESS;
    }
}