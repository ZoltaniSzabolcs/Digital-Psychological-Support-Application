<?php

use App\Http\Controllers\JournalController;
use App\Http\Controllers\MoodController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function (Request $request) {
    return Inertia::render('Dashboard', [
        // Check if a mood entry exists for the current user created 'today'
        'hasLoggedToday' => User::find($request->user()->id)
            ->moods()
            ->whereDate('created_at', now())
            ->exists()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/journal', function () {
    return Inertia::render('Journal');
})->middleware(['auth', 'verified'])->name('journal');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/mood', [MoodController::class, 'store'])->name('mood.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/journal', [JournalController::class, 'index'])->name('journal');
    Route::post('/journal', [JournalController::class, 'store'])->name('journal.store');
});


require __DIR__ . '/auth.php';
