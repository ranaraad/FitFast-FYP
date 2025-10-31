@extends('cms.layouts.app')

@section('page-title', 'Review Management')
@section('page-subtitle', 'Manage user reviews')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Reviews Management</h1>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Reviews</h6>
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

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered" id="reviewsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Item</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reviews as $review)
                            <tr>
                                <td>{{ $review->id }}</td>
                                <td>
                                    <strong>{{ $review->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $review->user->email }}</small>
                                </td>
                                <td>
                                    <strong>{{ $review->item->name }}</strong>
                                    <br>
                                    <small class="text-muted">${{ number_format($review->item->price, 2) }}</small>
                                </td>
                                <td>
                                    <div class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star text-warning"></i>
                                            @endif
                                        @endfor
                                        <span class="badge badge-light ml-2">{{ $review->rating }}/5</span>
                                    </div>
                                </td>
                                <td>
                                    @if($review->hasComment())
                                        {{ Str::limit($review->comment, 50) }}
                                    @else
                                        <span class="text-muted">No comment</span>
                                    @endif
                                </td>
                                <td>{{ $review->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('cms.reviews.show', $review) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $review->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $review->id }}" action="{{ route('cms.reviews.destroy', $review) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.rating-stars {
    font-size: 14px;
}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#reviewsTable').DataTable({
            "pageLength": 25,
            "order": [[0, 'desc']],
            "columnDefs": [
                { "orderable": false, "targets": [6] } // Disable sorting for actions column
            ]
        });
    });

    function confirmDelete(reviewId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + reviewId).submit();
            }
        });
    }
</script>
@endpush
