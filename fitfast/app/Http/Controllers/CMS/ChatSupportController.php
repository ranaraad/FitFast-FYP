<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\ChatSupport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatSupportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $chats = ChatSupport::with(['user', 'admin'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('cms.chat_support.index', compact('chats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('role_id', 2)->get(); // Assuming role_id 2 is customers
        return view('cms.chat_support.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:question,complaint,suggestion,technical'
        ]);

        $validated['admin_id'] = Auth::id();

        ChatSupport::create($validated);

        return redirect()->route('admin.chat-support.index')
            ->with('success', 'Chat message created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChatSupport $chatSupport)
    {
        $chatSupport->load(['user', 'admin']);
        return view('cms.chat_support.show', compact('chatSupport'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChatSupport $chatSupport)
    {
        $users = User::where('role_id', 2)->get();
        $admins = User::where('role_id', 1)->get(); // Assuming role_id 1 is admins
        return view('cms.chat_support.edit', compact('chatSupport', 'users', 'admins'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChatSupport $chatSupport)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'admin_id' => 'nullable|exists:users,id',
            'message' => 'required|string|max:1000',
            'status' => 'required|in:open,in_progress,resolved,closed',
            'type' => 'required|in:question,complaint,suggestion,technical'
        ]);

        if ($validated['status'] === 'resolved' && $chatSupport->status !== 'resolved') {
            $validated['resolved_at'] = now();
        }

        $chatSupport->update($validated);

        return redirect()->route('admin.chat-support.index')
            ->with('success', 'Chat message updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatSupport $chatSupport)
    {
        $chatSupport->delete();

        return redirect()->route('admin.chat-support.index')
            ->with('success', 'Chat message deleted successfully.');
    }

    /**
     * Update chat status to in progress.
     */
    public function takeChat(ChatSupport $chatSupport)
    {
        $chatSupport->update([
            'admin_id' => Auth::id(),
            'status' => 'in_progress'
        ]);

        return redirect()->back()
            ->with('success', 'Chat assigned to you.');
    }

    /**
     * Resolve a chat.
     */
    public function resolve(ChatSupport $chatSupport)
    {
        $chatSupport->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Chat marked as resolved.');
    }
}
