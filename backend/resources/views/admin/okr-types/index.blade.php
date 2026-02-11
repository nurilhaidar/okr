@extends('layouts.app')

@section('title', 'OKR Types - OKR Management System')

@section('content')
  <div class="row">
    <div class="col-12 col-lg-12 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4 class="mb-1">OKR Types</h4>
              <p class="text-muted mb-0">Manage OKR types for individual employees and organization units.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTypeModal">
              <i class="ti ti-plus me-2"></i>Add Type
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
                  <th>ID</th>
                  <th>Name</th>
                  <th>Owner Type</th>
                  <th>Used In OKRs</th>
                  <th>Created At</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($okrTypes as $type)
                  <tr>
                    <td>{{ $type->id }}</td>
                    <td>
                      <span class="badge bg-label-primary">{{ $type->name }}</span>
                    </td>
                    <td>
                      @if($type->is_employee)
                        <span class="badge bg-label-success">
                          <i class="ti ti-user me-1"></i>Employee
                        </span>
                      @else
                        <span class="badge bg-label-info">
                          <i class="ti ti-building me-1"></i>Organization
                        </span>
                      @endif
                    </td>
                    <td>{{ $type->okrs_count }}</td>
                    <td>{{ \Carbon\Carbon::parse($type->created_at)->format('M d, Y') }}</td>
                    <td>
                      <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editType({{ $type->id }}, '{{ $type->name }}', {{ $type->is_employee ? 'true' : 'false' }})">
                          <i class="ti ti-pencil"></i>
                        </button>
                        @if($type->okrs_count == 0)
                          <form method="POST" action="{{ route('admin.okr-types.destroy', $type->id) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this OKR type?')">
                              <i class="ti ti-trash"></i>
                            </button>
                          </form>
                        @else
                          <button type="button" class="btn btn-sm btn-outline-danger" disabled title="Cannot delete type with associated OKRs">
                            <i class="ti ti-trash"></i>
                          </button>
                        @endif
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
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Individual, Team, Department" required>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Owner Type <span class="text-danger">*</span></label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="is_employee" id="isEmployee" value="1" checked>
                <label class="form-check-label" for="isEmployee">
                  <strong>Employee</strong>
                  <small class="text-muted d-block">For individual employee OKRs</small>
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="is_employee" id="isOrgUnit" value="0">
                <label class="form-check-label" for="isOrgUnit">
                  <strong>Organization Unit</strong>
                  <small class="text-muted d-block">For team/department/company OKRs</small>
                </label>
              </div>
              @error('is_employee')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
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
              <input type="text" name="name" id="editTypeName" class="form-control" placeholder="e.g., Individual, Team, Department" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Owner Type <span class="text-danger">*</span></label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="is_employee" id="editIsEmployee" value="1">
                <label class="form-check-label" for="editIsEmployee">
                  <strong>Employee</strong>
                  <small class="text-muted d-block">For individual employee OKRs</small>
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="is_employee" id="editIsOrgUnit" value="0">
                <label class="form-check-label" for="editIsOrgUnit">
                  <strong>Organization Unit</strong>
                  <small class="text-muted d-block">For team/department/company OKRs</small>
                </label>
              </div>
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
    function editType(id, name, isEmployee) {
      document.getElementById('editTypeId').value = id;
      document.getElementById('editTypeName').value = name;
      document.getElementById('editIsEmployee').checked = isEmployee;
      document.getElementById('editIsOrgUnit').checked = !isEmployee;
      document.getElementById('editTypeForm').action = '/admin/okr-types/' + id;

      const modal = new bootstrap.Modal(document.getElementById('editTypeModal'));
      modal.show();
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
