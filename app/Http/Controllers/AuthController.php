<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
   public function register(Request $request): JsonResponse
   {
       $request->validate([
           'name' => 'required|string|max:255',
           'email' => 'required|email|unique:users,email',
           'password' => 'required|string|min:6|confirmed',
           'role_id' => 'required|exists:roles,id',
       ]);

       $user = User::create([
           'name' => $request->name,
           'email' => $request->email,
           'password' => Hash::make($request->password),
           'role_id' => $request->role_id,
       ]);

       $user->load('role');

       $token = $user->createToken('auth_token')->plainTextToken;

       return response()->json([
           'message' => 'User registered successfully',
           'user' => $user,
           'token' => $token,
           'token_type' => 'Bearer',
       ], 201);
   }

   public function login(Request $request): JsonResponse
   {
       $request->validate([
           'email' => 'required|email',
           'password' => 'required|string',
       ]);

       $user = User::where('email', $request->email)->with('role')->first();

       if (!$user || !Hash::check($request->password, $user->password)) {
           throw ValidationException::withMessages([
               'email' => ['Invalid email or password.'],
           ]);
       }

       $token = $user->createToken('auth_token')->plainTextToken;

       return response()->json([
           'message' => 'Login successful',
           'user' => $user,
           'token' => $token,
           'token_type' => 'Bearer',
       ]);
   }

   public function me(Request $request): JsonResponse
   {
       $user = $request->user()->load('role');

       return response()->json($user);
   }

   public function logout(Request $request): JsonResponse
   {
       $request->user()->currentAccessToken()->delete();

       return response()->json([
           'message' => 'Logged out successfully',
       ]);
   }

   public function adminOnly(Request $request): JsonResponse
   {
       return response()->json([
           'message' => 'Welcome Admin',
           'user' => $request->user()->load('role'),
       ]);
   }
}
