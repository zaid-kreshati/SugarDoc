<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\JsonResponseTrait;
use Illuminate\Support\Facades\Hash;

class Auth2Controller extends Controller
{
    use JsonResponseTrait;

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'password'      => 'required|string|min:6',
            'phone'         => 'required|string',
            'age'           => 'required|integer',
            'diabetes_type' => 'required',
            'hba1c'         => 'nullable|numeric',
        ]);

        // Create user
        $user = User::create([
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
            'name'     => $validated['name'],
            'phone'    => $validated['phone'],
            'age'      => $validated['age'],
        ]);

        // Create patient info
        Patient::create([
            'user_id'       => $user->id,
            'diabetes_type' => $validated['diabetes_type'],
            'hba1c'         => $validated['hba1c'] ?? null,
        ]);

        return $this->success($user->load('patient'), 'User registered successfully', 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'          => 'required|email',
            'password'       => 'required',
            'firebase_token' => 'nullable|string',
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        // Save firebase token if provided
        if (!empty($validated['firebase_token'])) {
            $user->firebase_token = $validated['firebase_token'];
            $user->save();
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user'  => $user,
            'role'  => $user->getRoleNames()->first() ?? null,
            'token' => $token,
        ], 'Login successful', 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(['message' => 'Logged out successfully'], 200);
    }
}
