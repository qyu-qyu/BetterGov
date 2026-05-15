<?php

namespace App\Http\Controllers;

use App\Models\OfficeTimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeTimeSlotController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $role  = $user->role?->name;

        $query = OfficeTimeSlot::with('office:id,name,email,phone,address')->latest();

        if ($role === 'office') {
            $query->where('office_id', $user->office_id);
        }

        $slots = $query->get()->map(fn($slot) => [
            'id'           => $slot->id,
            'office_id'    => $slot->office_id,
            'office_name'  => $slot->office->name  ?? null,
            'office_email' => $slot->office->email ?? null,
            'office_phone' => $slot->office->phone ?? null,
            'day_of_week'  => $slot->day_of_week,
            'start_time'   => $slot->start_time,
            'end_time'     => $slot->end_time,
            'max_capacity' => $slot->max_capacity,
            'is_active'    => $slot->is_active,
            'created_at'   => $slot->created_at,
        ]);

        return response()->json(['success' => true, 'data' => $slots], 200);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $role = $user->role?->name;

        $data = $request->validate([
            'office_id'    => 'required|exists:offices,id',
            'day_of_week'  => 'required|string|max:20',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'max_capacity' => 'required|integer|min:1',
            'is_active'    => 'nullable|boolean',
        ]);

        if ($role === 'office' && (int) $data['office_id'] !== $user->office_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $slot = OfficeTimeSlot::create([
            'office_id'    => $data['office_id'],
            'day_of_week'  => $data['day_of_week'],
            'start_time'   => $data['start_time'],
            'end_time'     => $data['end_time'],
            'max_capacity' => $data['max_capacity'],
            'is_active'    => $data['is_active'] ?? true,
        ]);

        return response()->json(['success' => true, 'message' => 'Slot created.', 'data' => $slot], 201);
    }

    public function update(Request $request, int $id)
    {
        $slot = OfficeTimeSlot::findOrFail($id);
        $user = Auth::user();

        if ($user->role?->name === 'office' && $slot->office_id !== $user->office_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'day_of_week'  => 'required|string|max:20',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'max_capacity' => 'required|integer|min:1',
        ]);

        $slot->update($data);

        return response()->json(['success' => true, 'data' => $slot], 200);
    }

    public function toggleActive(int $id)
    {
        $slot = OfficeTimeSlot::findOrFail($id);
        $user = Auth::user();

        if ($user->role?->name === 'office' && $slot->office_id !== $user->office_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $slot->update(['is_active' => !$slot->is_active]);

        return response()->json([
            'success' => true,
            'message' => $slot->is_active ? 'Slot activated.' : 'Slot deactivated.',
            'data'    => $slot,
        ], 200);
    }

    public function byOffice(int $officeId)
    {
        $slots = OfficeTimeSlot::where('office_id', $officeId)
            ->where('is_active', true)
            ->get();

        return response()->json(['success' => true, 'data' => $slots]);
    }
}
