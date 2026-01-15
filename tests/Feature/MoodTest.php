<?php

use App\Models\User;
use App\Models\Mood;
use App\Mail\CrisisAlertMail;
use Illuminate\Support\Facades\Mail;

test('users can log their daily mood and notes', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('mood.store'), [
        'score' => 7,
        'emoji' => '😊',
        'notes' => 'Feeling quite productive today.',
        'suicidal_thought_flag' => false,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('moods', [
        'user_id' => $user->id,
        'score' => 7,
        'emoji' => '😊',
        'notes' => 'Feeling quite productive today.',
    ]);
});

test('system triggers critical alert and email when a crisis is detected', function () {
    Mail::fake(); // Intercept emails

    // Setup: Create a psychologist and a patient assigned to them
    $psychologist = User::factory()->create(['role' => 'psychologist', 'email' => 'doc@example.com']);
    $patient = User::factory()->create([
        'role' => 'patient',
        'assigned_psychologist_id' => $psychologist->id
    ]);

    // Action: Post a high-distress entry
    $response = $this->actingAs($patient)->post(route('mood.store'), [
        'score' => 1, // Scores <= 2 trigger crisis
        'emoji' => '😫',
        'suicidal_thought_flag' => true,
    ]);

    // Assert: Database alert record created
    $this->assertDatabaseHas('alerts', [
        'patient_id' => $patient->id,
        'psychologist_id' => $psychologist->id,
        'severity' => 'critical'
    ]);

    // Assert: Email was sent to the correct psychologist
    Mail::assertSent(CrisisAlertMail::class, function ($mail) use ($psychologist) {
        return $mail->hasTo($psychologist->email);
    });

    $response->assertSessionHas('warning');
});

test('insights page renders correctly with mood history stats', function () {
    $user = User::factory()->create();

    // Seed some fake mood data for the graph
    Mood::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('insights'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Insights')
        ->has('stats')
        ->has('moodGraph')
        ->has('recent_entries')
    );
});
