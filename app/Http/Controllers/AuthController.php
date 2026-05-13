<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'                  => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
            'email'                 => ['required', 'email:rfc,dns', 'unique:users,email', 'max:255'],
            'password'              => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'password_confirmation' => ['required'],
            'role_id'               => ['required', 'exists:roles,id'],
        ], [
            'name.regex'            => 'Name may only contain letters, spaces, and hyphens.',
            'email.unique'          => 'An account with this email already exists.',
            'password.confirmed'    => 'Passwords do not match.',
            'role_id.exists'        => 'Selected role is invalid.',
        ]);

        $user = User::create([
            'name'     => trim($request->name),
            'email'    => strtolower(trim($request->email)),
            'password' => $request->password,
            'role_id'  => $request->role_id,
        ]);

        $user->load('role');

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role?->name,
            ],
            'token'      => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:1'],
        ], [
            'email.required'    => 'Email address is required.',
            'email.email'       => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
        ]);

        $user = User::with('role')
            ->where('email', strtolower(trim($request->email)))
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        // Revoke previous tokens to enforce single-session
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role?->name,
            ],
            'token'      => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');

        return response()->json([
            'success' => true,
            'user'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role?->name,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'You have been logged out successfully.',
        ]);
    }

    public function roles(): JsonResponse
    {
        $roles = Role::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'data'    => $roles,
        ]);
    }

    /**
     * Serve the auth page (login + register tabs).
     * Used by web routes only — API routes stay separate.
     */
    public function showPage(Request $request)
    {
        $roles = Role::select('id', 'name')->get();
        $tab   = $request->query('tab', 'login');

        return view('auth', compact('roles', 'tab'));
    }
}

