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
        'email'                 => ['required', 'email:rfc,dns', 'unique:users,email', 'max:255'],
        'password'              => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        'password_confirmation' => ['required'],
        'role_id'               => ['nullable', 'exists:roles,id'],
        'id_document'           => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
    ], [
        'email.unique'          => 'An account with this email already exists.',
        'password.confirmed'    => 'Passwords do not match.',
        'role_id.exists'        => 'Selected role is invalid.',
        'id_document.required'  => 'Please upload your ID document.',
        'id_document.mimes'     => 'ID document must be jpg, jpeg, png, or pdf.',
    ]);

    $idPath = $request->file('id_document')->store('ids', 'public');

    /*
    |--------------------------------------------------------------------------
    | Mock ID extraction
    |--------------------------------------------------------------------------
    | This simulates an external OCR/API extraction from the uploaded ID.
    | Later, this part can be replaced with a real OCR API.
    */
    $firstName = 'Mock';
    $lastName = 'Citizen';
    $idNumber = 'LB-' . rand(100000, 999999);
    $dateOfBirth = '2000-01-01';

    $citizenRole = Role::where('name', 'citizen')->first()
        ?? Role::where('name', 'user')->first();

    $roleId = $request->role_id ?? $citizenRole?->id;

    if (! $roleId) {
        throw ValidationException::withMessages([
            'role_id' => ['Citizen role was not found. Please seed roles first.'],
        ]);
    }

    $user = User::create([
        'name'             => $firstName . ' ' . $lastName,
        'first_name'       => $firstName,
        'last_name'        => $lastName,
        'email'            => strtolower(trim($request->email)),
        'password'         => $request->password,
        'role_id'          => $roleId,
        'id_number'        => $idNumber,
        'date_of_birth'    => $dateOfBirth,
        'id_document_path' => $idPath,
    ]);

    $user->load('role');

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Account created successfully using ID verification.',
        'user'    => [
            'id'            => $user->id,
            'name'          => $user->name,
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name,
            'email'         => $user->email,
            'id_number'     => $user->id_number,
            'date_of_birth' => $user->date_of_birth,
            'role'          => $user->role?->name,
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
        $user = $request->user()->load(['role', 'office:id,name']);

        return response()->json([
            'success' => true,
            'user'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role?->name,
                'office_id'  => $user->office_id,
                'office'     => $user->office ? ['id' => $user->office->id, 'name' => $user->office->name] : null,
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

