@extends('layouts.app')

@section('title', 'Edit Organization Unit - OKR Management System')

@section('content')
  <div class="row justify-content-center">
    <div class="col-12 col-lg-8">
      <div class="card">
        <div class="card-header">
          <h4 class="mb-0">Edit Organization Unit</h4>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('admin.org-units.update', $orgUnit->id) }}">
            @method('PUT')
            @csrf

            <h5 class="mb-3">Basic Information</h5>

            <div class="row g-3 mb-4">
              <div class="col-md-12">
                <label class="form-label">Unit Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $orgUnit->name) }}" placeholder="e.g., Engineering Department" required>
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Parent Unit</label>
                <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                  <option value="">No Parent (Root Level)</option>
                  @foreach($parentUnits as $unit)
                    <option value="{{ $unit->id }}" {{ old('parent_id', $orgUnit->parent_id) == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                  @endforeach
                </select>
                @error('parent_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Optional: Create hierarchy by selecting parent</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="is_active" id="isActive" class="form-select">
                  <option value="1" {{ old('is_active', $orgUnit->is_active) ? 'selected' : '' }}>Active</option>
                  <option value="0" {{ !old('is_active', $orgUnit->is_active) ? 'selected' : '' }}>Inactive</option>
                </select>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('admin.org-units') }}" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">
                <i class="ti ti-check me-2"></i>Update Unit
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
      // Handle form submission
      document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const isActiveSelect = document.getElementById('isActive');
        const data = {
          name: formData.get('name'),
          parent_id: formData.get('parent_id') || null,
          is_active: isActiveSelect.value === '1'
        };

        fetch('{{ route('admin.org-units.update', $orgUnit->id) }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            localStorage.setItem('toast_success', 'Org Unit updated successfully!');
            window.location.href = '{{ route('admin.org-units') }}';
          } else if (data.errors) {
            Object.values(data.errors).flat().forEach(msg => {
              showToast('Validation Error', msg, 'error');
            });
          } else {
            showToast('Error', data.message || 'An error occurred', 'error');
          }
        })
        .catch(err => {
          console.error(err);
          showToast('Error', 'An error occurred while updating the org unit.', 'error');
        });
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
