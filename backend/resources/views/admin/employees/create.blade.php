@extends('layouts.app')

@section('title', 'Add Employee - OKR Management System')

@section('content')
  <!-- Breadcrumb -->
  <div class="row mb-3 justify-content-center">
    <div class="col-12 col-lg-8">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.employees') }}">Employees</a></li>
          <li class="breadcrumb-item active">Add New Employee</li>
        </ol>
      </nav>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-12 col-lg-8 mb-4">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title mb-1">Add New Employee</h4>
          <p class="text-muted mb-4">Fill in the employee details below to create a new account.</p>

          <form method="POST" action="{{ route('admin.employees.store') }}" id="employeeForm">
            @csrf

            <!-- Basic Information -->
            <h6 class="mb-3">Basic Information</h6>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Enter full name" required>
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="Enter username" required>
                @error('username')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter email address" required>
                @error('email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Position</label>
                <input type="text" name="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position') }}" placeholder="Enter job position">
                @error('position')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Role and Status -->
            <h6 class="mb-3">Role & Status</h6>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="role_id" class="form-select @error('role_id') is-invalid @enderror">
                  <option value="">Select Role</option>
                  @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                  @endforeach
                </select>
                @error('role_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                  <option value="1" selected>Active</option>
                  <option value="0">Inactive</option>
                </select>
                <small class="text-muted">Active employees can log in to the system</small>
              </div>
            </div>

            <!-- Password -->
            <h6 class="mb-3">Password</h6>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">Password <span class="text-danger">*</span></label>
                <div class="input-group input-group-merge">
                  <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter password" required>
                  <span class="input-group-text cursor-pointer" onclick="togglePassword('password', this)">
                    <i class="ti ti-eye"></i>
                  </span>
                </div>
                @error('password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Minimum 8 characters</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <div class="input-group input-group-merge">
                  <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="Confirm password" required>
                  <span class="input-group-text cursor-pointer" onclick="togglePassword('password_confirmation', this)">
                    <i class="ti ti-eye"></i>
                  </span>
                </div>
                @error('password_confirmation')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
              <a href="{{ route('admin.employees') }}" class="btn btn-outline-secondary">
                <i class="ti ti-x me-1"></i>Cancel
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="ti ti-check me-1"></i>Create Employee
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
    function togglePassword(inputId, element) {
      const input = document.querySelector(`input[name="${inputId}"]`);
      const icon = element.querySelector('i');

      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('ti-eye');
        icon.classList.add('ti-eye-off');
      } else {
        input.type = 'password';
        icon.classList.remove('ti-eye-off');
        icon.classList.add('ti-eye');
      }
    }

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
