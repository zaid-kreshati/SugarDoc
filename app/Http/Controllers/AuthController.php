<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class AuthController extends Controller
{
    use JsonResponseTrait;

    public function register(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'phone' => 'required|string',
                'role' => 'in:patient,doctor',

                // patient fields
                'age' => 'required|integer',
                'diabetes_type' => 'required',
                'hba1c' => 'nullable|numeric',
            ]);

            $response= DB::transaction(function () use ($request) {

                $user = User::create([
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'password' => bcrypt($request['password']),
                    'role' => $request['role'] ?? 'patient', 
                    'phone' => $request['phone'] ?? null,
                    'age' => $request['age'],

                ]);

                $user->patient()->create([
                    'diabetes_type' => $request['diabetes_type'],
                    'hba1c' => $request['hba1c'] ?? null,
                ]);

                return [
                    'user' => $user,
                    'token' => $user->createToken('auth_token')->plainTextToken,
                ];
            });
            return $this->success($response,'data initiated successfully and verification code sent to your email');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }






    public function Login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'firebase_token' => 'required',
        ]);
        $credentials = $request->only('email', 'password');

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
        return $this->success($response, 'Verification code sent to your email', 200);
    }




    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success('Logged out', 200);
    }
}
