<?php

namespace App\Http\Controllers;

use App\Models\OfficeTimeSlot;
use Illuminate\Http\Request;

class OfficeTimeSlotController extends Controller
{
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
}