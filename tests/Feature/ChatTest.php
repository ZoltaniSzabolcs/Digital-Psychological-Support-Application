<?php

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('users can view their conversation history', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    // Create a sample message
    Message::create([
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
        'type' => 'text',
        'content' => 'Hello there!',
    ]);

    $response = $this->actingAs($sender)->get(route('chat.show', $receiver->id));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Chat/Show')
        ->has('messages', 1)
        ->where('messages.0.content', 'Hello there!')
        ->where('messages.0.is_me', true)
    );
});

test('user can send a text message', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    $response = $this->actingAs($sender)->post(route('chat.store', $receiver->id), [
        'content' => 'Test message content',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('messages', [
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
        'content' => 'Test message content',
        'type' => 'text',
    ]);
});

test('user can send an audio voice message', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    // Fake a webm audio file from the recorder
    $audio = UploadedFile::fake()->create('voice.webm', 200, 'audio/webm');

    $response = $this->actingAs($sender)->post(route('chat.store', $receiver->id), [
        'audio' => $audio,
    ]);

    $response->assertRedirect();

    $message = Message::where('sender_id', $sender->id)->first();
    expect($message->type)->toBe('audio');

    // Check if Spatie Media Library processed the file
    expect($message->getFirstMediaUrl('audio_messages'))->not->toBeEmpty();
});

test('chat polling returns only the messages for the specific conversation', function () {
    $me = User::factory()->create();
    $partner = User::factory()->create();
    $stranger = User::factory()->create();

    // Message with partner
    Message::create(['sender_id' => $me->id, 'receiver_id' => $partner->id, 'content' => 'Partner msg']);
    // Message with someone else
    Message::create(['sender_id' => $me->id, 'receiver_id' => $stranger->id, 'content' => 'Stranger msg']);

    // Act: Request chat with PARTNER
    $response = $this->actingAs($me)->get(route('chat.show', $partner->id));

    // Assert: Should only see 1 message
    $response->assertInertia(fn ($page) => $page
        ->has('messages', 1)
        ->where('messages.0.content', 'Partner msg')
    );
});
