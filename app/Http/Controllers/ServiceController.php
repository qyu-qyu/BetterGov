<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with(['category', 'office', 'documentTypes'])->get();
        return response()->json(['success' => true, 'data' => $services], 200);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $role = $user->role?->name;

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'office_id'           => 'required|exists:offices,id',
            'fee'                 => 'required|numeric|min:0',
            'estimated_time'      => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'document_type_ids'   => 'nullable|array',
            'document_type_ids.*' => 'exists:document_types,id',
        ]);

        // Office users can only create services for their own office
        if ($role === 'office' && (int) $validated['office_id'] !== $user->office_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $service = Service::create($validated);

        if ($request->has('document_type_ids')) {
            $service->documentTypes()->sync($request->document_type_ids ?? []);
        }

        return response()->json(['success' => true, 'data' => $service->load(['category', 'office', 'documentTypes'])], 201);
    }

    public function show(int $id)
    {
        $service = Service::with(['category', 'office', 'documentTypes'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $service], 200);
    }

    public function update(Request $request, int $id)
    {
        $user    = Auth::user();
        $role    = $user->role?->name;
        $service = Service::findOrFail($id);

        if ($role === 'office' && $service->office_id !== $user->office_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'office_id'           => 'required|exists:offices,id',
            'fee'                 => 'required|numeric|min:0',
            'estimated_time'      => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'document_type_ids'   => 'nullable|array',
            'document_type_ids.*' => 'exists:document_types,id',
        ]);

        if ($role === 'office' && (int) $validated['office_id'] !== $user->office_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $service->update($validated);

        if ($request->has('document_type_ids')) {
            $service->documentTypes()->sync($request->document_type_ids ?? []);
        }

        return response()->json(['success' => true, 'data' => $service->load(['category', 'office', 'documentTypes'])], 200);
    }

    public function destroy(int $id)
    {
        $user    = Auth::user();
        $role    = $user->role?->name;
        $service = Service::findOrFail($id);

        if ($role === 'office' && $service->office_id !== $user->office_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $service->delete();
        return response()->json(['success' => true, 'message' => 'Service deleted.'], 200);
    }

    public function attachRequiredDocument(Request $request, int $serviceId)
    {
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
        ]);

        $service = Service::findOrFail($serviceId);
        $service->documentTypes()->syncWithoutDetaching([$validated['document_type_id']]);

        return response()->json(['success' => true, 'data' => $service->load('documentTypes')], 200);
    }

    public function removeRequiredDocument(int $serviceId, int $documentTypeId)
    {
        $service = Service::findOrFail($serviceId);
        $service->documentTypes()->detach($documentTypeId);

        return response()->json(['success' => true, 'data' => $service->load('documentTypes')], 200);
    }

    public function getRequiredDocuments(int $serviceId)
    {
        $service = Service::with('documentTypes')->findOrFail($serviceId);

        return response()->json([
            'success' => true,
            'data'    => [
                'service_id'         => $service->id,
                'service_name'       => $service->name,
                'required_documents' => $service->documentTypes,
            ],
        ]);
    }
}
