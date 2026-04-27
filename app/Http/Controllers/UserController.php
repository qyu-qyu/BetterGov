<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
   public function index(): JsonResponse
   {
       $users = User::with('role')->get();

       return response()->json($users);
   }

   public function store(Request $request): JsonResponse
   {
       $request->validate([
           'name' => 'required|string|max:255',
           'email' => 'required|email|unique:users,email',
           'password' => 'required|string|min:6',
           'role_id' => 'required|exists:roles,id',
       ]);

       $user = User::create([
           'name' => $request->name,
           'email' => $request->email,
           'password' => Hash::make($request->password),
           'role_id' => $request->role_id,
       ]);

       $user->load('role');

       return response()->json([
           'message' => 'User created successfully',
           'user' => $user,
       ], 201);
   }

   public function show(string $id): JsonResponse
   {
       $user = User::with('role')->findOrFail($id);

       return response()->json($user);
   }

   public function update(Request $request, string $id): JsonResponse
   {
       $user = User::findOrFail($id);

       $request->validate([
           'name' => 'sometimes|string|max:255',
           'email' => 'sometimes|email|unique:users,email,' . $user->id,
           'password' => 'nullable|string|min:6',
           'role_id' => 'sometimes|exists:roles,id',
       ]);

       $user->name = $request->name ?? $user->name;
       $user->email = $request->email ?? $user->email;
       $user->role_id = $request->role_id ?? $user->role_id;

       if ($request->filled('password')) {
           $user->password = Hash::make($request->password);
       }

       $user->save();
       $user->load('role');

       return response()->json([
           'message' => 'User updated successfully',
           'user' => $user,
       ]);
   }

   public function destroy(string $id): JsonResponse
   {
       $user = User::findOrFail($id);
       $user->delete();

       return response()->json([
           'message' => 'User deleted successfully',
       ]);
   }
}
