<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        return response()->json(ServiceCategory::all(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category = ServiceCategory::create($validated);

        return response()->json($category, 201);
    }

    public function show(string $id)
    {
        $category = ServiceCategory::findOrFail($id);

        return response()->json($category, 200);
    }

    public function update(Request $request, string $id)
    {
        $category = ServiceCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category->update($validated);

        return response()->json($category, 200);
    }

    public function destroy(string $id)
    {
        $category = ServiceCategory::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully'], 200);
    }
}