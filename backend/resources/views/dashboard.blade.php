@extends('layouts.app')

@section('title', 'Dashboard - OKR Management System')

@section('sidebar')
  <!-- Menu -->
  <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
      <a href="{{ route('dashboard') }}" class="app-brand-link">
        <span class="app-brand-logo demo">
          <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="#7367F0" />
            <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616" />
            <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616" />
            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z" fill="#7367F0" />
          </svg>
        </span>
        <span class="app-brand-text demo menu-text fw-bold">OKR System</span>
      </a>

      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
        <i class="ti ti-x d-block ti-sm align-middle"></i>
      </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
      <!-- Dashboard -->
      <li class="menu-item active">
        <a href="{{ route('dashboard') }}" class="menu-link">
          <i class="menu-icon tf-icons ti ti-smart-home"></i>
          <div data-i18n="Dashboard">Dashboard</div>
        </a>
      </li>

      <!-- My OKRs -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="menu-icon tf-icons ti ti-target"></i>
          <div data-i18n="My OKRs">My OKRs</div>
        </a>
      </li>

      <!-- Objectives to Track -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="menu-icon tf-icons ti ti-chart-line"></i>
          <div data-i18n="Objectives to Track">Objectives to Track</div>
          @if($objectivesToTrack->count() > 0)
            <div class="badge bg-primary rounded-pill ms-auto">{{ $objectivesToTrack->count() }}</div>
          @endif
        </a>
      </li>

      <!-- Pending Approvals -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="menu-icon tf-icons ti ti-checklist"></i>
          <div data-i18n="Pending Approvals">Pending Approvals</div>
          @if($pendingApprovals->count() > 0)
            <div class="badge bg-danger rounded-pill ms-auto">{{ $pendingApprovals->count() }}</div>
          @endif
        </a>
      </li>

      <!-- Check-ins -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="menu-icon tf-icons ti ti-clipboard-check"></i>
          <div data-i18n="Check-ins">Check-ins</div>
        </a>
      </li>
    </ul>
  </aside>
  <!-- / Menu -->
@endsection

@section('navbar')
  <!-- Navbar -->
  <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
      <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0);">
        <i class="ti ti-menu-2 ti-md"></i>
      </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
      <!-- User -->
      <ul class="navbar-nav flex-row align-items-center ms-auto">
        <li class="nav-item navbar-dropdown dropdown-user">
          <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <div class="avatar avatar-online">
              <img src="/vuexy/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="javascript:void(0);">
                <div class="d-flex">
                  <div class="flex-shrink-0 me-3">
                    <div class="avatar avatar-online">
                      <img src="/vuexy/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                  </div>
                  <div>
                    <h6 class="mb-0">{{ $user->name }}</h6>
                    <small class="text-muted">{{ $user->role->name ?? 'No Role' }}</small>
                  </div>
                </div>
              </a>
            </li>
            <li>
              <div class="dropdown-divider"></div>
            </li>
            <li>
              <a class="dropdown-item" href="#">
                <i class="ti ti-user me-2"></i>
                <span class="align-middle">My Profile</span>
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
  <!-- / Navbar -->
@endsection

@section('content')
  <div class="row">
    <div class="col-12 col-lg-12 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4 class="mb-1">Welcome back, {{ explode(' ', $user->name)[0] }}! 👋</h4>
              <p class="text-muted">Here's what's happening with your OKRs today.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="row">
    <div class="col-12 col-sm-6 col-xl-3 mb-4">
      <div class="card stat-card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar bg-label-primary rounded-3 me-3">
              <i class="ti ti-target fs-4"></i>
            </div>
            <div>
              <h6 class="mb-0">Total OKRs</h6>
              <h3 class="mb-0">{{ $myOkrs->count() }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 mb-4">
      <div class="card stat-card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar bg-label-success rounded-3 me-3">
              <i class="ti ti-checkbox fs-4"></i>
            </div>
            <div>
              <h6 class="mb-0">Active OKRs</h6>
              <h3 class="mb-0">{{ $activeOkrs->count() }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 mb-4">
      <div class="card stat-card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar bg-label-info rounded-3 me-3">
              <i class="ti ti-bullseye fs-4"></i>
            </div>
            <div>
              <h6 class="mb-0">Objectives to Track</h6>
              <h3 class="mb-0">{{ $objectivesToTrack->count() }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 mb-4">
      <div class="card stat-card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar bg-label-warning rounded-3 me-3">
              <i class="ti ti-alert-circle fs-4"></i>
            </div>
            <div>
              <h6 class="mb-0">Pending Approvals</h6>
              <h3 class="mb-0">{{ $pendingApprovals->count() }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- My Active OKRs Section -->
  <div class="row">
    <div class="col-12 mb-4">
      <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">My Active OKRs</h5>
            <a href="#" class="btn btn-sm btn-primary">View All</a>
          </div>
        </div>
        <div class="card-body">
          @if($activeOkrs->count() > 0)
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>OKR Name</th>
                    <th>Type</th>
                    <th>Weight</th>
                    <th>Period</th>
                    <th>Progress</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($activeOkrs->take(5) as $okr)
                    @php
                      $progress = $okr->progress ?? 0;
                      $progressClass = $progress >= 80 ? 'bg-success' : ($progress >= 50 ? 'bg-warning' : 'bg-danger');
                    @endphp
                    <tr>
                      <td>
                        <strong>{{ $okr->name }}</strong>
                      </td>
                      <td><span class="badge bg-label-primary">{{ $okr->okrType->name ?? 'N/A' }}</span></td>
                      <td>{{ $okr->weight ?? 0 }}%</td>
                      <td>{{ \Carbon\Carbon::parse($okr->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($okr->end_date)->format('M d, Y') }}</td>
                      <td>
                        <div class="okr-progress">
                          <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $progress }}%"></div>
                        </div>
                        <small class="text-muted">{{ number_format($progress, 1) }}%</small>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center py-4">
              <p class="text-muted">No active OKRs found.</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page_scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
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
