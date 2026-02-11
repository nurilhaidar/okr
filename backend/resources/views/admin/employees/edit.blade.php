@extends('layouts.app')

@section('title', 'Edit Employee - OKR Management System')

@section('sidebar')
  <!-- Menu -->
  <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
      <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
        <span class="app-brand-logo demo">
          <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="#7367F0" />
            <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616" />
            <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616" />
            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z" fill="#7367F0" />
          </svg>
        </span>
        <span class="app-brand-text demo menu-text fw-bold">OKR Admin</span>
      </a>

      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
        <i class="ti ti-x d-block ti-sm align-middle"></i>
      </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
      <!-- Dashboard -->
      <li class="menu-item">
        <a href="{{ route('admin.dashboard') }}" class="menu-link">
          <i class="menu-icon tf-icons ti ti-smart-home"></i>
          <div data-i18n="Dashboard">Dashboard</div>
        </a>
      </li>

      <!-- Employees -->
      <li class="menu-item active">
        <a href="{{ route('admin.employees') }}" class="menu-link">
          <i class="menu-icon tf-icons ti ti-users"></i>
          <div data-i18n="Employees">Employees</div>
        </a>
      </li>

      <!-- Roles -->
      <li class="menu-item">
        <a href="{{ route('admin.roles') }}" class="menu-link">
          <i class="menu-icon tf-icons ti ti-shield"></i>
          <div data-i18n"Roles">Roles</div>
        </a>
      </li>

      <!-- Organization Units -->
      <li class="menu-item">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons ti ti-building"></i>
          <div data-i18n="Organization">Organization</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item">
            <a href="#" class="menu-link">
              <div data-i18n>Units</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="#" class="menu-link">
              <div data-i18n>Unit Types</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="#" class="menu-link">
              <div data-i18n>Unit Roles</div>
            </a>
          </li>
        </ul>
      </li>

      <!-- OKR Types -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="menu-icon tf-icons ti ti-tags"></i>
          <div data-i18n="OKR Types">OKR Types</div>
        </a>
      </li>

      <!-- All OKRs -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="menu-icon tf-icons ti ti-target"></i>
          <div data-i18n="All OKRs">All OKRs</div>
        </a>
      </li>
    </ul>
  </aside>
@endsection

@section('navbar')
  <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
      <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0);">
        <i class="ti ti-menu-2 ti-md"></i>
      </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
      <ul class="navbar-nav flex-row align-items-center ms-auto">
        <li class="nav-item navbar-dropdown dropdown-user">
          <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <div class="avatar avatar-online">
              <img src="/vuexy/img/avatars/5.png" alt class="w-px-40 h-auto rounded-circle" />
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="javascript:void(0);">
                <div class="d-flex">
                  <div class="flex-shrink-0 me-3">
                    <div class="avatar avatar-online">
                      <img src="/vuexy/img/avatars/5.png" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                  </div>
                  <div>
                    <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                    <small class="text-muted">{{ auth()->user()->role->name ?? 'No Role' }}</small>
                  </div>
                </div>
              </a>
            </li>
            <li>
              <div class="dropdown-divider"></div>
            </li>
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item">
                  <i class="ti ti-logout me-2"></i>
                  <span class="align-middle">Log Out</span>
                </button>
              </form>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
@endsection

@section('content')
  <div class="row">
    <div class="col-12 col-lg-8 mb-4">
      <div class="card">
        <div class="card-body">
          <h4 class="mb-1">Edit Employee</h4>
          <p class="text-muted">Update employee information for {{ $employee->name }}.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 col-lg-8">
      <div class="card">
        <div class="card-body">
          <form method="POST" action="{{ route('admin.employees.update', $employee->id) }}">
            @method('PUT')
            @csrf

            <!-- Basic Information -->
            <h5 class="mb-3">Basic Information</h5>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->name) }}" required>
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $employee->username) }}" required>
                @error('username')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->email) }}" required>
                @error('email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Position</label>
                <input type="text" name="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position', $employee->position) }}">
                @error('position')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Role and Status -->
            <h5 class="mb-3">Role & Status</h5>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="role_id" class="form-select @error('role_id') is-invalid @enderror">
                  <option value="">Select Role</option>
                  @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id', $employee->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                  @endforeach
                </select>
                @error('role_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label d-flex justify-content-between">
                  <span>Status</span>
                </label>
                <div class="form-check form-switch mt-2">
                  <input class="form-check-input" type="checkbox" name="is_active" id="isActive" {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                  <label class="form-check-label" for="isActive">Active</label>
                </div>
              </div>
            </div>

            <!-- Password -->
            <h5 class="mb-3">Change Password</h5>
            <p class="text-muted small mb-3">Leave blank to keep current password</p>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">New Password</label>
                <div class="input-group input-group-merge">
                  <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
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
                <label class="form-label">Confirm New Password</label>
                <div class="input-group input-group-merge">
                  <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror">
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
            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('admin.employees') }}" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">
                <i class="ti ti-check me-2"></i>Update Employee
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Employee Info Sidebar -->
    <div class="col-12 col-lg-4">
      <div class="card mb-4">
        <div class="card-body text-center">
          <div class="avatar bg-label-primary mx-auto mb-3" style="width: 80px; height: 80px;">
            <span class="avatar-initials rounded-circle" style="font-size: 2rem;">{{ substr($employee->name, 0, 1) }}</span>
          </div>
          <h5 class="mb-1">{{ $employee->name }}</h5>
          <p class="text-muted mb-2">{{ $employee->position ?? 'No Position' }}</p>
          @if($employee->role)
            <span class="badge bg-label-primary">{{ $employee->role->name }}</span>
          @endif
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-3">
            <i class="ti ti-info-circle me-2"></i>Information
          </h5>
          <ul class="mb-0">
            <li class="mb-2">
              <strong>Email:</strong><br>
              <span class="text-muted">{{ $employee->email }}</span>
            </li>
            <li class="mb-2">
              <strong>Username:</strong><br>
              <span class="text-muted">{{ $employee->username }}</span>
            </li>
            <li class="mb-2">
              <strong>Status:</strong><br>
              @if($employee->is_active)
                <span class="badge bg-label-success">Active</span>
              @else
                <span class="badge bg-label-secondary">Inactive</span>
              @endif
            </li>
            <li>
              <strong>Created:</strong><br>
              <span class="text-muted">{{ \Carbon\Carbon::parse($employee->created_at)->format('M d, Y') }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page_scripts')
  <script>
    function togglePassword(inputId, element) {
      const input = document.getElementById(inputId) || document.querySelector(`input[name="${inputId}"]`);
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
