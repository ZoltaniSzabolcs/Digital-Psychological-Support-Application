<?php

use App\Models\User;

test('dashboard displays correct daily check-in status', function () {
    $user = User::factory()->create(['role' => 'patient']);

    // Case 1: User hasn't logged yet
    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertInertia(fn($page) => $page
        ->component('Dashboard')
        ->where('hasLoggedToday', false)
    );

    // Case 2: User has logged (simulate a mood entry for today)
    $user->moods()->create([
        'score' => 8,
        'emoji' => '🙂',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertInertia(fn($page) => $page
        ->where('hasLoggedToday', true)
    );
});

test('patient can assign or change their specialist', function () {
    $patient = User::factory()->create(['role' => 'patient']);
    $psychologist = User::factory()->create(['role' => 'psychologist']);

    $response = $this->actingAs($patient)->put(route('user.assign_psychologist'), [
        'psychologist_id' => $psychologist->id,
    ]);

    $response->assertRedirect();

    // Verify the database update
    expect($patient->fresh()->assigned_psychologist_id)->toBe($psychologist->id);
});

test('mood logging requires a valid score range', function () {
    $user = User::factory()->create(['role' => 'patient']);

    // Attempting to post a score of 15 (max is 10)
    $response = $this->actingAs($user)->post(route('mood.store'), [
        'score' => 15,
        'emoji' => '🤩',
    ]);

    $response->assertSessionHasErrors('score');
});

test('specialist assignment validation prevents non-existent IDs', function () {
    $patient = User::factory()->create(['role' => 'patient']);

    // Attempting to assign a non-existent specialist (ID 9999)
    $response = $this->actingAs($patient)->put(route('user.assign_psychologist'), [
        'psychologist_id' => 9999,
    ]);

    $response->assertSessionHasErrors('psychologist_id');
});
