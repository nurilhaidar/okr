@extends('layouts.app')

@section('title', 'OKR Types - OKR Management System')

@section('content')
  <!-- Breadcrumb -->
  <div class="row mb-3">
    <div class="col-12">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">OKR Types</li>
        </ol>
      </nav>
    </div>
  </div>

  <!-- Header Card -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4 class="mb-1">OKR Types</h4>
              <p class="text-muted mb-0">Manage OKR types for individual employees and organization units.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTypeModal">
              <i class="ti ti-plus me-1"></i>Add Type
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Types Table -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Type</th>
                  <th>OKR Types Used</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($okrTypes as $type)
                  <tr>
                    <td>{{ $type->name }}</td>
                    <td>
                      @if($type->is_employee)
                        <span class="badge bg-label-primary">
                          <i class="ti ti-user me-1"></i>Employee
                        </span>
                      @else
                        <span class="badge bg-label-success">
                          <i class="ti ti-building me-1"></i>OrgUnit
                        </span>
                      @endif
                    </td>
                    <td>
                      @if($type->okrs_count > 0)
                        <span class="badge bg-label-warning">{{ $type->okrs_count }}</span>
                      @else
                        <span class="text-muted">0</span>
                      @endif
                    </td>
                    <td>
                      <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-icon btn-outline-primary" onclick="editType({{ $type->id }}, '{{ $type->name }}', {{ $type->is_employee ? 'true' : 'false' }}, {{ $type->is_active ? 'true' : 'false' }})">
                          <i class="ti ti-pencil"></i>
                        </button>
                        @if($type->is_active)
                          <button class="btn btn-sm btn-icon btn-outline-warning" onclick="deactivateType({{ $type->id }})" title="Deactivate">
                            <i class="ti ti-player-pause"></i>
                          </button>
                        @else
                          <button class="btn btn-sm btn-icon btn-outline-success" onclick="activateType({{ $type->id }})" title="Activate">
                            <i class="ti ti-player-play"></i>
                          </button>
                        @endif
                        <form method="POST" action="{{ route('admin.okr-types.destroy', $type->id) }}" class="d-inline">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" onclick="return confirm('Are you sure you want to delete this type?')">
                            <i class="ti ti-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Type Modal -->
  <div class="modal fade" id="addTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New OKR Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('admin.okr-types.store') }}">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Type Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Individual, Team" required>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" name="is_employee" class="form-check-input" id="addIsEmployee">
              <label class="form-check-label" for="addIsEmployee">
                <i class="ti ti-user me-1"></i>For Employee
              </label>
              <small class="text-muted d-block">Check if this type is for individual employees (unchecked = for organization units)</small>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" name="is_active" id="addIsActive" checked>
                <label class="form-check-label" for="addIsActive">
                  <i class="ti ti-activity me-1"></i>Active
                </label>
                <small class="text-muted d-block">Enable this type for use in the system</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-check me-2"></i>Save Type
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Type Modal -->
  <div class="modal fade" id="editTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit OKR Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('admin.okr-types.update', 0) }}" id="editTypeForm">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <input type="hidden" name="id" id="editTypeId">
            <div class="mb-3">
              <label class="form-label">Type Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="editTypeName" class="form-control" placeholder="e.g., Individual, Team" required>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" name="is_employee" class="form-check-input" id="editIsEmployee">
              <label class="form-check-label" for="editIsEmployee">
                <i class="ti ti-user me-1"></i>For Employee
              </label>
              <small class="text-muted d-block">Check if this type is for individual employees (unchecked = for organization units)</small>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" name="is_active" id="editIsActive" checked>
                <label class="form-check-label" for="editIsActive">
                  <i class="ti ti-activity me-1"></i>Active
                </label>
                <small class="text-muted d-block">Enable this type for use in the system</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-check me-2"></i>Update Type
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('page_scripts')
  <script>
    function editType(id, name, isEmployee, isActive) {
      document.getElementById('editTypeId').value = id;
      document.getElementById('editTypeName').value = name;
      document.getElementById('editIsEmployee').checked = isEmployee;
      document.getElementById('editIsActive').checked = isActive;
      document.getElementById('editTypeForm').action = '/admin/okr-types/' + id;

      const modal = new bootstrap.Modal(document.getElementById('editTypeModal'));
      modal.show();
    }

    function deactivateType(id) {
      if (!confirm('Are you sure you want to deactivate this type?')) return;

      fetch(`/admin/okr-types/${id}/deactivate`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        }
      })
      .then(response => {
        if (response.ok) {
          showToast('Success', 'Type deactivated successfully', 'success');
          location.reload();
        } else {
          return response.json().then(data => {
            showToast('Error', data.message || 'Failed to deactivate type', 'error');
          });
        }
      });
    }

    function activateType(id) {
      if (!confirm('Are you sure you want to activate this type?')) return;

      fetch(`/admin/okr-types/${id}/activate`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        }
      })
      .then(response => {
        if (response.ok) {
          showToast('Success', 'Type activated successfully', 'success');
          location.reload();
        } else {
          return response.json().then(data => {
            showToast('Error', data.message || 'Failed to activate type', 'error');
          });
        }
      });
    }

    document.addEventListener('DOMContentLoaded', function() {
      // Display toastr notifications for CRUD operations
      @if (session('success'))
        showToast('Success', '{{ session('success') }}', 'success');
      @endif

      @if (session('error'))
        showToast('Error', '{{ session('error') }}', 'error');
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
