<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $diabetesTypes = [
            'Type 1',
            'Type 2',
            'Gestational Diabetes',
            'LADA - Latent Autoimmune Diabetes in Adults',
            'MODY - Maturity Onset Diabetes of the Young',
            'Secondary Diabetes due to Pancreatic Disease',
            'Drug-induced Diabetes',
            'Hormone-induced Diabetes',
            'Syndrome-related Diabetes',
            'Immune-related Diabetes',
            'Age-related Diabetes',
        ];

        for ($i = 1; $i <= 15; $i++) {

            $user = User::create([
                'name' => 'Patient ' . $i,
                'email' => "patient{$i}@gmail.com",
                'password' => Hash::make('password'),
                'role' => 'patient',
                'phone' => '09' . rand(10000000, 99999999),
                 'age' => rand(18, 75),

            ]);

            $user->patient()->create([
                'diabetes_type' => $diabetesTypes[array_rand($diabetesTypes)],
                'hba1c' => rand(55, 95) / 10, // 5.5 → 9.5
            ]);
        }
    }
}
