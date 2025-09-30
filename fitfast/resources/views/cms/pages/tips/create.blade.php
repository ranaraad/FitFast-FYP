@extends('cms.layouts.app')

@section('page-title', 'Create New Tip')
@section('page-subtitle', 'Add a new fashion tip')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Tip Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.tips.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="title" style="font-weight: bold;">Tip Title *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}"
                               placeholder="Enter tip title" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Keep it short and descriptive (max: 200 characters)</small>
                    </div>

                    <div class="form-group">
                        <label for="content" style="font-weight: bold;">Tip Content *</label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="8"
                                  placeholder="Write your fashion tip content here..." required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Provide detailed fashion advice or information</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Tip
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
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Tips</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    <i class="fas fa-lightbulb text-warning mr-2"></i>
                    Create helpful fashion tips that educate and inspire your users.
                </p>
                <ul class="small text-muted pl-3">
                    <li>Keep titles clear and concise</li>
                    <li>Provide actionable advice</li>
                    <li>Use simple, easy-to-understand language</li>
                    <li>Focus on practical fashiion guidance</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
