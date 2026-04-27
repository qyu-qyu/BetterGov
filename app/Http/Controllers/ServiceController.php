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

    public function show($id)
    {
        $service = Service::with('documentTypes')->findOrFail($id);

        return response()->json($service, 200);
    }

    public function update(Request $request, $id)
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

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Service deleted'], 200);
    }
}