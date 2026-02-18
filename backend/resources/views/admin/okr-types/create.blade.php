@extends('layouts.app')

@section('title', 'Add OKR Type - OKR Management System')

@section('content')
    <div class="row mb-3 justify-content-center">
        <div class="col-12 col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.okr-types') }}">OKR Types</a></li>
                    <li class="breadcrumb-item active">Add New Type</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-1">Add New OKR Type</h4>
                    <p class="text-muted mb-4">Create a new OKR type for individual employees or organization units.</p>

                    <form method="POST" action="{{ route('admin.okr-types.store') }}" id="okrTypeForm">
                        @csrf

                        <!-- Type Name -->
                        <div class="mb-3">
                            <label class="form-label">Type Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g., Individual, Team, Department" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Owner Type -->
                        <div class="mb-3">
                            <label class="form-label">Owner Type <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_employee" id="isEmployee" value="1"
                                    {{ old('is_employee', '1') == '1' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="isEmployee">
                                    <i class="ti ti-user me-1"></i>For Employee
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_employee" id="isOrgUnit" value="0"
                                    {{ old('is_employee', '1') == '0' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="isOrgUnit">
                                    <i class="ti ti-building me-1"></i>For Organization Unit
                                </label>
                            </div>
                            <small class="text-muted">Select whether this OKR type is for individual employees or organization units</small>
                            @error('is_employee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                            <small class="text-muted">Enable this type for use in the system</small>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('admin.okr-types') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>Create Type
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
