<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return response()->json(Service::with('documentTypes')->get(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'service_type_id' => 'required|exists:service_types,id',
            'office_id' => 'required|exists:offices,id',
            'fee' => 'required|numeric',
            'estimated_time' => 'required|integer',
            'document_type_ids' => 'nullable|array',
            'document_type_ids.*' => 'exists:document_types,id'
        ]);

        $service = Service::create($validated);

        if ($request->has('document_type_ids')) {
            $service->documentTypes()->sync($request->document_type_ids);
        }

        return response()->json($service->load('documentTypes'), 201);
    }

    public function show(int $id)
    {
        $service = Service::with('documentTypes')->findOrFail($id);

        return response()->json($service, 200);
    }

    public function update(Request $request, int $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'service_type_id' => 'required|exists:service_types,id',
            'office_id' => 'required|exists:offices,id',
            'fee' => 'required|numeric',
            'estimated_time' => 'required|integer',
            'document_type_ids' => 'nullable|array',
            'document_type_ids.*' => 'exists:document_types,id'
        ]);

        $service->update($validated);

        if ($request->has('document_type_ids')) {
            $service->documentTypes()->sync($request->document_type_ids);
        }

        return response()->json($service->load('documentTypes'), 200);
    }

    public function destroy(int $id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Service deleted'], 200);
    }

    public function attachRequiredDocument(Request $request,int $serviceId)
{
    $validated = $request->validate([
        'document_type_id' => 'required|exists:document_types,id',
    ]);

    $service = Service::findOrFail($serviceId);

    $service->documentTypes()->syncWithoutDetaching([
        $validated['document_type_id']
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Required document attached to service successfully',
        'data' => $service->load('documentTypes')
    ], 200);
}

public function getRequiredDocuments(int $serviceId)
{
    $service = Service::with('documentTypes')->findOrFail($serviceId);

    return response()->json([
        'success' => true,
        'message' => 'Service required documents retrieved successfully',
        'data' => [
            'service_id' => $service->id,
            'service_name' => $service->name,
            'required_documents' => $service->documentTypes
        ]
    ], 200);
}

public function removeRequiredDocument(int $serviceId,int $documentTypeId)
{
    $service = Service::findOrFail($serviceId);

    $service->documentTypes()->detach($documentTypeId);

    return response()->json([
        'success' => true,
        'message' => 'Required document removed from service successfully',
        'data' => $service->load('documentTypes')
    ], 200);
}
}