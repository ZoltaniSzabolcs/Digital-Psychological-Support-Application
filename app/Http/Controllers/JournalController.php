<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JournalController extends Controller
{
    /**
     * Display the journal page with the use's entries.
     */
    public function index()
    {
        return Inertia::render('Journal', [
            'entries' => auth()->user()
                ->journals()
                ->latest()
                ->get()
                ->map(fn($entry) => [
                    'id' => $entry->id,
                    'content' => $entry->content,
                    'visibility' => $entry->visibility,
                    'created_at' => $entry->created_at->toDateTimeString(),
                ]),
        ]);
    }

    /**
     * Store a newly created journal entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'visibility' => 'required|in:private,shared',
        ]);

        auth()->user()->journals()->create([
            'type' => 'text',
            'content' => $validated['content'],
            'visibility' => $validated['visibility'],
        ]);

        return redirect()->back()->with('success', 'Journal entry saved.');
    }

    /**
     * Soft delete a journal entry.
     */
    public function destroy(Journal $journal)
    {
        // Security check: users can only delete their own entries
        abort_if($journal->user_id !== auth()->id(), 403);

        $journal->delete();

        return redirect()->back()->with('success', 'Journal entry deleted.');
    }
}
