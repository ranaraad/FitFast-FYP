<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatSupportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $chats = ChatSupport::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('client.chat_support.index', compact('chats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('client.chat_support.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'type' => 'required|in:question,complaint,suggestion,technical'
        ]);

        ChatSupport::create([
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'type' => $validated['type'],
            'status' => 'open'
        ]);

        return redirect()->route('client.chat-support.index')
            ->with('success', 'Your message has been sent to support. We will get back to you soon.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChatSupport $chatSupport)
    {
        // Ensure user can only view their own chats
        if ($chatSupport->user_id !== Auth::id()) {
            abort(403);
        }

        return view('client.chat_support.show', compact('chatSupport'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChatSupport $chatSupport)
    {
        if ($chatSupport->user_id !== Auth::id() || $chatSupport->isResolved()) {
            abort(403);
        }

        return view('client.chat_support.edit', compact('chatSupport'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChatSupport $chatSupport)
    {
        if ($chatSupport->user_id !== Auth::id() || $chatSupport->isResolved()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'type' => 'required|in:question,complaint,suggestion,technical'
        ]);

        $chatSupport->update($validated);

        return redirect()->route('client.chat-support.show', $chatSupport)
            ->with('success', 'Message updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatSupport $chatSupport)
    {
        if ($chatSupport->user_id !== Auth::id()) {
            abort(403);
        }

        $chatSupport->delete();

        return redirect()->route('client.chat-support.index')
            ->with('success', 'Message deleted successfully.');
    }
}
