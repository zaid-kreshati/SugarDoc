<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\JsonResponseTrait;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    use JsonResponseTrait;

    /**
     * Show authenticated user profile
     */
    public function show(Request $request)
    {
        $response = $request->user()->load(['patient', 'doctor']);
        return $this->success($response, 'user retrived successfully', 200);
    }

    /**
     * Update authenticated user profile
     * User may update one or more fields
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            // user fields
            'name'  => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string',
            'password' => 'sometimes|min:6|confirmed',
            'age' => 'sometimes|integer|min:1',

            // patient fields
            'diabetes_type' => 'sometimes|in:type1,type2',
            'hba1c' => 'sometimes|nullable|numeric',

            // doctor fields
            'specialty' => 'sometimes|nullable|string',
        ]);


        /** ------------------
         * Update user table
         * ------------------ */
        $userData = collect($validated)
            ->only(['name', 'phone', 'age'])
            ->toArray();


        if (isset($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        if (!empty($userData)) {
            $user->update($userData);
        }

        /** ------------------
         * Update patient data
         * ------------------ */
        if ($user->role === 'patient' && $user->patient) {
            $patientData = collect($validated)
                ->only(['diabetes_type', 'hba1c'])
                ->toArray();

            if (!empty($patientData)) {
                $user->patient->update($patientData);
            }
        }

        /** ------------------
         * Update doctor data
         * ------------------ */
        if ($user->role === 'doctor' && $user->doctor) {
            $doctorData = collect($validated)
                ->only(['specialty'])
                ->toArray();

            if (!empty($doctorData)) {
                $user->doctor->update($doctorData);
            }
        }

        $response = $user->fresh()->load(['patient', 'doctor']);
        return $this->success($response, 'Profile updated successfully', 200);
    }
}
