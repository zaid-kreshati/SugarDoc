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
        // Create user
        $user = User::create([
            'email'    => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'name'     => $request->input('name'),
            'phone'    => $request->input('phone'),
            'age'      => $request->input('age'),
        ]);

        // Create patient info
        Patient::create([
            'user_id'       => $user->id,
            'diabetes_type' => $request->input('diabetes_type'),
            'hba1c'         => $request->input('hba1c'),
        ]);

        return $this->success($user->load('patient'), 'User registered successfully', 201);
    }

    public function login(Request $request)
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        // Save firebase token if provided
        if ($request->filled('firebase_token')) {
            $user->firebase_token = $request->input('firebase_token');
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
