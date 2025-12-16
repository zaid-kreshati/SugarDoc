<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'sugarDoc@admin.com'],
            [
                'name' => 'Dr. Admin',
                'password' => Hash::make('password123'),
                'role' => 'doctor',
                'phone' => '12345678',
                'age' => '30'
            ]
        );

        if (!$user->doctor) {
            $user->doctor()->create([
                'specialty' => 'Diabetes Specialist',
            ]);
        }
    }
}
