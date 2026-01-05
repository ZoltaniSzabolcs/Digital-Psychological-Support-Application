<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaterialController extends Controller
{
    public function index()
    {
        return Inertia::render('Material', [
            'materials' => Material::with('media', 'author')
                ->latest()
                ->get(),
            'canPost' => auth()->user()->role === 'psychologist',
        ]);
    }

    public function store(Request $request)
    {
        abort_if(auth()->user()->role !== 'psychologist', 403);

        $validated = $request->validate([
            'type' => 'required|in:text,image,video,audio,link',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'media' => 'nullable|file|max:51200', // 50MB
        ]);

        $path = null;

        if ($request->hasFile('media')) {
            $path = $request->file('media')->store('materials', 'public');
        }

        Material::create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'] ?? null,
            'media_path' => $path,
        ]);

        return redirect()->back()->with('success', 'Material published.');
    }
}
