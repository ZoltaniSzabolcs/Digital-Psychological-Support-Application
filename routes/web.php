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

Route::middleware(['auth', 'verified'])->group(function () {

    // 1. Update GET Dashboard
    Route::get('/dashboard', function (Request $request) {
        return Inertia::render('Dashboard', [
            'hasLoggedToday' => User::find($request->user()->id)
                ->moods()
                ->whereDate('created_at', now())
                ->exists(),

            // Pass the list of psychologists
            'availablePsychologists' => User::where('role', 'psychologist')
                ->select('id', 'name', 'email')
                ->get(),

            // Pass the current selection
            'currentPsychologistId' => $request->user()->assigned_psychologist_id
        ]);
    })->name('dashboard');

    // 2. Add PUT Route to save the psychologist
    Route::put('/user/psychologist', function (Request $request) {
        $validated = $request->validate([
            'psychologist_id' => 'required|exists:users,id'
        ]);

        \Illuminate\Support\Facades\Log::info(User::find($request->user()->id)->assigned_psychologist_id);

        // Update the logged-in user
        User::find($request->user()->id)->update([
            'assigned_psychologist_id' => $validated['psychologist_id']
        ]);

        \Illuminate\Support\Facades\Log::info(User::find($request->user()->id)->assigned_psychologist_id);

        return redirect()->back()->with('success', 'Specialist assigned successfully.');
    })->name('user.assign_psychologist');

});

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
