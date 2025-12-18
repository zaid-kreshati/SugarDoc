<?php

namespace App\Repositories;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis as RedisFacade;

class AuthRepository
{
    protected const REGISTRATION_CACHE_PREFIX = 'registration:';
    protected const RESET_CODE_PREFIX = 'reset_code:';

    // 100 minutes
    protected const CACHE_TTL = 6000;

    /* =========================
     | Registration verification
     ========================= */

    public function initiateRegistration(array $data): void
    {
        try {
            $key = self::REGISTRATION_CACHE_PREFIX . strtolower($data['email']);

            RedisFacade::setex($key, self::CACHE_TTL, json_encode($data));
        } catch (\Throwable $e) {
            // optional: log error
        }
    }

    public function getUserData(string $email): ?array
    {
        try {
            $key = self::REGISTRATION_CACHE_PREFIX . strtolower($email);
            $data = RedisFacade::get($key);

            return $data ? json_decode($data, true) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function deleteUserData(string $email): void
    {
        try {
            $key = self::REGISTRATION_CACHE_PREFIX . strtolower($email);
            RedisFacade::del($key);
        } catch (\Throwable $e) {}
    }

    /* =========================
     | Password reset
     ========================= */

    public function cacheResetCode(array $request): void
    {
        try {
            $key = self::RESET_CODE_PREFIX . strtolower($request['email']);
            RedisFacade::setex($key, self::CACHE_TTL, json_encode($request));
        } catch (\Throwable $e) {}
    }

    public function getResetCode(string $email): ?array
    {
        try {
            $key = self::RESET_CODE_PREFIX . strtolower($email);
            $data = RedisFacade::get($key);

            return $data ? json_decode($data, true) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function deleteResetCode(string $email): void
    {
        try {
            $key = self::RESET_CODE_PREFIX . strtolower($email);
            RedisFacade::del($key);
        } catch (\Throwable $e) {}
    }

    /* =========================
     | Password update
     ========================= */

    public function updatePassword(array $request): User
    {
        $user = User::where('email', $request['email'])->first();

        if (! $user) {
            throw new Exception('User not found with email: ' . $request['email']);
        }

        $user->password = Hash::make($request['new_password']);
        $user->save();

        return $user;
    }

    /* =========================
     | Authentication
     ========================= */

    public function login($request): array
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw new Exception('Invalid credentials');
        }

        $user = User::find(Auth::id());

        if (! $user) {
            throw new Exception('Authenticated user not found');
        }

        if (! empty($request['device_token'])) {
            $user->update(['device_token' => $request['device_token']]);
        }

        return [
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'role'  => $user->getRoleNames()->first(),
            'token' => $user->createToken('auth_token')->plainTextToken,
        ];
    }

    /* =========================
     | Logout
     ========================= */

    public function logout($request): void
    {
        $request->user()->tokens()->delete();
    }
}
