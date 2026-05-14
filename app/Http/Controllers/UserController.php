<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('role')->get();

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'role_id'   => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => strtolower(trim($request->email)),
            'password'  => $request->password,
            'role_id'   => $request->role_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->load('role');

        return response()->json(['success' => true, 'message' => 'User created.', 'data' => $user], 201);
    }

    public function show(string $id): JsonResponse
    {
        $user = User::with('role')->findOrFail($id);

        return response()->json(['success' => true, 'data' => $user]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|string|min:8',
            'role_id'   => 'sometimes|exists:roles,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->name    = $request->input('name', $user->name);
        $user->email   = isset($request->email) ? strtolower(trim($request->email)) : $user->email;
        $user->role_id = $request->input('role_id', $user->role_id);

        if ($request->has('is_active')) {
            $user->is_active = $request->boolean('is_active');
        }

        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->save();
        $user->load('role');

        return response()->json(['success' => true, 'message' => 'User updated.', 'data' => $user]);
    }

    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted.']);
    }

    public function toggleActive(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->is_active = ! $user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success'   => true,
            'message'   => "User account {$status}.",
            'is_active' => $user->is_active,
        ]);
    }
}
