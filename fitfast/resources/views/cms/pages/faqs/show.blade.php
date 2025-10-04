@extends('cms.layouts.app')

@section('page-title', 'FAQ Details')
@section('page-subtitle', 'View fashion FAQ information')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">FAQ Details</h6>
                <div class="d-flex" style="gap: 10px;">
                    <a href="{{ route('cms.faqs.edit', $faq) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('cms.faqs.destroy', $faq) }}" method="POST" class="d-inline delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-faq-question="{{ $faq->question }}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="faq-item">
                    <h4 class="mb-3">
                        <i class="fas fa-question-circle text-warning mr-2"></i>
                        {{ $faq->question }}
                    </h4>
                    <div class="bg-light p-4 rounded">
                        <h6 class="text-success mb-3">
                            <i class="fas fa-info-circle text-success mr-2"></i>
                            Answer:
                        </h6>
                        {!! nl2br(e($faq->answer)) !!}
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <small class="text-muted">
                        <strong>Created:</strong> {{ $faq->created_at->format('F d, Y \a\t h:i A') }}
                    </small>
                    <br>
                    <small class="text-muted">
                        <strong>Last Updated:</strong> {{ $faq->updated_at->format('F d, Y \a\t h:i A') }}
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
                <a href="{{ route('cms.faqs.create') }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-plus"></i> Create New FAQ
                </a>
                <a href="{{ route('cms.faqs.edit', $faq) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-edit"></i> Edit This FAQ
                </a>
                <a href="{{ route('cms.faqs.index') }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-list"></i> Back to All FAQs
                </a>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">FAQ Statistics</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-calendar fa-2x"></i>
                    </div>
                    <h5 class="text-primary">{{ $faq->created_at->diffForHumans() }}</h5>
                    <small class="text-muted">Created</small>
                </div>
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
            const faqQuestion = this.getAttribute('data-faq-question');
            const form = this.closest('.delete-form');

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete the FAQ: "${faqQuestion}". This action cannot be undone!`,
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
