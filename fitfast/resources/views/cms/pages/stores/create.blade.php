@extends('cms.layouts.app')

@section('page-title', 'Stores Management')
@section('page-subtitle', 'Manage stores in the system')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Store</h1>
    <a href="{{ route('cms.stores.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Stores
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Store Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.stores.store') }}" method="POST" id="storeForm" enctype="multipart/form-data">
                    @csrf

                    <!-- Store Visuals Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-images mr-2"></i>Store Visuals
                            </h5>
                        </div>

                        <!-- Logo Upload -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="logo">Store Logo</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('logo') is-invalid @enderror"
                                           id="logo" name="logo" accept="image/*">
                                    <label class="custom-file-label" for="logo" id="logoLabel">
                                        <i class="fas fa-upload mr-2"></i>Choose logo file...
                                    </label>
                                    @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">
                                    Recommended: Square image, 200x200px, JPG/PNG/SVG, max 2MB
                                </small>

                                <!-- Logo Preview -->
                                <div class="logo-preview mt-3 text-center" id="logoPreview" style="display: none;">
                                    <div class="preview-container">
                                        <img id="logoPreviewImage" class="img-thumbnail rounded-circle"
                                             style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #e3f2fd;">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLogo()">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Banner Upload -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="banner_image">Banner Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('banner_image') is-invalid @enderror"
                                           id="banner_image" name="banner_image" accept="image/*">
                                    <label class="custom-file-label" for="banner_image" id="bannerLabel">
                                        <i class="fas fa-upload mr-2"></i>Choose banner file...
                                    </label>
                                    @error('banner_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">
                                    Recommended: 1200x400px, JPG/PNG, max 5MB
                                </small>

                                <!-- Banner Preview -->
                                <div class="banner-preview mt-3 text-center" id="bannerPreview" style="display: none;">
                                    <div class="preview-container">
                                        <img id="bannerPreviewImage" class="img-thumbnail"
                                             style="max-height: 150px; width: 100%; object-fit: cover;">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeBanner()">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-info-circle mr-2"></i>Basic Information
                            </h5>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Store Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required
                                       placeholder="Enter store name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select class="form-control @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Store Admin Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-user-shield mr-2"></i>Store Administration
                            </h5>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_id">Store Admin</label>
                                <select class="form-control @error('user_id') is-invalid @enderror"
                                        id="user_id" name="user_id">
                                    <option value="">Select Store Admin</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Assign a Store Admin to manage this store
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-align-left mr-2"></i>Additional Information
                            </h5>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3"
                                          placeholder="Enter store description">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                          id="address" name="address" rows="2"
                                          placeholder="Enter store address">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact_info">Contact Information</label>
                                <textarea class="form-control @error('contact_info') is-invalid @enderror"
                                          id="contact_info" name="contact_info" rows="2"
                                          placeholder="Enter contact information (phone, email, etc.)">{{ old('contact_info') }}</textarea>
                                @error('contact_info')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    You can enter phone numbers, email addresses, or any other contact details.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Create Store
                                    </button>
                                    <a href="{{ route('cms.stores.index') }}" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                                <div class="text-muted">
                                    <small>* Required fields</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.custom-file-label::after {
    content: "Browse";
}

.preview-container {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 15px;
    background-color: #f8f9fa;
}

.logo-preview .preview-container {
    max-width: 200px;
    margin: 0 auto;
}

.banner-preview .preview-container {
    max-width: 400px;
    margin: 0 auto;
}

.form-section {
    border-left: 4px solid #007bff;
    padding-left: 15px;
    margin-bottom: 2rem;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // File input label update
        $('#logo').on('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Choose logo file...';
            $('#logoLabel').html('<i class="fas fa-upload mr-2"></i>' + fileName);
            previewImage(this, 'logoPreviewImage', 'logoPreview');
        });

        $('#banner_image').on('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Choose banner file...';
            $('#bannerLabel').html('<i class="fas fa-upload mr-2"></i>' + fileName);
            previewImage(this, 'bannerPreviewImage', 'bannerPreview');
        });

        // Image preview function
        function previewImage(input, previewId, previewContainerId) {
            const preview = document.getElementById(previewId);
            const previewContainer = document.getElementById(previewContainerId);
            const file = input.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }

                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
                preview.src = '';
            }
        }

        // Remove logo
        window.removeLogo = function() {
            $('#logo').val('');
            $('#logoLabel').html('<i class="fas fa-upload mr-2"></i>Choose logo file...');
            $('#logoPreview').hide();
        }

        // Remove banner
        window.removeBanner = function() {
            $('#banner_image').val('');
            $('#bannerLabel').html('<i class="fas fa-upload mr-2"></i>Choose banner file...');
            $('#bannerPreview').hide();
        }

        // File validation
        function validateFile(file, maxSize, allowedTypes) {
            if (file.size > maxSize) {
                return `File size must be less than ${maxSize / 1024 / 1024}MB`;
            }

            if (!allowedTypes.includes(file.type)) {
                return 'Invalid file type. Please select an image file.';
            }

            return null;
        }

        // Form validation
        $('#storeForm').on('submit', function(e) {
            const name = $('#name').val();
            const status = $('#status').val();

            if (!name || !status) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information',
                    text: 'Please fill in all required fields.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            // Validate logo file if selected
            const logoFile = $('#logo')[0].files[0];
            if (logoFile) {
                const logoError = validateFile(logoFile, 2 * 1024 * 1024, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml']);
                if (logoError) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Logo File',
                        text: logoError,
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
            }

            // Validate banner file if selected
            const bannerFile = $('#banner_image')[0].files[0];
            if (bannerFile) {
                const bannerError = validateFile(bannerFile, 5 * 1024 * 1024, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']);
                if (bannerError) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Banner File',
                        text: bannerError,
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
            }

            // Show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Store...');

            return true;
        });

        // Reset form loading state if validation fails
        $('#storeForm').on('invalid-form.validate', function() {
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Create Store');
        });
    });
</script>
@endpush
