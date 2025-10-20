@extends('cms.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Support Tickets</h1>
    <div>
        <a href="{{ route('cms.chat-support.by-status', 'open') }}" class="btn btn-warning btn-sm">
            <i class="fas fa-exclamation-circle"></i> Open Tickets
        </a>
        <a href="{{ route('cms.chat-support.by-status', 'in_progress') }}" class="btn btn-info btn-sm">
            <i class="fas fa-spinner"></i> In Progress
        </a>
        <a href="{{ route('cms.chat-support.by-status', 'resolved') }}" class="btn btn-success btn-sm">
            <i class="fas fa-check"></i> Resolved
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Open Tickets</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ \App\Models\ChatSupport::open()->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            In Progress</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ \App\Models\ChatSupport::inProgress()->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-spinner fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Resolved Today</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ \App\Models\ChatSupport::resolved()->whereDate('resolved_at', today())->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Support Tickets</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="ticketsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Message Preview</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    <tr class="{{ $ticket->isOpen() ? 'table-warning' : '' }}">
                        <td>#{{ $ticket->id }}</td>
                        <td>
                            <strong>{{ $ticket->user->name }}</strong>
                            <br><small class="text-muted">{{ $ticket->user->email }}</small>
                        </td>
                        <td>
                            <div class="message-preview">
                                {{ Str::limit($ticket->message, 80) }}
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ ucfirst($ticket->type) }}</span>
                        </td>
                        <td>
                            @include('cms.pages.chat-support.partials.status-badge', ['ticket' => $ticket])
                        </td>
                        <td>
                            @if($ticket->admin)
                                <span class="badge badge-success">{{ $ticket->admin->name }}</span>
                            @else
                                <span class="badge badge-secondary">Unassigned</span>
                            @endif
                        </td>
                        <td>{{ $ticket->created_at->diffForHumans() }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('cms.chat-support.show', $ticket) }}" class="btn btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$ticket->admin_id && $ticket->isOpen())
                                <form action="{{ route('cms.chat-support.take', $ticket) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning" title="Take Ticket">
                                        <i class="fas fa-hand-paper"></i>
                                    </button>
                                </form>
                                @endif
                                @if(!$ticket->isResolved())
                                <form action="{{ route('cms.chat-support.resolve', $ticket) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success" title="Resolve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#ticketsTable').DataTable({
            "order": [[0, 'desc']],
            "pageLength": 25
        });
    });
</script>
@endpush
