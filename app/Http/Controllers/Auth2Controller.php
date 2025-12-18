<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\JsonResponseTrait;

class Auth2Controller extends Controller
{
    use JsonResponseTrait;

    public function register(Request $request)
    {
        $request->validate([
             'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'phone' => 'required|string',

                // patient fields
                'age' => 'required|integer',
                'diabetes_type' => 'required',
                'hba1c' => 'nullable|numeric',
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'name' => $request->name,
            'phone' => $request->phone,
            'age' => $request->age,
        ]);

        # add patient fields
        $user->patient()->create([
            'user_id' => $user->id,
            'diabetes_type' => $request->diabetes_type,
            'hba1c' => $request->hba1c,
        ]);


        return $this->successResponse(['message' => 'User registered successfully'], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|max:255',
            'firebase_token' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = Auth::user()->createToken('authToken')->plainTextToken;

        return $this->successResponse(['token' => $token], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(['message' => 'Logged out successfully'], 200);
    }
}