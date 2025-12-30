<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChatSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SupportChatController extends Controller
{
    private const VALID_TYPES = ['question', 'complaint', 'suggestion', 'technical'];

    public function index()
    {
        $tickets = ChatSupport::where('user_id', Auth::id())
            ->latest()
            ->get(['id', 'message', 'status', 'type', 'created_at', 'resolved_at']);

        return response()->json([
            'data' => $tickets,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'type' => ['required', Rule::in(self::VALID_TYPES)],
        ]);

        $ticket = ChatSupport::create([
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'type' => $validated['type'],
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'Your message has been sent to support.',
            'ticket' => $ticket,
        ], 201);
    }

    public function show(ChatSupport $chatSupport)
    {
        if ($chatSupport->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $conversation = ChatSupport::where('user_id', $chatSupport->user_id)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'ticket' => $chatSupport,
            'conversation' => $conversation,
        ]);
    }

    public function reply(Request $request, ChatSupport $chatSupport)
    {
        if ($chatSupport->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($chatSupport->isResolved()) {
            return response()->json(['message' => 'This ticket is already resolved.'], 422);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $chatSupport->update([
            'status' => 'in_progress',
        ]);

        $reply = ChatSupport::create([
            'user_id' => $chatSupport->user_id,
            'message' => $validated['message'],
            'type' => $chatSupport->type,
            'status' => 'in_progress',
        ]);

        return response()->json([
            'message' => 'Reply sent successfully.',
            'reply' => $reply,
        ], 201);
    }
}