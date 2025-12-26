<?php

namespace App\Http\Controllers;

use App\Mail\CrisisAlertMail;
use App\Models\Alert;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

class MoodController extends Controller
{
    /**
     * Store a newly created mood entry in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming data from the Vue form
        $validated = $request->validate([
            'score' => 'required|integer|min:1|max:10',
            'emoji' => 'required|string',
            'notes' => 'nullable|string|max:500',
            'suicidal_thought_flag' => 'boolean',
        ]);

        $user = $request->user();

        // 2. Create the Moods
        $entry = $user->moods()->create([
            'score' => $validated['score'],
            'emoji' => $validated['emoji'],
            'notes' => $validated['notes'] ?? null,
            'suicidal_thought_flag' => $validated['suicidal_thought_flag'] ?? false,
            // We can auto-tag based on score if tags aren't sent
            'tags' => $this->generateAutoTags($validated['score']),
        ]);

        // 3. --- CRISIS CHECK LOGIC ---
        // If the patient is in danger, we immediately create an alert for the psychologist
        if ($this->isCrisis($validated)) {
            $this->triggerCrisisAlert($user, $entry);

            // Return with a specific warning message for the frontend
            return Redirect::back()->with('warning', 'Your input indicates high distress. Your psychologist has been notified.');
        }

        // 4. Standard Success Redirect
        return Redirect::back()->with('success', 'Mood logged successfully.');
    }

    /**
     * Determine if this entry counts as a "Crisis".
     */
    private function isCrisis(array $data): bool
    {
        // Crisis if: User flagged suicidal thoughts OR Score is very low (1 or 2)
        return ($data['suicidal_thought_flag'] ?? false) === true || $data['score'] <= 2;
    }

    /**
     * Create an Alert AND Send Email
     */
    private function triggerCrisisAlert($patient, $entry)
    {
        if ($patient->assigned_psychologist_id) {

            // 1. Create the Database Record
            $alert = Alert::create([
                'patient_id' => $patient->id,
                'psychologist_id' => $patient->assigned_psychologist_id,
                'severity' => 'critical',
                'reason' => $entry->suicidal_thought_flag
                    ? 'Patient reported suicidal ideation.'
                    : 'Patient reported critically low mood (Score: ' . $entry->score . ').',
                'is_resolved' => false,
            ]);

            // 2. Fetch the Psychologist User Object to get email
            // We assume the relationship $patient->psychologist exists,
            // but fetching manually ensures we have the object even if the relationship isn't loaded.
            $psychologist = User::find($patient->assigned_psychologist_id);

            // 3. Send the Email
            if ($psychologist && $psychologist->email) {
                try {
                    Mail::to($psychologist->email)->send(new CrisisAlertMail($alert, $patient));
                } catch (\Exception $e) {
                    // Log the error so the app doesn't crash if email fails
                    Log::error("Failed to send crisis email to {$psychologist->email}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Optional helper to add context tags automatically.
     */
    private function generateAutoTags(int $score): array
    {
        if ($score <= 3) return ['low_mood'];
        if ($score >= 8) return ['positive'];
        return ['neutral'];
    }
}
