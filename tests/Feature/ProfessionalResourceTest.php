<?php

use App\Models\User;
use App\Models\ProfessionalResource;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

/**
 * 1. Test Authorization (Security Gate)
 */
test('non-psychologists cannot access or post to the professional library', function () {
    $patient = User::factory()->create(['role' => 'patient']);

    // Check access to index
    $this->actingAs($patient)
        ->get(route('professional.library'))
        ->assertStatus(403);

    // Check ability to store
    $this->actingAs($patient)
        ->post(route('professional.library.store'), [
            'type' => 'link',
            'title' => 'Secret Document',
            'external_url' => 'https://example.com',
        ])
        ->assertStatus(403);
});

/**
 * 2. Test File Upload Logic
 */
test('psychologist can upload a PDF resource', function () {
    $psychologist = User::factory()->create(['role' => 'psychologist']);
    $pdf = UploadedFile::fake()->create('therapy_guide.pdf', 1024, 'application/pdf');

    $response = $this->actingAs($psychologist)->post(route('professional.library.store'), [
        'type' => 'pdf',
        'title' => 'CBT Techniques Guide',
        'description' => 'A comprehensive guide for therapists.',
        'file' => $pdf,
    ]);

    $response->assertRedirect();

    $resource = ProfessionalResource::first();
    expect($resource->file_path)->not->toBeNull();

    // Check if file exists on the virtual disk
    Storage::disk('public')->assertExists($resource->file_path);
});

/**
 * 3. Test External Link Storage
 */
test('psychologist can add an external link resource', function () {
    $psychologist = User::factory()->create(['role' => 'psychologist']);
    $link = 'https://www.psychologytoday.com/articles';

    $response = $this->actingAs($psychologist)->post(route('professional.library.store'), [
        'type' => 'link',
        'title' => 'Latest Research Article',
        'external_url' => $link,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('professional_resources', [
        'title' => 'Latest Research Article',
        'external_url' => $link,
        'user_id' => $psychologist->id,
    ]);
});
