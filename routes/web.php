<?php


use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\MoodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PsychologistController;
use App\Http\Middleware\EnsureUserIsPsychologist;
use App\Models\Mood;
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

    // ==========================================
    // 1. SHARED / REDIRECT LOGIC
    // ==========================================
    Route::get('/dashboard', function (Request $request) {
        // Redirect psychologists to their dashboard
        if ($request->user()->role === 'psychologist') {
            return redirect()->route('psychologist.dashboard');
        }
        if ($request->user()->role === 'admin') {
            return redirect()->route('admin.users.index');
        }
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

    // ==========================================
    // 2. PATIENT ROUTES
    // ==========================================
    Route::put('/user/psychologist', function (Request $request) {
        $validated = $request->validate([
            'psychologist_id' => 'required|exists:users,id'
        ]);

        // Update the logged-in user
        User::find($request->user()->id)->update([
            'assigned_psychologist_id' => $validated['psychologist_id']
        ]);

        return redirect()->back()->with('success', 'Specialist assigned successfully.');
    })->name('user.assign_psychologist');

    Route::get('/insights', function (Request $request) {
        $userId = $request->user()->id;

        // 1. Fetch Graph Data (All time or Year to Date)
        $history = Mood::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get(['created_at', 'score', 'emoji', 'notes']);

        // 2. Calculate Statistics
        $totalEntries = $history->count();
        $averageMood = $totalEntries > 0 ? round($history->avg('score'), 1) : 0;

        // Find best and worst days
        $bestDay = $history->sortByDesc('score')->first();
        $worstDay = $history->sortBy('score')->first();

        return Inertia::render('Insights', [
            'moodGraph' => [
                'labels' => $history->map(fn($e) => $e->created_at->format('d M')),
                'data' => $history->pluck('score'),
            ],
            'stats' => [
                'total_logs' => $totalEntries,
                'average_mood' => $averageMood,
                'best_day' => $bestDay ? $bestDay->created_at->format('d M Y') : 'N/A',
                'worst_day' => $worstDay ? $worstDay->created_at->format('d M Y') : 'N/A',
            ],
            'recent_entries' => $history->sortByDesc('created_at')->take(5)->values()
        ]);
    })->name('insights');

    // ==========================================
    // 3. PSYCHOLOGIST ROUTES (Protected Group)
    // ==========================================
    Route::middleware(['auth', 'verified', EnsureUserIsPsychologist::class]) // <--- Use Class, not Function
    ->prefix('psychologist')
        ->name('psychologist.')
        ->group(function () {
            Route::get('/dashboard', [PsychologistController::class, 'index'])->name('dashboard');
            Route::get('/patient/{id}', [PsychologistController::class, 'show'])->name('patient.show');
        });
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

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/journal', [JournalController::class, 'index'])->name('journal');
    Route::post('/journal', [JournalController::class, 'store'])->name('journal.store');
});


require __DIR__ . '/auth.php';
