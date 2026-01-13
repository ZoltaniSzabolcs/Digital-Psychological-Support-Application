<?php

namespace App\Http\Controllers;

use App\Models\ProfessionalResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfessionalResourceController extends Controller
{
    public function index()
    {
        abort_if(auth()->user()->role !== 'psychologist', 403);

        return Inertia::render('ProfessionalLibrary', [
            'resources' => ProfessionalResource::with('author')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_if(auth()->user()->role !== 'psychologist', 403);

        $validated = $request->validate([
            'type' => 'required|in:pdf,document,video,link',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:51200', // 50MB
            'external_url' => 'nullable|url',
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('professional-library', 'public');
        }

        ProfessionalResource::create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $path,
            'external_url' => $validated['external_url'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Resource added.');
    }
}
