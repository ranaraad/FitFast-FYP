@extends('cms.layouts.app')

@section('page-title', 'Payment Method View')
@section('page-subtitle', 'Manage payment methods added by users')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">User Payment Methods</h1>
    <small class="text-muted">View-only - Users manage their own payment methods</small>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        @if($paymentMethods->isEmpty())
        <div class="text-center py-4">
            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No payment methods found</h5>
            <p class="text-muted">No users have added payment methods yet.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Masked Details</th>
                        <th>Default</th>
                        <th>Status</th>
                        <th>Associated Orders</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentMethods as $method)
                    <tr>
                        <td>#{{ $method->id }}</td>
                        <td>
                            <strong>{{ $method->user->name }}</strong><br>
                            <small class="text-muted">{{ $method->user->email }}</small>
                        </td>
                        <td>
                            <span class="badge badge-{{ $method->type === 'cash' ? 'success' : 'primary' }}">
                                {{ ucfirst(str_replace('_', ' ', $method->type)) }}
                            </span>
                        </td>
                        <td>
                            @if(in_array($method->type, ['credit_card', 'debit_card']))
                                {{ $method->masked_card_number }}
                                @if($method->isExpired())
                                    <span class="badge badge-danger ml-1">Expired</span>
                                @endif
                            @elseif($method->type === 'paypal')
                                {{ $method->details['email'] ?? 'N/A' }}
                            @elseif($method->type === 'bank_transfer')
                                {{ $method->details['bank_name'] ?? 'N/A' }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($method->is_default)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-secondary">No</span>
                            @endif
                        </td>
                        <td>
                            @if($method->isExpired())
                                <span class="badge badge-danger">Expired</span>
                            @else
                                <span class="badge badge-success">Active</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info">
                                {{ $method->payments()->count() }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('cms.payment-methods.show', $method) }}"
                                   class="btn btn-info btn-sm" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$method->payments()->exists())
                                <form action="{{ route('cms.payment-methods.destroy', $method) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm delete-payment-method-btn"
                                            data-method-id="{{ $method->id }}"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @else
                                <button class="btn btn-danger btn-sm" disabled
                                        title="Cannot delete - has associated payments">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Results Count -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                @if(method_exists($paymentMethods, 'total'))
                    Showing {{ $paymentMethods->firstItem() ?? 0 }} to {{ $paymentMethods->lastItem() ?? 0 }} of {{ $paymentMethods->total() }} payment methods
                @else
                    Showing all {{ $paymentMethods->count() }} payment methods
                @endif
            </div>

            <!-- Pagination Links (only if paginated) -->
            @if(method_exists($paymentMethods, 'links'))
                {{ $paymentMethods->links() }}
            @endif
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// SweetAlert for delete confirmation
document.querySelectorAll('.delete-payment-method-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const form = this.closest('form');
        const methodId = this.getAttribute('data-method-id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This payment method will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

// Show success/error messages with SweetAlert
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        timer: 4000,
        showConfirmButton: true
    });
@endif
</script>
@endpush
