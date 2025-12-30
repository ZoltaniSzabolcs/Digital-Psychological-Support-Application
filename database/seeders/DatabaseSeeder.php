<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Mood;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create specific Login Accounts
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
        // Loop back 30 days to create a consistent history graph
        $this->seedDailyMoods($patient, 30);

        // Create a Crisis Event for Maria (Today)
        Mood::factory()->crisis()->create([
            'user_id' => $patient->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create Appointments & Invoices
        Appointment::factory()->count(3)->create([
            'patient_id' => $patient->id,
            'psychologist_id' => $psychologist->id
        ]);
        Invoice::factory()->count(2)->create(['user_id' => $patient->id]);


        // 3. Generate Bulk Data (5 Psychologists, each with 5 Patients)
        User::factory()->psychologist()->count(5)->create()->each(function ($psych) {

            // For each psychologist, create 5 patients
            User::factory()->count(5)->create([
                'assigned_psychologist_id' => $psych->id
            ])->each(function ($p) use ($psych) {

                // For each bulk patient, seed 14 days of history
                $this->seedDailyMoods($p, 14);

                // Create random appointments
                Appointment::factory()->count(2)->create([
                    'patient_id' => $p->id,
                    'psychologist_id' => $psych->id
                ]);
            });
        });
    }

    /**
     * Helper function to seed sequential daily moods
     */
    private function seedDailyMoods(User $user, int $daysBack)
    {
        for ($i = $daysBack; $i > 0; $i--) {
            // Calculate the specific date
            $date = Carbon::now()->subDays($i);

            Mood::factory()->create([
                'user_id' => $user->id,
                'created_at' => $date,
                'updated_at' => $date,
                // Optional: ensure score varies slightly to make graph look real
                // The Factory logic likely handles random scores, so we trust it there.
            ]);
        }
    }
}
