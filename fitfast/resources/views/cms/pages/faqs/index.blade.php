@extends('cms.layouts.app')

@section('page-title', 'FAQs Management')
@section('page-subtitle', 'Manage fashion-related frequently asked questions')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold">All FAQs</h6>
        <a href="{{ route('cms.faqs.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus fa-sm"></i> Add New FAQ
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

        @if($faqs->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Answer Preview</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($faqs as $faq)
                        <tr>
                            <td class="font-weight-bold">{{ $faq->question }}</td>
                            <td>{{ Str::limit($faq->answer, 100) }}</td>
                            <td>{{ $faq->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="d-flex" style="gap: 8px;">
                                    <a href="{{ route('cms.faqs.show', $faq) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('cms.faqs.edit', $faq) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('cms.faqs.destroy', $faq) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-faq-question="{{ $faq->question }}">
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
                {{ $faqs->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No FAQs found</h5>
                <p class="text-muted">Get started by creating your first fashion FAQ!</p>
                <a href="{{ route('cms.faqs.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First FAQ
                </a>
            </div>
        @endif
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
