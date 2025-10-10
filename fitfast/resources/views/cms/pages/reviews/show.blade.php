@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Review Details</h1>
    <div>
        <a href="{{ route('cms.reviews.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Reviews
        </a>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-lg-8">
        <!-- Review Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Review Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">ID</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $review->id }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Rating</p>
                    </div>
                    <div class="col-sm-9">
                        <div class="rating-stars">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $review->rating)
                                    <i class="fas fa-star text-warning fa-lg"></i>
                                @else
                                    <i class="far fa-star text-warning fa-lg"></i>
                                @endif
                            @endfor
                            <span class="badge badge-primary ml-2">{{ $review->rating }} out of 5</span>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Comment</p>
                    </div>
                    <div class="col-sm-9">
                        @if($review->hasComment())
                            <div class="border rounded p-3 bg-light">
                                {{ $review->comment }}
                            </div>
                        @else
                            <p class="text-muted mb-0">No comment provided</p>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Created At</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $review->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Updated At</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $review->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- User Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-circle fa-3x text-primary"></i>
                    </div>
                    <h5>{{ $review->user->name }}</h5>
                    <p class="text-muted">{{ $review->user->email }}</p>

                    <div class="row mt-3">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h6 class="mb-0">{{ $review->user->reviews->count() }}</h6>
                                <small class="text-muted">Reviews</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h6 class="mb-0">{{ $review->user->orders->count() }}</h6>
                                <small class="text-muted">Orders</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Item Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-tshirt fa-3x text-info"></i>
                    </div>
                    <h5>{{ $review->item->name }}</h5>
                    <p class="text-muted">${{ number_format($review->item->price, 2) }}</p>

                    <div class="row mt-3">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h6 class="mb-0">{{ $review->item->averageRating() }}</h6>
                                <small class="text-muted">Avg Rating</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h6 class="mb-0">{{ $review->item->reviewCount() }}</h6>
                                <small class="text-muted">Reviews</small>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('cms.items.show', $review->item) }}" class="btn btn-sm btn-outline-primary mt-3">
                        <i class="fas fa-external-link-alt"></i> View Item
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete Review
                    </button>
                    <form id="delete-form" action="{{ route('cms.reviews.destroy', $review) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.rating-stars {
    font-size: 18px;
}
</style>
@endpush

@push('scripts')
<script>
    function confirmDelete() {
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
                document.getElementById('delete-form').submit();
            }
        });
    }
</script>
@endpush
