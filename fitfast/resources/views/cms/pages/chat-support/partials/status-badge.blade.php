@php
    $statusColors = [
        'open' => 'warning',
        'in_progress' => 'info',
        'resolved' => 'success',
        'closed' => 'secondary'
    ];
    $statusIcons = [
        'open' => 'exclamation-circle',
        'in_progress' => 'spinner',
        'resolved' => 'check',
        'closed' => 'times'
    ];
@endphp

<span class="badge badge-{{ $statusColors[$ticket->status] ?? 'secondary' }}">
    <i class="fas fa-{{ $statusIcons[$ticket->status] ?? 'circle' }}"></i>
    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
</span>
