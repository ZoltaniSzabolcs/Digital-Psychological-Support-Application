<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mood;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PsychologistController extends Controller
{
    public function index(Request $request)
    {
        // Fetch patients assigned to this psychologist
        $patients = User::where('assigned_psychologist_id', $request->user()->id)
            ->with(['moods' => function($query) {
                // Eager load only the most recent mood entry for the "Status" column
                $query->latest()->limit(1);
            }])
            ->get()
            ->map(function ($patient) {
                $lastMood = $patient->moods->first();

                return [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'email' => $patient->email,
                    'joined_at' => $patient->created_at->format('d M Y'),

                    // Last Recorded Mood Data
                    'last_activity' => $lastMood ? $lastMood->created_at->diffForHumans() : 'Never',
                    'current_status' => $lastMood ? $lastMood->score : null,
                    'is_critical' => $lastMood ? ($lastMood->score <= 2 || $lastMood->suicidal_thought_flag) : false,
                ];
            });

        return Inertia::render('Psychologist/Dashboard', [
            'patients' => $patients
        ]);
    }

    public function show(Request $request, $id)
    {
        $psychologistId = $request->user()->id;

        // 1. Fetch Patient & Verify Ownership
        $patient = User::where('id', $id)
            ->where('assigned_psychologist_id', $psychologistId)
            ->firstOrFail();

        // 2. Fetch Raw Mood History (For the Graph)
        $graphData = Mood::where('user_id', $patient->id)
            ->where('created_at', '>=', now()->subDays(60)) // Last 60 days
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('AVG(score) as value')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // 3. Generate Weekly Reports (Aggregation)
        // We group data by "Year-Week" to build the table
        $weeklyStats = Mood::where('user_id', $patient->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('W, Y'); // e.g., "52, 2024"
            })
            ->map(function ($row) {
                return [
                    'week_number' => Carbon::parse($row->first()->created_at)->startOfWeek()->format('d M') . ' - ' . Carbon::parse($row->first()->created_at)->endOfWeek()->format('d M'),
                    'average_mood' => round($row->avg('score'), 1),
                    'total_logs' => $row->count(),
                    'lowest_score' => $row->min('score'),
                    'highest_score' => $row->max('score'),
                    'dominant_emoji' => $row->mode('emoji')[0] ?? '', // Most frequent emoji
                    'alerts_count' => $row->where('score', '<=', 2)->count() // Simple logic for "bad days"
                ];
            });

        return Inertia::render('Psychologist/PatientDetail', [
            'patient' => $patient,
            'moodGraph' => [
                'labels' => $graphData->pluck('date'),
                'data' => $graphData->pluck('value'),
            ],
            'weeklyReports' => $weeklyStats
        ]);
    }
}
