<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // GET all services
    public function index()
    {
        return response()->json(Service::all(), 200);
    }

    // CREATE service
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'service_type_id' => 'required|exists:service_types,id',
            'office_id' => 'required|exists:offices,id',
            'fee' => 'required|numeric',
            'estimated_time' => 'required|integer'
        ]);

        $service = Service::create($validated);

        return response()->json($service, 201);
    }

    // GET single service
    public function show($id)
    {
        $service = Service::findOrFail($id);

        return response()->json($service, 200);
    }

    // UPDATE service
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $service->update($request->all());

        return response()->json($service, 200);
    }

    // DELETE service
    public function destroy($id)
    {
        Service::destroy($id);

        return response()->json(['message' => 'Service deleted'], 200);
    }
}