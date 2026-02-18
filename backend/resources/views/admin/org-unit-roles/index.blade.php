@extends('layouts.app')

@section('title', 'Organization Unit Roles - OKR Management System')

@section('content')
  <!-- Breadcrumb -->
  <div class="row mb-3">
    <div class="col-12">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Organization Unit Roles</li>
        </ol>
      </nav>
    </div>
  </div>

  <div class="row">
    <div class="col-12 col-lg-12 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4 class="mb-1">Organization Unit Roles</h4>
              <p class="text-muted mb-0">Manage roles for organization unit members (e.g., Manager, Lead, Member).</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
              <i class="ti ti-plus me-2"></i>Add Role
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Roles Table -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover dt-responsive" id="orgUnitRolesTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Exclusive</th>
                  <th>Assigned Members</th>
                  <th>Created At</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($orgUnitRoles as $role)
                  <tr data-id="{{ $role->id }}" data-name="{{ $role->name }}" data-is-exclusive="{{ $role->is_exclusive ? 'true' : 'false' }}" data-members-count="{{ $role->org_unit_employees_count }}">
                    <td>{{ $role->id }}</td>
                    <td>
                      <span class="badge bg-label-primary">{{ $role->name }}</span>
                    </td>
                    <td>
                      @if($role->is_exclusive)
                        <span class="badge bg-label-warning">Yes</span>
                      @else
                        <span class="badge bg-label-secondary">No</span>
                      @endif
                    </td>
                    <td>{{ $role->org_unit_employees_count }}</td>
                    <td>{{ \Carbon\Carbon::parse($role->created_at)->format('M d, Y') }}</td>
                    <td>
                      <button class="btn btn-sm btn-icon btn-outline-primary btn-edit" title="Edit">
                        <i class="ti ti-pencil"></i>
                      </button>
                      <button class="btn btn-sm btn-icon btn-outline-danger btn-delete ms-1" title="Delete">
                        <i class="ti ti-trash"></i>
                      </button>
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

  <!-- Add Role Modal -->
  <div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Unit Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('admin.org-unit-roles.store') }}">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Role Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Manager, Lead, Member" required>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-3">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_exclusive" id="isExclusive">
                <label class="form-check-label" for="isExclusive">
                  <strong>Exclusive Role</strong>
                  <small class="text-muted d-block">Only one person can hold this role per unit</small>
                </label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-check me-2"></i>Save Role
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Role Modal -->
  <div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Unit Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('admin.org-unit-roles.update', 0) }}" id="editRoleForm">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <input type="hidden" name="id" id="editRoleId">
            <div class="mb-3">
              <label class="form-label">Role Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="editRoleName" class="form-control" placeholder="e.g., Manager, Lead, Member" required>
            </div>
            <div class="mb-3">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_exclusive" id="editIsExclusive">
                <label class="form-check-label" for="editIsExclusive">
                  <strong>Exclusive Role</strong>
                  <small class="text-muted d-block">Only one person can hold this role per unit</small>
                </label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-check me-2"></i>Update Role
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('page_scripts')
  <!-- Vendors JS -->
  <script src="{{ asset('plugin/vuexy/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
  <script src="{{ asset('plugin/vuexy/assets/js/tables-datatables-advanced.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize DataTable
      const orgUnitRolesTable = $('#orgUnitRolesTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [
          [10, 25, 50, -1],
          [10, 25, 50, 'All']
        ],
        language: {
          search: '_INPUT_',
          searchPlaceholder: 'Search roles...',
          paginate: {
            next: '<i class="ti ti-chevron-right"></i>',
            previous: '<i class="ti ti-chevron-left"></i>'
          }
        },
        columnDefs: [{
          orderable: false,
          targets: [5]
        }],
        order: [
          [0, 'desc']
        ]
      });

      // Edit button click handler
      $(document).on('click', '.btn-edit', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const name = row.data('name');
        const isExclusive = row.data('is-exclusive') === 'true';

        document.getElementById('editRoleId').value = id;
        document.getElementById('editRoleName').value = name;
        document.getElementById('editIsExclusive').checked = isExclusive;
        document.getElementById('editRoleForm').action = '/admin/org-unit-roles/' + id;

        const modal = new bootstrap.Modal(document.getElementById('editRoleModal'));
        modal.show();
      });

      // Delete button click handler
      $(document).on('click', '.btn-delete', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const name = row.data('name');
        const membersCount = row.data('members-count');

        if (membersCount > 0) {
          showToast('Error', 'Cannot delete role. It has ' + membersCount + ' member(s) assigned.', 'error');
          return;
        }

        if (confirm(`Are you sure you want to delete "${name}"?`)) {
          // Create and submit form dynamically
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = '/admin/org-unit-roles/' + id;

          const csrfInput = document.createElement('input');
          csrfInput.type = 'hidden';
          csrfInput.name = '_token';
          csrfInput.value = '{{ csrf_token() }}';

          const methodInput = document.createElement('input');
          methodInput.type = 'hidden';
          methodInput.name = '_method';
          methodInput.value = 'DELETE';

          form.appendChild(csrfInput);
          form.appendChild(methodInput);
          document.body.appendChild(form);
          form.submit();
        }
      });

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
