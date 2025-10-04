@extends('cms.layouts.app')

@section('page-title', 'Create New FAQ')
@section('page-subtitle', 'Add a new fashion-related question and answer')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">FAQ Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.faqs.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="question" style="font-weight: bold;">Question *</label>
                        <input type="text" class="form-control @error('question') is-invalid @enderror"
                               id="question" name="question" value="{{ old('question') }}"
                               placeholder="Enter the customer question" required>
                        @error('question')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Common questions about fashion, sizing, shipping, etc. (max: 500 characters)</small>
                    </div>

                    <div class="form-group">
                        <label for="answer" style="font-weight: bold;">Answer *</label>
                        <textarea class="form-control @error('answer') is-invalid @enderror"
                                  id="answer" name="answer" rows="8"
                                  placeholder="Provide a clear and helpful answer..." required>{{ old('answer') }}</textarea>
                        @error('answer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Provide detailed, helpful information for your customers</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create FAQ
                        </button>
                        <a href="{{ route('cms.faqs.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">FAQ Guidelines</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    <i class="fas fa-info-circle text-info mr-2"></i>
                    Create helpful FAQs that address common fashion customer concerns.
                </p>
                <ul class="small text-muted pl-3">
                    <li>Focus on common fashion questions</li>
                    <li>Be clear and concise in answers</li>
                    <li>Include sizing and fit information</li>
                    <li>Cover shipping and return policies</li>
                    <li>Address fabric care instructions</li>
                    <li>Explain styling recommendations</li>
                </ul>

                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="text-primary">Common Topics:</h6>
                    <small class="text-muted">
                        • Sizing & Fit Guides<br>
                        • Shipping & Delivery<br>
                        • Returns & Exchanges<br>
                        • Fabric Care<br>
                        • Styling Advice<br>
                        • Product Quality
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
