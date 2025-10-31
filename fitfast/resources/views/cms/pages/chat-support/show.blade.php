@extends('cms.layouts.app')

@section('page-title', 'Chat Support')
@section('page-subtitle', 'Manage chat support tickets')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        Support Ticket #{{ $chatSupport->id }}
        @include('cms.pages.chat-support.partials.status-badge', ['ticket' => $chatSupport])
    </h1>
    <div>
        @if(!$chatSupport->admin_id && $chatSupport->isOpen())
        <form action="{{ route('cms.chat-support.take', $chatSupport) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm">
                <i class="fas fa-hand-paper"></i> Take This Ticket
            </button>
        </form>
        @endif

        @if(!$chatSupport->isResolved())
        <form action="{{ route('cms.chat-support.resolve', $chatSupport) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fas fa-check"></i> Mark Resolved
            </button>
        </form>
        @endif

        <a href="{{ route('cms.chat-support.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Tickets
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Conversation Thread -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Conversation</h6>
                <span class="badge badge-info">{{ ucfirst($chatSupport->type) }}</span>
            </div>
            <div class="card-body">
                <div class="chat-messages" style="max-height: 500px; overflow-y: auto;">
                    @foreach($conversation as $message)
                    <div class="message mb-4 {{ $message->admin_id ? 'admin-message' : 'user-message' }}">
                        <div class="d-flex {{ $message->admin_id ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="message-bubble {{ $message->admin_id ? 'bg-primary text-white' : 'bg-light' }} p-3 rounded" style="max-width: 70%;">
                                <div class="message-header d-flex justify-content-between align-items-center mb-2">
                                    <strong>
                                        @if($message->admin_id)
                                            {{ $message->admin->name }} (Admin)
                                        @else
                                            {{ $message->user->name }} (User)
                                        @endif
                                    </strong>
                                    <small class="text-muted">{{ $message->created_at->format('M j, g:i A') }}</small>
                                </div>
                                <div class="message-content">
                                    {{ $message->message }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Reply Form -->
                @if(!$chatSupport->isResolved() && $chatSupport->admin_id)
                <div class="mt-4">
                    <form action="{{ route('cms.chat-support.update', $chatSupport) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="replyMessage">Your Reply</label>
                            <textarea class="form-control" id="replyMessage" name="message" rows="3"
                                      placeholder="Type your response here..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Ticket Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Ticket Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>User:</strong><br>
                    {{ $chatSupport->user->name }}<br>
                    <small class="text-muted">{{ $chatSupport->user->email }}</small>
                </div>

                <div class="mb-3">
                    <strong>Status:</strong><br>
                    @include('cms.pages.chat-support.partials.status-badge', ['ticket' => $chatSupport])
                </div>

                <div class="mb-3">
                    <strong>Type:</strong><br>
                    <span class="badge badge-info">{{ ucfirst($chatSupport->type) }}</span>
                </div>

                <div class="mb-3">
                    <strong>Assigned To:</strong><br>
                    @if($chatSupport->admin)
                        <span class="badge badge-success">{{ $chatSupport->admin->name }}</span>
                    @else
                        <span class="badge badge-secondary">Unassigned</span>
                    @endif
                </div>

                <div class="mb-3">
                    <strong>Created:</strong><br>
                    {{ $chatSupport->created_at->format('M j, Y g:i A') }}
                </div>

                @if($chatSupport->resolved_at)
                <div class="mb-3">
                    <strong>Resolved:</strong><br>
                    {{ $chatSupport->resolved_at->format('M j, Y g:i A') }}
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                @if(!$chatSupport->isResolved())
                <form action="{{ route('cms.chat-support.update', $chatSupport) }}" method="POST" class="mb-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="resolved">
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-check"></i> Mark as Resolved
                    </button>
                </form>
                @endif

                @if($chatSupport->isOpen() && !$chatSupport->admin_id)
                <form action="{{ route('cms.chat-support.take', $chatSupport) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-block">
                        <i class="fas fa-hand-paper"></i> Take Ownership
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.message-bubble {
    border-radius: 18px;
}
.admin-message .message-bubble {
    border-bottom-right-radius: 4px;
}
.user-message .message-bubble {
    border-bottom-left-radius: 4px;
}
</style>
@endpush
