<?php

namespace App\Services;

use App\Jobs\SendResetPasswordEmailJob;
use App\Jobs\SendVerificationEmailJob;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\AuthRepository;
use App\Services\MailService;

class AuthService
{

     public function __construct(
        protected MailService $mailService,
        protected AuthRepository $authRepository
    ) {}


     
    public function initiate_registration(Request $request): void
    {

        $verification_expires_at = now()->addMinutes(3);
        $verification_code = rand(100000, 999999);

        $register_Data = [];
        $register_Data['verification_code'] = $verification_code;
        $register_Data['verification_expires_at'] = $verification_expires_at;
        $register_Data['email'] = $request->email;
        $register_Data['name'] = $request->name;
        $register_Data['password'] = $request->password;
        $register_Data['phone'] = $request->phone;
        $register_Data['age'] = $request->age;
        $register_Data['diabetes_type'] = $request->diabetes_type;
        $register_Data['hba1c'] = $request->hba1c;
       

       

        $this->authRepository->initiateRegistration($register_Data);

        SendVerificationEmailJob::dispatch([
            'verification_code' => $register_Data['verification_code'],
            'verification_expires_at' => $register_Data['verification_expires_at'],
            'email' => $register_Data['email'],
        ])->delay(now()->addSeconds(5)); // Delayed by 2 minutes

    }

    /**
     * @throws \Exception
     */
    public function register(Request $request)
    {

        $data = $this->authRepository->getUserData($request['email']);

        if (! $data) {
            throw new Exception('Invalid verification code');
        }

        if ($data['verification_code'] != $request['code']) {
            throw new Exception('Invalid verification code');
        }

        if (now()->isAfter(Carbon::parse($data['verification_expires_at']))) {
            $this->authRepository->deleteUserData($request['email']);
            throw new \Exception('Verification code has expired');
        }

        return $this->Client($data);
    }

    public function Client(array $data)
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => "patient",
            'phone' => $data['phone'] ?? null,
            'age' => $data['age'],

        ]);

        $user->patient()->create([
            'diabetes_type' => $data['diabetes_type'],
            'hba1c' => $data['hba1c'] ?? null,
        ]);

            return [
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ];
        });
    }

    /**
     * @throws Exception
     */
    public function resend_code($email): void
    {
        $data = $this->authRepository->getUserData($email);
        if (! $data) {
            throw new Exception('no data for this email');
        }

        $verification_expires_at = now()->addMinutes(3);
        $data['verification_expires_at'] = $verification_expires_at;

        $data['verification_code'] = rand(100000, 999999);

        $this->authRepository->initiateRegistration($data);

        SendVerificationEmailJob::dispatch([
            'verification_code' => $data['verification_code'],
            'verification_expires_at' => $data['verification_expires_at'],
            'email' => $email,
        ])->delay(now()->addSeconds(5)); // Delayed by 2 minutes

    }

    public function reset_password(Request $request): void
    {
        $reset_code = rand(100000, 999999);
        $reset_expires_at = now()->addMinutes(3)->toDateTimeString();

        $data = [];
        $data['reset_code'] = $reset_code;
        $data['reset_expires_at'] = $reset_expires_at;
        $data['email'] = $request['email'];

        $this->authRepository->cacheResetCode($data);

        SendResetPasswordEmailJob::dispatch([
            'reset_code' => $reset_code,
            'reset_expires_at' => $reset_expires_at,
            'email' => $request->email,
        ])->delay(now()->addSeconds(3));

        $this->mailService->sendResetPasswordEmail([
            'reset_code' => $reset_code,
            'reset_expires_at' => $reset_expires_at,
            'email' => $request->email,
        ]);

    }

    /**
     * @throws Exception
     */
    public function confirm_reset_password(Request $request)
    {
        $data = $this->authRepository->getResetCode($request['email']);

        if (! $data) {
            throw new \Exception('Invalid reset code');
        }
        if ($data['reset_code'] != $request['reset_code']) {
            throw new \Exception('Invalid reset code');
        }
        if (now()->isAfter(Carbon::parse($data['reset_expires_at']))) {
            $this->authRepository->deleteResetCode($request->email);
            throw new \Exception('Reset code has expired');
        }

        return $this->authRepository->updatePassword($request->all());
    }

   

    /**
     * @throws \Exception
     */
    public function login(Request $request): array
    {
        return $this->authRepository->login($request);
    }

    public function logout(Request $request): void
    {
        $this->authRepository->logout($request);
    }

   
}
