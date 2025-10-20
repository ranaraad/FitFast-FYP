<?php

namespace App\Http\Controllers\CMS;

use App\Models\ChatSupport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ChatSupportController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index()
    {
        $tickets = ChatSupport::with(['user', 'admin'])
            ->latest()
            ->paginate(20);

        return view('cms.pages.chat-support.index', compact('tickets'));
    }

    public function show(ChatSupport $chatSupport)
    {
        $chatSupport->load(['user', 'admin']);

        // Get conversation thread (all messages in this ticket)
        $conversation = ChatSupport::where(function($query) use ($chatSupport) {
            $query->where('user_id', $chatSupport->user_id)
                  ->orWhere('id', $chatSupport->id);
        })->with(['user', 'admin'])
          ->latest()
          ->get();

        $admins = User::whereHas('role', function($query) {
            $query->where('name', 'admin');
        })->get();

        return view('cms.pages.chat-support.show', compact('chatSupport', 'conversation', 'admins'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:general,technical,billing,other',
        ]);

        ChatSupport::create([
            'user_id' => $validated['user_id'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'status' => 'open',
        ]);

        return redirect()->route('cms.chat-support.index')
            ->with('success', 'Support ticket created successfully.');
    }

    public function update(Request $request, ChatSupport $chatSupport)
    {
        $validated = $request->validate([
            'admin_id' => 'nullable|exists:users,id',
            'status' => 'required|in:open,in_progress,resolved,closed',
            'message' => 'sometimes|string|max:1000', // For admin replies
        ]);

        // Get authenticated user ID safely
        $adminId = $this->getAuthenticatedUserId();

        // If admin is assigning themselves
        if ($request->has('admin_id') && !$chatSupport->admin_id && $adminId) {
            $validated['admin_id'] = $adminId;
            $validated['status'] = 'in_progress';
        }

        // If resolving ticket
        if ($validated['status'] === 'resolved') {
            $validated['resolved_at'] = now();
        }

        $chatSupport->update($validated);

        // If admin is sending a reply
        if ($request->has('message') && !empty($request->message) && $adminId) {
            ChatSupport::create([
                'user_id' => $chatSupport->user_id,
                'admin_id' => $adminId,
                'message' => $request->message,
                'type' => $chatSupport->type,
                'status' => 'in_progress',
            ]);
        }

        return redirect()->route('cms.chat-support.show', $chatSupport)
            ->with('success', 'Ticket updated successfully.');
    }

    public function destroy(ChatSupport $chatSupport)
    {
        $chatSupport->delete();

        return redirect()->route('cms.chat-support.index')
            ->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Admin takes ownership of a ticket
     */
    public function takeChat(ChatSupport $chatSupport)
    {
        $adminId = $this->getAuthenticatedUserId();

        if (!$adminId) {
            return redirect()->back()
                ->with('error', 'You must be logged in to take ownership of a ticket.');
        }

        $chatSupport->update([
            'admin_id' => $adminId,
            'status' => 'in_progress',
        ]);

        return redirect()->route('cms.chat-support.show', $chatSupport)
            ->with('success', 'You have taken ownership of this ticket.');
    }

    /**
     * Resolve a ticket
     */
    public function resolve(ChatSupport $chatSupport)
    {
        $chatSupport->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return redirect()->route('cms.chat-support.show', $chatSupport)
            ->with('success', 'Ticket marked as resolved.');
    }

    /**
     * Get tickets by status
     */
    public function byStatus($status)
    {
        $validStatuses = ['open', 'in_progress', 'resolved', 'closed'];

        if (!in_array($status, $validStatuses)) {
            return redirect()->route('cms.chat-support.index')
                ->with('error', 'Invalid status provided.');
        }

        $tickets = ChatSupport::with(['user', 'admin'])
            ->where('status', $status)
            ->latest()
            ->paginate(20);

        return view('cms.pages.chat-support.index', compact('tickets', 'status'));
    }

    /**
     * Safely get authenticated user ID with fallback
     */
    private function getAuthenticatedUserId()
    {
        try {
            return Auth::check() ? Auth::id() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get current user's assigned tickets
     */
    public function myTickets()
    {
        $adminId = $this->getAuthenticatedUserId();

        if (!$adminId) {
            return redirect()->route('cms.chat-support.index')
                ->with('error', 'You must be logged in to view your tickets.');
        }

        $tickets = ChatSupport::with(['user', 'admin'])
            ->where('admin_id', $adminId)
            ->latest()
            ->paginate(20);

        return view('cms.pages.chat-support.index', compact('tickets'))
            ->with('title', 'My Assigned Tickets');
    }

    /**
     * Get unassigned tickets
     */
    public function unassigned()
    {
        $tickets = ChatSupport::with(['user'])
            ->whereNull('admin_id')
            ->where('status', 'open')
            ->latest()
            ->paginate(20);

        return view('cms.paages.chat-support.index', compact('tickets'))
            ->with('title', 'Unassigned Tickets');
    }
}
