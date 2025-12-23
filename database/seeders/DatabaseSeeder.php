<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Mood;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create specific Login Accounts (for you to test)
        $admin = User::factory()->admin()->create([
            'email' => 'admin@clinic.ro',
            'name'  => 'System Administrator'
        ]);

        $psychologist = User::factory()->psychologist()->create([
            'email' => 'doctor@clinic.ro',
            'name'  => 'Dr. Andrei Popescu'
        ]);

        $patient = User::factory()->create([
            'email' => 'patient@clinic.ro',
            'name'  => 'Maria Ionescu',
            'assigned_psychologist_id' => $psychologist->id
        ]);

        // 2. Generate Data for the Main Patient (Maria)
        // Create 30 days of mood history
        Mood::factory()->count(25)->create(['user_id' => $patient->id]);

        // Create a Crisis Event for Maria
        Mood::factory()->crisis()->create(['user_id' => $patient->id]);

        // Create Appointments for Maria
        Appointment::factory()->count(3)->create([
            'patient_id' => $patient->id,
            'psychologist_id' => $psychologist->id
        ]);

        // Create Invoices for Maria
        Invoice::factory()->count(2)->create(['user_id' => $patient->id]);


        // 3. Generate Bulk Data (5 Psychologists, each with 5 Patients)
        User::factory()->psychologist()->count(5)->create()->each(function ($psych) {

            // For each psychologist, create 5 patients
            User::factory()->count(5)->create([
                'assigned_psychologist_id' => $psych->id
            ])->each(function ($p) use ($psych) {

                // For each random patient, create mood entries
                Mood::factory()->count(10)->create(['user_id' => $p->id]);

                // Create random appointments
                Appointment::factory()->count(2)->create([
                    'patient_id' => $p->id,
                    'psychologist_id' => $psych->id
                ]);
            });
        });
    }
}
