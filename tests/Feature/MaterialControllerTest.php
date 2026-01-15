<?php

// command to run this: php artisan test tests/Feature/MaterialControllerTest.php

use App\Models\User;
use App\Models\Material;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('materials page can be rendered', function () {
    $user = User::factory()->create(['role' => 'psychologist']);

    $response = $this->actingAs($user)->get(route('materials.index'));

    $response->assertStatus(200);
});

test('psychologist can create a text material', function () {
    $psychologist = User::factory()->create(['role' => 'psychologist']);

    $response = $this->actingAs($psychologist)->post(route('materials.store'), [
        'type' => 'text',
        'title' => 'Mental Health Tip',
        'content' => 'Remember to breathe deeply.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('materials', [
        'title' => 'Mental Health Tip',
        'type' => 'text',
    ]);
});

test('psychologist can post a youtube link', function () {
    $psychologist = User::factory()->create(['role' => 'psychologist']);
    $url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

    $response = $this->actingAs($psychologist)->post(route('materials.store'), [
        'type' => 'link',
        'content' => $url,
    ]);

    $this->assertDatabaseHas('materials', [
        'type' => 'link',
        'content' => $url,
    ]);
});

test('patient cannot create a material', function () {
    $patient = User::factory()->create(['role' => 'patient']);

    $response = $this->actingAs($patient)->post(route('materials.store'), [
        'type' => 'text',
        'content' => 'Unauthorized content',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseCount('materials', 0);
});

test('material requires a valid type', function () {
    $psychologist = User::factory()->create(['role' => 'psychologist']);

    $response = $this->actingAs($psychologist)->post(route('materials.store'), [
        'type' => 'invalid-type',
        'content' => 'Some content',
    ]);

    $response->assertSessionHasErrors('type');
});
