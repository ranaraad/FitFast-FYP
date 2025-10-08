@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Item</h1>
    <a href="{{ route('cms.items.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Items
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Item Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.items.store') }}" method="POST" id="itemForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Item Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required
                                       placeholder="Enter item name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="store_id">Store *</label>
                                <select class="form-control @error('store_id') is-invalid @enderror"
                                        id="store_id" name="store_id" required>
                                    <option value="">Select Store</option>
                                    @foreach(\App\Models\Store::all() as $store)
                                        <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('store_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="price">Price ($) *</label>
                                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror"
                                       id="price" name="price" value="{{ old('price') }}" required
                                       placeholder="0.00" min="0">
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select class="form-control @error('category_id') is-invalid @enderror"
                                    id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $item->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }} ({{ $category->type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="color">Color *</label>
                                <input type="text" class="form-control @error('color') is-invalid @enderror"
                                       id="color" name="color" value="{{ old('color') }}" required
                                       placeholder="e.g., Red, Blue, Black">
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3"
                                  placeholder="Enter item description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @include('cms.pages.items.partials.sizing-data')

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Item
                            </button>
                            <a href="{{ route('cms.items.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Form validation
        $('#itemForm').on('submit', function() {
            const price = parseFloat($('#price').val());
            const stock = parseInt($('#stock_quantity').val());

            if (price < 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Price',
                    text: 'Price cannot be negative.'
                });
                return false;
            }

            if (stock < 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Stock',
                    text: 'Stock quantity cannot be negative.'
                });
                return false;
            }

            // Validate JSON for sizing data if provided
            const sizingData = $('#sizing_data').val();
            if (sizingData.trim() !== '') {
                try {
                    JSON.parse(sizingData);
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid JSON',
                        text: 'Sizing data must be valid JSON format.'
                    });
                    return false;
                }
            }

            return true;
        });
    });
</script>
@endpush
