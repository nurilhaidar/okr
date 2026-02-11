@extends('layouts.app')

@section('title', 'Organization Unit Types - OKR Management System')

@section('content')
  <div class="row">
    <div class="col-12 col-lg-12 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4 class="mb-1">Organization Unit Types</h4>
              <p class="text-muted mb-0">Manage types for organization units (e.g., Department, Division, Team).</p>
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
                  <th>Units Count</th>
                  <th>Created At</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($orgUnitTypes as $type)
                  <tr>
                    <td>{{ $type->id }}</td>
                    <td>
                      <span class="badge bg-label-info">{{ $type->name }}</span>
                    </td>
                    <td>{{ $type->org_units_count }}</td>
                    <td>{{ \Carbon\Carbon::parse($type->created_at)->format('M d, Y') }}</td>
                    <td>
                      <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-primary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                          <i class="ti ti-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          <li>
                            <button class="dropdown-item" onclick="editType({{ $type->id }}, '{{ $type->name }}')">
                              <i class="ti ti-pencil me-2"></i>Edit
                            </button>
                          </li>
                          @if($type->org_units_count == 0)
                            <li>
                              <div class="dropdown-divider"></div>
                            </li>
                            <li>
                              <form method="POST" action="{{ route('admin.org-unit-types.destroy', $type->id) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this type?')">
                                  <i class="ti ti-trash me-2"></i>Delete
                                </button>
                              </form>
                            </li>
                          @endif
                        </ul>
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
          <h5 class="modal-title">Add New Unit Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('admin.org-unit-types.store') }}">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Type Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Department, Division, Team" required>
              @error('name')
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
          <h5 class="modal-title">Edit Unit Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('admin.org-unit-types.update', 0) }}" id="editTypeForm">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <input type="hidden" name="id" id="editTypeId">
            <div class="mb-3">
              <label class="form-label">Type Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="editTypeName" class="form-control" placeholder="e.g., Department, Division, Team" required>
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
    function editType(id, name) {
      document.getElementById('editTypeId').value = id;
      document.getElementById('editTypeName').value = name;
      document.getElementById('editTypeForm').action = '/admin/org-unit-types/' + id;

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
