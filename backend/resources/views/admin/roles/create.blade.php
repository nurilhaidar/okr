@extends('layouts.app')

@section('title', 'Add Role - OKR Management System')

@section('content')
    <!-- Breadcrumb -->
    <div class="row mb-3 justify-content-center">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.roles') }}">Roles</a></li>
                    <li class="breadcrumb-item active">Add New Role</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Form Card -->
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-1">Add New Role</h4>
                    <p class="text-muted mb-4">Create a new system-wide role for employee permissions.</p>

                    <form method="POST" action="{{ route('admin.roles.store') }}" id="roleForm">
                        @csrf

                        <!-- Role Name -->
                        <div class="mb-3">
                            <label class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g., Admin, Manager, Supervisor" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                            <small class="text-muted">Enable this role for use in the system</small>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Optional: Describe what this role has access to">{{ old('description') }}</textarea>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('admin.roles') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>Create Role
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Display toastr notifications for CRUD operations
            @if (session('success'))
                showToast('Success', '{{ session('success') }}', 'success');
            @endif

            @if (session('error'))
                showToast('Error', '{{ session('error') }}', 'error');
            @endif

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    showToast('Validation Error', '{{ $error }}', 'error');
                @endforeach
            @endif

            // Auto-dismiss alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                });
            }, 5000);
        });
    </script>
@endsection
