<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OfficeController extends Controller
{
   public function index(): JsonResponse
   {
       $offices = Office::with(['municipality', 'officeType'])->get();

       return response()->json($offices);
   }

   public function store(Request $request): JsonResponse
   {
       $request->validate([
           'name' => 'required|string|max:255',
           'municipality_id' => 'required|exists:municipalities,id',
           'office_type_id' => 'required|exists:office_types,id',
           'address' => 'nullable|string|max:255',
           'phone' => 'nullable|string|max:50',
           'email' => 'nullable|email|max:255',
       ]);

       $office = Office::create($request->only([
           'name',
           'municipality_id',
           'office_type_id',
           'address',
           'phone',
           'email',
       ]));

       $office->load(['municipality', 'officeType']);

       return response()->json([
           'message' => 'Office created successfully',
           'office' => $office,
       ], 201);
   }

   public function show(string $id): JsonResponse
   {
       $office = Office::with(['municipality', 'officeType'])->findOrFail($id);

       return response()->json($office);
   }

   public function update(Request $request, string $id): JsonResponse
   {
       $office = Office::findOrFail($id);

       $request->validate([
           'name' => 'sometimes|string|max:255',
           'municipality_id' => 'sometimes|exists:municipalities,id',
           'office_type_id' => 'sometimes|exists:office_types,id',
           'address' => 'nullable|string|max:255',
           'phone' => 'nullable|string|max:50',
           'email' => 'nullable|email|max:255',
       ]);

       $office->update($request->only([
           'name',
           'municipality_id',
           'office_type_id',
           'address',
           'phone',
           'email',
       ]));

       $office->load(['municipality', 'officeType']);

       return response()->json([
           'message' => 'Office updated successfully',
           'office' => $office,
       ]);
   }

   public function destroy(string $id): JsonResponse
   {
       $office = Office::findOrFail($id);
       $office->delete();

       return response()->json([
           'message' => 'Office deleted successfully',
       ]);
   }
}


