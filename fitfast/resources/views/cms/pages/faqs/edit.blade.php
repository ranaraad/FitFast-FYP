@extends('cms.layouts.app')

@section('page-title', 'Edit FAQ')
@section('page-subtitle', 'Update fashion FAQ details')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Edit FAQ</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.faqs.update', $faq) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="question" style="font-weight: bold;">Question *</label>
                        <input type="text" class="form-control @error('question') is-invalid @enderror"
                               id="question" name="question" value="{{ old('question', $faq->question) }}"
                               placeholder="Enter the customer question" required>
                        @error('question')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="answer" style="font-weight: bold;">Answer *</label>
                        <textarea class="form-control @error('answer') is-invalid @enderror"
                                  id="answer" name="answer" rows="8"
                                  placeholder="Provide a clear and helpful answer..." required>{{ old('answer', $faq->answer) }}</textarea>
                        @error('answer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update FAQ
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
                <h6 class="m-0 font-weight-bold">FAQ Information</h6>
            </div>
            <div class="card-body">
                <p><strong>Created:</strong> {{ $faq->created_at->format('M d, Y') }}</p>
                <p><strong>Last Updated:</strong> {{ $faq->updated_at->format('M d, Y') }}</p>
                <hr>
                <a href="{{ route('cms.faqs.show', $faq) }}" class="btn btn-info btn-block btn-sm">
                    <i class="fas fa-eye"></i> View FAQ
                </a>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Preview</h6>
            </div>
            <div class="card-body">
                <h6 class="text-primary">Q: {{ $faq->question }}</h6>
                <p class="small text-muted mt-2">
                    {{ Str::limit($faq->answer, 150) }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
