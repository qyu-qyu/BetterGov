<?php

namespace App\Http\Controllers;

use App\Jobs\SendAppointmentReminder;
use App\Models\Appointment;
use App\Models\OfficeTimeSlot;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $role  = $user->role?->name;
        $query = Appointment::with(['office', 'timeSlot', 'user:id,name,email'])->latest();

        if ($role === 'office') {
            $query->where('office_id', $user->office_id);
        } else {
            $query->where('user_id', $user->id);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'office_id'             => 'required|exists:offices,id',
            'office_time_slot_id'   => 'required|exists:office_time_slots,id',
            'appointment_date_only' => 'required|date|after_or_equal:today',
            'notes'                 => 'nullable|string|max:500',
        ]);

        $slot = OfficeTimeSlot::findOrFail($data['office_time_slot_id']);

        if (!$slot->is_active) {
            return response()->json(['message' => 'This time slot is not available.'], 422);
        }

        $existing = Appointment::query()
            ->where('office_time_slot_id', $slot->id)
            ->where('appointment_date_only', $data['appointment_date_only'])
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($existing >= $slot->max_capacity) {
            return response()->json(['message' => 'This slot is fully booked for the selected date.'], 422);
        }

        $appointmentDate = null;
        if (!empty($data['appointment_date_only']) && !empty($slot->start_time)) {
            try {
                $appointmentDate = Carbon::parse($data['appointment_date_only'] . ' ' . $slot->start_time);
            } catch (\Exception) {
                $appointmentDate = Carbon::parse($data['appointment_date_only']);
            }
        }

        $appointment = Appointment::create([
            'user_id'               => Auth::id(),
            'office_id'             => $data['office_id'],
            'office_time_slot_id'   => $data['office_time_slot_id'],
            'appointment_date_only' => $data['appointment_date_only'],
            'appointment_date'      => $appointmentDate,
            'status'                => 'pending',
            'notes'                 => $data['notes'] ?? null,
        ]);

        // ── Send booking confirmation email ─────────────────────────────────
        SendAppointmentReminder::dispatch($appointment->id, 'confirmation')
            ->onQueue('emails');

        // ── In-app notification ─────────────────────────────────────────────
        $officeName = $appointment->office?->name ?? 'the office';
        $date       = $appointment->appointment_date_only
            ? Carbon::parse($appointment->appointment_date_only)->format('d M Y')
            : 'TBC';
        NotificationService::notify(
            Auth::id(),
            0, // no request_id for appointments — use 0
            "Your appointment at {$officeName} on {$date} has been received and is pending confirmation.",
            'status_change'
        );

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully.',
            'data'    => $appointment->load(['office', 'timeSlot']),
        ], 201);
    }

    public function cancel(string $id)
    {
        $appointment = Appointment::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $appointment->update(['status' => 'cancelled']);

        // ── Send cancellation email ─────────────────────────────────────────
        SendAppointmentReminder::dispatch($appointment->id, 'cancelled')
            ->onQueue('emails');

        // ── In-app notification ─────────────────────────────────────────────
        $officeName = $appointment->office?->name ?? 'the office';
        $date       = $appointment->appointment_date_only
            ? Carbon::parse($appointment->appointment_date_only)->format('d M Y')
            : 'TBC';
        NotificationService::notify(
            Auth::id(),
            0,
            "Your appointment at {$officeName} on {$date} has been cancelled.",
            'status_change'
        );

        return response()->json(['success' => true, 'message' => 'Appointment cancelled.']);
    }

    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => 'required|in:confirmed,cancelled',
        ]);

        $appointment = Appointment::findOrFail($id);
        $user        = Auth::user();

        if ($user->role?->name === 'office' && $appointment->office_id !== $user->office_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($appointment->status !== 'pending') {
            return response()->json(['message' => 'Only pending appointments can be updated.'], 422);
        }

        $appointment->update(['status' => $data['status']]);

        // ── Notify citizen of office decision ──────────────────────────────
        $emailType = $data['status'] === 'confirmed' ? 'confirmation' : 'cancelled';
        SendAppointmentReminder::dispatch($appointment->id, $emailType)
            ->onQueue('emails');

        // ── In-app notification to citizen ─────────────────────────────────
        $officeName = $appointment->office?->name ?? 'the office';
        $date       = $appointment->appointment_date_only
            ? Carbon::parse($appointment->appointment_date_only)->format('d M Y')
            : 'TBC';
        $notifMsg = $data['status'] === 'confirmed'
            ? "Your appointment at {$officeName} on {$date} has been confirmed."
            : "Your appointment at {$officeName} on {$date} has been declined by the office.";
        NotificationService::notify(
            $appointment->user_id,
            0,
            $notifMsg,
            'status_change'
        );

        return response()->json([
            'success' => true,
            'message' => $data['status'] === 'confirmed' ? 'Appointment confirmed.' : 'Appointment declined.',
            'data'    => $appointment,
        ]);
    }
}