<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\JsonResponseTrait;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

use Exception;

class AuthController extends Controller
{
    use JsonResponseTrait;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

     public function initiate_registration(Request $request): JsonResponse
    {
        try {
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

            $this->authService->initiate_registration($request);

            return $this->success('data initiated successfully and verification code sent to your email');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    public function confirm_registration(Request $request): JsonResponse
    {

        $data = $request->validate([
            'email' => 'required|email',
            'code' => 'required|integer|digits:6',
        ]);

        try {
            $data = $this->authService->register($request);

            return $this->success($data, 'Registered successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function resend_code(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);


        try {
            $this->authService->resend_code($data['email']);

            return $this->success([], 'Verification code sent to your email',200);
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
        $credentials = $request ->only('email', 'password');

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
        $request->user()->currentAccessToken()->delete();
        return $this->success( 'Logged out',200);
    }
}
