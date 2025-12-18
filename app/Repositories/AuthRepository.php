<?php

namespace App\Repositories;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

class AuthRepository
{
    protected const REGISTRATION_CACHE_PREFIX = 'registration:';
    protected const RESET_CODE_PREFIX = 'reset_code:';

    // TTL in seconds (100 minutes)
    protected const CACHE_TTL = 6000;

    /* =========================
     | Registration verification
     ========================= */

    public function initiateRegistration(array $data): void
    {
        $key = self::REGISTRATION_CACHE_PREFIX . strtolower($data['email']);

        Redis::setex(
            $key,
            self::CACHE_TTL,
            json_encode($data)
        );
    }

    public function getUserData(string $email): ?array
    {
        $key = self::REGISTRATION_CACHE_PREFIX . strtolower($email);

        $data = Redis::get($key);

        return $data ? json_decode($data, true) : null;
    }

    public function deleteUserData(string $email): void
    {
        $key = self::REGISTRATION_CACHE_PREFIX . strtolower($email);
        Redis::del($key);
    }

    /* =========================
     | Password reset
     ========================= */

    public function cacheResetCode(array $request): void
    {
        $key = self::RESET_CODE_PREFIX . strtolower($request['email']);

        Redis::setex(
            $key,
            self::CACHE_TTL,
            json_encode($request)
        );
    }

    public function getResetCode(string $email): ?array
    {
        $key = self::RESET_CODE_PREFIX . strtolower($email);

        $data = Redis::get($key);

        return $data ? json_decode($data, true) : null;
    }

    public function deleteResetCode(string $email): void
    {
        $key = self::RESET_CODE_PREFIX . strtolower($email);
        Redis::del($key);
    }

    /* =========================
     | Password update
     ========================= */

    /**
     * @throws Exception
     */
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

    /**
     * @throws Exception
     */
    public function login($request): array
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            throw new Exception('Invalid credentials');
        }

        $user = User::find(Auth::id());

        if (! $user) {
            throw new Exception('Authenticated user not found');
        }

        if (! empty($request['device_token'])) {
            $user->update([
                'device_token' => $request['device_token'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $role = $user->getRoleNames()->first();

        return [
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'role'  => $role,
            'token' => $token,
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
