<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();

        // 1. Determine who to list
        $query = User::query()->select('id', 'name', 'email', 'role');

        if ($currentUser->role === 'patient') {
            // Patients see Psychologists
            $query->where('role', 'psychologist');
        } elseif ($currentUser->role === 'psychologist') {
            // Psychologists see Patients
            // Optional: ->where('assigned_psychologist_id', $currentUser->id) if you want strict scoping
            $query->where('role', 'patient');
        } else {
            // Admins see everyone
            $query->where('id', '!=', $currentUser->id);
        }

        // 2. Cursor Pagination (More efficient for infinite scroll)
        $users = $query->orderBy('name')->cursorPaginate(15);

        // 3. Handle JSON Request (for "Load More" scrolling)
        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json($users);
        }

        // 4. Handle Page Load
        return Inertia::render('Chat/Directory', [
            'initialUsers' => $users
        ]);
    }
}
