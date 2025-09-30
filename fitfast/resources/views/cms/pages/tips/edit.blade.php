@extends('cms.layouts.app')

@section('page-title', 'Edit Tip')
@section('page-subtitle', 'Update fashion tip details')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3" style="background-color: #800020; border-color: #800020;">
                <h6 class="m-0 font-weight-bold" style="color: white;">Edit Tip</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.tips.update', $tip) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="title" style="font-weight: bold;">Tip Title *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title', $tip->title) }}"
                               placeholder="Enter fashion tip title" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="content" style="font-weight: bold;">Tip Content *</label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="8"
                                  placeholder="Write your fashion tip content here..." required>{{ old('content', $tip->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" style="background-color: #800020; border-color: #800020;">
                            <i class="fas fa-save"></i> Update Tip
                        </button>
                        <a href="{{ route('cms.tips.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3" style="background-color: #800020; border-color: #800020;">
                <h6 class="m-0 font-weight-bold" style="color: white;">Tip Information</h6>
            </div>
            <div class="card-body">
                <p><strong>Created:</strong> {{ $tip->created_at->format('M d, Y') }}</p>
                <p><strong>Last Updated:</strong> {{ $tip->updated_at->format('M d, Y') }}</p>
                <hr>
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('cms.tips.show', $tip) }}" class="btn btn-info btn-sm" style="background-color: #17a2b8; border-color: #17a2b8;">
                        <i class="fas fa-eye"></i> View Tip
                    </a>
                    <form action="{{ route('cms.tips.destroy', $tip) }}" method="POST" class="delete-form w-100">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-danger btn-sm w-100 delete-btn" data-tip-title="{{ $tip->title }}">
                            <i class="fas fa-trash"></i> Delete Tip
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
.btn-primary {
    background-color: #800020 !important;
    border-color: #800020 !important;
    border-radius: 6px;
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

.btn-danger {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    border-radius: 6px;
}

.btn-danger:hover {
    background-color: #c82333 !important;
    border-color: #bd2130 !important;
}

.card-header {
    background-color: #800020 !important;
    border-color: #800020 !important;
}

.card-header h6 {
    color: white !important;
}

.d-flex.flex-column.gap-2 {
    gap: 12px !important;
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
