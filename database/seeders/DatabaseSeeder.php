<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Mood;
use App\Models\User;
use App\Models\Message; // <--- Import Message Model
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

        // --- SEED MESSAGES FOR MARIA & DR. ANDREI ---
        $this->seedConversation($patient, $psychologist);


        // 2. Generate Data for the Main Patient (Maria)
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

            User::factory()->count(5)->create([
                'assigned_psychologist_id' => $psych->id
            ])->each(function ($p) use ($psych) {

                $this->seedDailyMoods($p, 14);

                Appointment::factory()->count(2)->create([
                    'patient_id' => $p->id,
                    'psychologist_id' => $psych->id
                ]);

                // Randomly seed a short conversation for some patients (50% chance)
                if (rand(0, 1)) {
                    $this->seedConversation($p, $psych, true); // true = random/short
                }
            });
        });
    }

    /**
     * Helper to seed sequential daily moods
     */
    private function seedDailyMoods(User $user, int $daysBack)
    {
        for ($i = $daysBack; $i > 0; $i--) {
            $date = Carbon::now()->subDays($i);
            Mood::factory()->create([
                'user_id' => $user->id,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    /**
     * Helper to seed a conversation between two users
     */
    private function seedConversation(User $patient, User $psychologist, bool $random = false)
    {
        // 1. Define a realistic script for the main demo users
        $script = [
            ['sender' => $psychologist, 'text' => 'Hello Maria, welcome to the platform. How are you feeling today?', 'delay' => 2800], // 2 days ago approx
            ['sender' => $patient,      'text' => 'Hi Dr. Popescu. I am feeling a bit anxious lately.', 'delay' => 2750],
            ['sender' => $psychologist, 'text' => 'I understand. Have you tried the breathing exercises we discussed?', 'delay' => 2740],
            ['sender' => $patient,      'text' => 'Yes, but I struggle to focus.', 'delay' => 1400], // 1 day ago
            ['sender' => $psychologist, 'text' => 'That is normal at first. Try to do them for just 2 minutes today.', 'delay' => 1380],
            ['sender' => $patient,      'text' => 'Okay, I will try. Thank you.', 'delay' => 60], // 1 hour ago
        ];

        // 2. Or generate random generic messages for bulk users
        if ($random) {
            $script = [
                ['sender' => $psychologist, 'text' => 'Hi there, just checking in on your progress.', 'delay' => 2000],
                ['sender' => $patient,      'text' => 'Doing okay, thanks for asking.', 'delay' => 1000],
            ];
        }

        // 3. Insert Messages
        foreach ($script as $line) {
            Message::create([
                'sender_id'   => $line['sender']->id,
                'receiver_id' => ($line['sender']->id === $patient->id) ? $psychologist->id : $patient->id,
                'content'     => $line['text'],
                'type'        => 'text',
                'created_at'  => Carbon::now()->subMinutes($line['delay']),
                'updated_at'  => Carbon::now()->subMinutes($line['delay']),
            ]);
        }
    }
}
