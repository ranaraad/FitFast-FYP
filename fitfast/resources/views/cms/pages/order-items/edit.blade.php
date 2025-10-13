@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Order Item - Order #{{ $order->id }}</h1>
    <a href="{{ route('cms.orders.order-items.index', $order) }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Order Items
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Edit Order Item Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.orders.order-items.update', [$order, $orderItem]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item_id">Item *</label>
                                <select name="item_id" id="item_id" class="form-control select2" required>
                                    <option value="">Select Item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" 
                                            data-sizes="{{ json_encode($item->available_sizes) }}"
                                            data-colors="{{ json_encode($item->available_colors) }}"
                                            data-price="{{ $item->price }}"
                                            {{ $orderItem->item_id == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }} - ${{ number_format($item->price, 2) }} (Stock: {{ $item->stock_quantity }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quantity">Quantity *</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" 
                                       min="1" value="{{ old('quantity', $orderItem->quantity) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="selected_size">Size *</label>
                                <select name="selected_size" id="selected_size" class="form-control" required>
                                    <option value="">Select Size</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="selected_color">Color *</label>
                                <select name="selected_color" id="selected_color" class="form-control" required>
                                    <option value="">Select Color</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="selected_brand">Brand</label>
                                <input type="text" name="selected_brand" id="selected_brand" class="form-control" 
                                       value="{{ old('selected_brand', $orderItem->selected_brand) }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Order Item</button>
                        <a href="{{ route('cms.orders.order-items.index', $order) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Current Item Details</h6>
            </div>
            <div class="card-body">
                <p><strong>Current Item:</strong> {{ $orderItem->item->name ?? 'N/A' }}</p>
                <p><strong>Size:</strong> {{ $orderItem->selected_size }}</p>
                <p><strong>Color:</strong> {{ $orderItem->selected_color }}</p>
                <p><strong>Quantity:</strong> {{ $orderItem->quantity }}</p>
                <p><strong>Unit Price:</strong> ${{ number_format($orderItem->unit_price, 2) }}</p>
                <p><strong>Line Total:</strong> ${{ number_format($orderItem->unit_price * $orderItem->quantity, 2) }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        function updateSizesAndColors() {
            var selectedOption = $('#item_id').find('option:selected');
            var sizes = selectedOption.data('sizes') || [];
            var colors = selectedOption.data('colors') || [];

            // Update sizes dropdown
            $('#selected_size').empty().append('<option value="">Select Size</option>');
            sizes.forEach(function(size) {
                $('#selected_size').append('<option value="' + size + '">' + size + '</option>');
            });

            // Update colors dropdown
            $('#selected_color').empty().append('<option value="">Select Color</option>');
            Object.keys(colors).forEach(function(colorKey) {
                $('#selected_color').append('<option value="' + colorKey + '">' + colors[colorKey] + '</option>');
            });

            // Set current values if editing
            @if(isset($orderItem))
            $('#selected_size').val('{{ $orderItem->selected_size }}');
            $('#selected_color').val('{{ $orderItem->selected_color }}');
            @endif
        }

        // Initial update
        updateSizesAndColors();

        // Update on item change
        $('#item_id').change(updateSizesAndColors);
    });
</script>
@endpush