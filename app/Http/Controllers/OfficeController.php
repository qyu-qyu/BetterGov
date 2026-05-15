<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OfficeController extends Controller
{
    public function index(): JsonResponse
    {
        $offices = Office::with(['municipality'])->get();

        return response()->json(['success' => true, 'data' => $offices]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'municipality_id' => 'required|exists:municipalities,id',
            'office_type_id'  => 'nullable|exists:office_types,id',
            'office_type'     => 'nullable|string|in:civil_registry,mukhtar,municipality,public_health,general_security',
            'address'         => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'working_hours'   => 'nullable|string|max:500',
        ]);

        $office = Office::create($request->only([
            'name', 'municipality_id', 'office_type_id', 'office_type',
            'address', 'phone', 'email',
            'latitude', 'longitude', 'working_hours',
        ]));

        $office->load(['municipality']);

        return response()->json(['success' => true, 'message' => 'Office created.', 'data' => $office], 201);
    }

    public function show(string $id): JsonResponse
    {
        $office = Office::with(['municipality', 'officeType', 'services', 'timeSlots'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $office]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $office = Office::findOrFail($id);

        $request->validate([
            'name'            => 'sometimes|string|max:255',
            'municipality_id' => 'sometimes|exists:municipalities,id',
            'office_type_id'  => 'nullable|exists:office_types,id',
            'office_type'     => 'nullable|string|in:civil_registry,mukhtar,municipality,public_health,general_security',
            'address'         => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'working_hours'   => 'nullable|string|max:500',
        ]);

        $office->update($request->only([
            'name', 'municipality_id', 'office_type_id', 'office_type',
            'address', 'phone', 'email',
            'latitude', 'longitude', 'working_hours',
        ]));

        $office->load(['municipality']);

        return response()->json(['success' => true, 'message' => 'Office updated.', 'data' => $office]);
    }

    public function destroy(string $id): JsonResponse
    {
        $office = Office::findOrFail($id);
        $office->delete();

        return response()->json(['success' => true, 'message' => 'Office deleted.']);
    }
}
