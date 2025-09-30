@extends('cms.layouts.app')

@section('page-title', $tip->title)
@section('page-subtitle', 'Tip Details')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Tip Details</h6>
                <div class="d-flex" style="gap: 10px;">
                    <a href="{{ route('cms.tips.edit', $tip) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('cms.tips.destroy', $tip) }}" method="POST" class="d-inline delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-tip-title="{{ $tip->title }}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <h4 class="mb-3">{{ $tip->title }}</h4>
                <div class="bg-light p-4 rounded">
                    {!! nl2br(e($tip->content)) !!}
                </div>

                <div class="mt-4 pt-3 border-top">
                    <small class="text-muted">
                        <strong>Created:</strong> {{ $tip->created_at->format('F d, Y \a\t h:i A') }}
                    </small>
                    <br>
                    <small class="text-muted">
                        <strong>Last Updated:</strong> {{ $tip->updated_at->format('F d, Y \a\t h:i A') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Quick Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('cms.tips.create') }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-plus"></i> Create New Tip
                </a>
                <a href="{{ route('cms.tips.edit', $tip) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-edit"></i> Edit This Tip
                </a>
                <a href="{{ route('cms.tips.index') }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-list"></i> Back to All Tips
                </a>
            </div>
        </div>
    </div>
</div>

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
                text: `You are about to delete the fashion tip: "${tipTitle}". This action cannot be undone!`,
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
