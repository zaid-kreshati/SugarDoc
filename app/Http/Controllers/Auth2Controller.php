<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\JsonResponseTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;



class Auth2Controller extends Controller
{
    use JsonResponseTrait;

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'phone'    => 'required|string',

            // patient fields
            'age'          => 'required|integer',
            'diabetes_type'=> 'required',
            'hba1c'        => 'nullable|numeric',
        ]);

        // Wrap everything in a transaction so we can roll back if anything fails
        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'email'    => $validated['email'],
                'password' => bcrypt($validated['password']),
                'name'     => $validated['name'],
                'phone'    => $validated['phone'],
                'age'      => $validated['age'],
            ]);

            // Ensure the patient relationship exists before creating
            if (!$user->patient) {
                $user->patient()->create([
                    'diabetes_type' => $validated['diabetes_type'],
                    'hba1c'         => $validated['hba1c'] ?? null,
                ]);
            }

            return $user;
        });

        Log::info('User registered successfully', ['user' => $user]);

        return $this->success(  $user->load('patient'),'User registered successfully', 201);
    }

    public function Login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'firebase_token' => 'required',
        ]);
        $credentials = $request ->only('email', 'password');
        Log::info('Login credentials', $credentials);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $user['firebase_token'] = $data['firebase_token'];
        $user->save();
        
        $token = $user->createToken('api-token')->plainTextToken;



        $response['user'] = $user;
        $response['role'] = $user['role'];
        $response['token'] = $token;
        return $this->success($response, 'Verification code sent to your email',200);
        
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(['message' => 'Logged out successfully'], 200);
    }
}