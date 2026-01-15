<?php

use App\Models\User;
use App\Models\Journal;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Fake the 'public' disk where Spatie usually stores media by default
    Storage::fake('public');
});

test('journal page is displayed for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('journal'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Journal'));
});

test('user can create a text-only journal entry', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('journal.store'), [
        'content' => 'This is a private reflection.',
        'visibility' => 'private',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('journals', [
        'user_id' => $user->id,
        'content' => 'This is a private reflection.',
        'type' => 'text',
        'visibility' => 'private',
    ]);
});

test('user can create a voice journal entry with an audio file', function () {
    $user = User::factory()->create();
    // Simulate a webm file recorded from the browser
    $audio = UploadedFile::fake()->create('recording.webm', 500, 'audio/webm');

    $response = $this->actingAs($user)->post(route('journal.store'), [
        'content' => 'Voice entry notes',
        'visibility' => 'shared',
        'audio' => $audio,
    ]);

    $response->assertRedirect();

    $journal = Journal::first();
    expect($journal->type)->toBe('audio');

    // Verify Spatie Media Library processed the file
    expect($journal->getFirstMediaUrl('voice_entries'))->not->toBeEmpty();
});
