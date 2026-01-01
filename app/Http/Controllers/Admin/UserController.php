<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // 1. Search Logic
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%") // 'ilike' for case-insensitive (Postgres)
                ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // 2. Pagination (10 per page)
        $users = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString(); // Keep search params in pagination links

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            // Pass list of psychologists in case we want to assign one to a patient
            'psychologists' => User::where('role', 'psychologist')
                ->where('id', '!=', $user->id) // Prevent assigning self if user is a psych
                ->select('id', 'name')
                ->get()
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,psychologist,patient',
            'assigned_psychologist_id' => [
                'nullable',
                'exists:users,id',
                // Only require/allow this if the user is a patient
                Rule::requiredIf($request->role === 'patient')
            ],
        ]);

        // Safety: Prevent Admin from demoting themselves accidentally
        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            return back()->with('error', 'You cannot change your own role.');
        }

        // Clean up psychologist ID if role is NOT patient
        if ($validated['role'] !== 'patient') {
            $validated['assigned_psychologist_id'] = null;
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }
}
