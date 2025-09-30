@extends('cms.layouts.app')

@section('page-title', 'Tips Management')
@section('page-subtitle', 'Manage fashion site tips and advice')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold">All Tips</h6>
        <a href="{{ route('cms.tips.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus fa-sm"></i> Add New Tip
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($tips->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Content Preview</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tips as $tip)
                        <tr>
                            <td class="font-weight-bold">{{ $tip->title }}</td>
                            <td>{{ Str::limit($tip->content, 100) }}</td>
                            <td>{{ $tip->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group" style="gap: 8px;">
                                    <a href="{{ route('cms.tips.show', $tip) }}" class="btn btn-info rounded"
                                       style="background-color: #17a2b8; border-color: #17a2b8; border-radius: 6px; padding: 0.375rem 0.75rem;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('cms.tips.edit', $tip) }}" class="btn btn-warning rounded"
                                       style="background-color: #ffc107; border-color: #ffc107; color: #212529; border-radius: 6px; padding: 0.375rem 0.75rem;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('cms.tips.destroy', $tip) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger rounded delete-btn"
                                                data-tip-title="{{ $tip->title }}"
                                                style="background-color: #dc3545; border-color: #dc3545; border-radius: 6px; padding: 0.375rem 0.75rem;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $tips->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-lightbulb fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No tips found</h5>
                <p class="text-muted">Get started by creating your first fashion tip!</p>
                <a href="{{ route('cms.tips.create') }}" class="btn btn-primary rounded"
                   style="background-color: #800020; border-color: #800020; border-radius: 8px;">
                    <i class="fas fa-plus"></i> Create First Tip
                </a>
            </div>
        @endif
    </div>
</div>

<style>
/* Custom button styles to match the burgundy theme */
.btn-primary {
    background-color: #800020 !important;
    border-color: #800020 !important;
    border-radius: 8px;
}

.btn-primary:hover {
    background-color: #600018 !important;
    border-color: #600018 !important;
}

.btn-info {
    background-color: #17a2b8 !important;
    border-color: #17a2b8 !important;
    border-radius: 6px;
}

.btn-info:hover {
    background-color: #138496 !important;
    border-color: #117a8b !important;
}

.btn-warning {
    background-color: #ffc107 !important;
    border-color: #ffc107 !important;
    color: #212529 !important;
    border-radius: 6px;
}

.btn-warning:hover {
    background-color: #e0a800 !important;
    border-color: #d39e00 !important;
    color: #212529 !important;
}

.btn-danger {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    border-radius: 6px;
}

.btn-danger:hover {
    background-color: #c82333 !important;
    border-color: #bd2130 !important;
}

/* Button group spacing */
.btn-group-sm {
    gap: 8px;
}

.btn-group-sm .btn {
    border-radius: 6px !important;
    padding: 0.375rem 0.75rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert for delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-btn');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const tipTitle = this.getAttribute('data-tip-title');
            const form = this.closest('.delete-form');

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete the tip: "${tipTitle}". This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#800020',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the form if confirmed
                    form.submit();
                }
            });
        });
    });

    // Success message with SweetAlert if there's a success flash message
    @if(session('success'))
        Swal.fire({
            title: 'Success!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonColor: '#800020',
            timer: 3000,
            showConfirmButton: true
        });
    @endif
});
</script>
@endsection
