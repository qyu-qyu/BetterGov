<?php

namespace App\Http\Controllers;

use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MunicipalityController extends Controller
{
    public function index(): JsonResponse
    {
        $municipalities = Municipality::withCount('offices')->orderBy('name')->get();

        return response()->json(['success' => true, 'data' => $municipalities]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:municipalities,name',
            'city' => 'nullable|string|max:255',
        ]);

        $municipality = Municipality::create([
            'name' => $request->name,
            'city' => $request->city,
        ]);

        return response()->json(['success' => true, 'message' => 'Municipality created.', 'data' => $municipality], 201);
    }

    public function show(int $id): JsonResponse
    {
        $municipality = Municipality::withCount('offices')->with('offices')->findOrFail($id);

        return response()->json(['success' => true, 'data' => $municipality]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $municipality = Municipality::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255|unique:municipalities,name,' . $id,
            'city' => 'nullable|string|max:255',
        ]);

        $municipality->update($request->only(['name', 'city']));

        return response()->json(['success' => true, 'message' => 'Municipality updated.', 'data' => $municipality]);
    }

    public function destroy(int $id): JsonResponse
    {
        $municipality = Municipality::findOrFail($id);
        $municipality->delete();

        return response()->json(['success' => true, 'message' => 'Municipality deleted.']);
    }
}
