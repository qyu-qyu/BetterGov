<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\OfficeTimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['office', 'timeSlot', 'user:id,name,email'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appointments,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'office_id'           => 'required|exists:offices,id',
            'office_time_slot_id' => 'required|exists:office_time_slots,id',
            'appointment_date_only' => 'required|date|after_or_equal:today',
            'notes'               => 'nullable|string|max:500',
        ]);

        $slot = OfficeTimeSlot::findOrFail($data['office_time_slot_id']);

        if (!$slot->is_active) {
            return response()->json(['message' => 'This time slot is not available.'], 422);
        }

        $existing = Appointment::where('office_time_slot_id', $slot->id)
            ->where('appointment_date_only', $data['appointment_date_only'])
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($existing >= $slot->max_capacity) {
            return response()->json(['message' => 'This slot is fully booked for the selected date.'], 422);
        }

        $appointment = Appointment::create([
            'user_id'               => Auth::id(),
            'office_id'             => $data['office_id'],
            'office_time_slot_id'   => $data['office_time_slot_id'],
            'appointment_date_only' => $data['appointment_date_only'],
            'status'                => 'pending',
            'notes'                 => $data['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully.',
            'data'    => $appointment->load(['office', 'timeSlot']),
        ], 201);
    }

    public function cancel($id)
    {
        $appointment = Appointment::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled.',
        ]);
    }
}
