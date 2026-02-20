@extends('layouts.app')

@section('title', 'Create OKR - OKR Management System')

@section('content')
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.okrs') }}">OKRs</a></li>
                    <li class="breadcrumb-item active">Create New OKR</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-1">Create New OKR</h4>
                        </div>
                        <a href="{{ route('admin.okrs') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-2"></i>Back to OKRs
                        </a>
                    </div>

                    <!-- OKR Form -->
                    @include('admin.okrs.form', [
                        'okrTypes' => $okrTypes,
                        'employees' => $employees,
                        'orgUnits' => $orgUnits,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
