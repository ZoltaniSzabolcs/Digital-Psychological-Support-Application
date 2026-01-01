<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MessageController extends Controller
{
    // GET /chat/{userId}
    public function show(Request $request, $userId)
    {
        $currentUser = Auth::user();
        $otherUser = User::findOrFail($userId);

        // Security: Ensure they are related (Psychologist <-> Patient)
        $this->authorizeChat($currentUser, $otherUser);

        // Fetch Messages (Ascending for chat flow)
        $messages = Message::where(function($q) use ($currentUser, $otherUser) {
            $q->where('sender_id', $currentUser->id)
                ->where('receiver_id', $otherUser->id);
        })
            ->orWhere(function($q) use ($currentUser, $otherUser) {
                $q->where('sender_id', $otherUser->id)
                    ->where('receiver_id', $currentUser->id);
            })
            ->with('sender:id,name') // Eager load sender name
            ->orderBy('created_at', 'asc') // Oldest first
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'content' => $msg->content,
                    'type' => $msg->type,
                    'sender_id' => $msg->sender_id,
                    'created_at' => $msg->created_at->format('H:i'),
                    'audio_url' => $msg->type === 'audio' ? $msg->getFirstMediaUrl('audio_messages') : null,
                    'is_me' => $msg->sender_id === Auth::id()
                ];
            });

        return Inertia::render('Chat/Show', [
            'messages' => $messages,
            'partner' => $otherUser
        ]);
    }

    // POST /chat/{userId}
    public function store(Request $request, $userId)
    {
        $request->validate([
            'content' => 'nullable|string',
            'audio' => 'nullable|file|mimes:webm,mp3,wav,ogg,m4a|max:10240',
        ]);

        $currentUser = Auth::user();

        // 1. Create the Message Record
        $message = Message::create([
            'sender_id' => $currentUser->id,
            'receiver_id' => $userId,
            'type' => $request->hasFile('audio') ? 'audio' : 'text',
            'content' => $request->input('content') ?? ($request->hasFile('audio') ? 'Voice Message' : ''),
        ]);

        // 2. Attach Audio if present
        if ($request->hasFile('audio')) {
            $message->addMediaFromRequest('audio')->toMediaCollection('audio_messages');
        }

        // Return back (Inertia will handle the partial reload)
        return redirect()->back();
    }

    private function authorizeChat($user1, $user2)
    {
        // Add logic: Only allow if one is the assigned psychologist of the other
        // For now, we allow basic access for demonstration
    }
}
