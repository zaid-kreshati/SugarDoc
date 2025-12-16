<?php

namespace App\Repositories;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthRepository
{
    protected const REGISTRATION_CACHE_PREFIX = 'registration_';

    protected const REGISTRATION_CACHE_MINUTES = 100; // Can easily change it in the future

    public function initiateRegistration(array $data): void
    {
        $cacheKey = self::REGISTRATION_CACHE_PREFIX.$data['email'];

        Cache::put($cacheKey, $data, now()->addMinutes(self::REGISTRATION_CACHE_MINUTES));
    }

    public function getUserData($email)
    {
        $cacheKey = self::REGISTRATION_CACHE_PREFIX.$email;

        return Cache::get($cacheKey);
    }

    public function deleteUserData($email): void
    {
        $cacheKey = self::REGISTRATION_CACHE_PREFIX.$email;
        Cache::forget($cacheKey);
    }

    public function cacheResetCode(array $request): void
    {
        $cacheKey = 'reset_code_'.$request['email'];
        Cache::put($cacheKey, $request, now()->addMinutes(self::REGISTRATION_CACHE_MINUTES));
    }

    public function getResetCode($email)
    {
        $cacheKey = 'reset_code_'.$email;

        return Cache::get($cacheKey);
    }

    public function deleteResetCode($email): void
    {
        $cacheKey = 'reset_code_'.$email;
        Cache::forget($cacheKey);
    }

    /**
     * @throws Exception
     */
    public function updatePassword(array $request)
    {
        $user = User::where('email', $request['email'])->first();
        if (! $user) {
            throw new Exception('User not found with email: '.$request['email']);
        }
        $user->password = Hash::make($request['new_password']);
        $user->save();

        return $user;
    }

    /**
     * @throws Exception
     */
    public function login($request): array
    {
        $credentials = $request->only('email', 'password');
        if (! Auth::attempt($credentials)) {
            throw new Exception('Invalid credentials');
        }

        $user = User::where('id', Auth::id())->first();

        if ($request['device_token']) {
            $user->update([
                'device_token' => $request['device_token'],
            ]);
            $user->save();
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        $role = $user->getRoleNames()->first();

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'role' => $role,
            'token' => $token,
        ];
    }

    /**
     * Logout user by deleting all tokens.
     */
    public function logout($request): void
    {
        $request->user()->tokens()->delete();
    }
}
