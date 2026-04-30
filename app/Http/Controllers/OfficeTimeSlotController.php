<?php

namespace App\Http\Controllers;

use App\Models\OfficeTimeSlot;
use Illuminate\Http\Request;

class OfficeTimeSlotController extends Controller
{

public function index()
{
    $slots = OfficeTimeSlot::with('office:id,name,email,phone,address')
        ->latest()
        ->get()
        ->map(function ($slot) {
            return [
                'id' => $slot->id,
                'office_name' => $slot->office->name ?? null,
                'office_email' => $slot->office->email ?? null,
                'office_phone' => $slot->office->phone ?? null,
                'day_of_week' => $slot->day_of_week,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'max_capacity' => $slot->max_capacity,
                'is_active' => $slot->is_active,
                'created_at' => $slot->created_at,
            ];
        });

    return response()->json([
        'success' => true,
        'message' => 'Appointment slots retrieved successfully',
        'data' => $slots
    ], 200);
}
    public function store(Request $request)
    {
        $data = $request->validate([
            'office_id' => 'required|exists:offices,id',
            'day_of_week' => 'required|string|max:20',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_capacity' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $slot = OfficeTimeSlot::create([
            'office_id' => $data['office_id'],
            'day_of_week' => $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'max_capacity' => $data['max_capacity'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment slot created successfully',
            'data' => $slot
        ], 201);
    }

    public function update(Request $request, $id)
{
    $slot = OfficeTimeSlot::findOrFail($id);

    $data = $request->validate([
        'day_of_week' => 'required|string|max:20',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'max_capacity' => 'required|integer|min:1',
    ]);

    $slot->update([
        'day_of_week' => $data['day_of_week'],
        'start_time' => $data['start_time'],
        'end_time' => $data['end_time'],
        'max_capacity' => $data['max_capacity'],
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Appointment slot updated successfully',
        'data' => $slot
    ], 200);
}

public function toggleActive($id)
{
    $slot = OfficeTimeSlot::findOrFail($id);

    $slot->update([
        'is_active' => !$slot->is_active
    ]);

    return response()->json([
        'success' => true,
        'message' => $slot->is_active
            ? 'Appointment slot activated successfully'
            : 'Appointment slot deactivated successfully',
        'data' => $slot
    ], 200);
}
}