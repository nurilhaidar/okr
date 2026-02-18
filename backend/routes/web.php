<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\OrgUnitController;
use App\Http\Controllers\Admin\OrgUnitTypeController;
use App\Http\Controllers\Admin\OrgUnitRoleController;
use App\Http\Controllers\Admin\OkrTypeController;
use App\Http\Controllers\Admin\OkrController;
use App\Http\Controllers\Admin\CheckInController;
use App\Http\Controllers\EmployeeOkrController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home page - redirect to login or dashboard based on auth status
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->role && $user->role->name === 'Admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {

    // Dashboard (accessible by all)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin-only Routes
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

        // Employees
        Route::get('/employees', [EmployeeController::class, 'index'])->name('admin.employees');
        Route::get('/employees/data', [EmployeeController::class, 'data'])->name('admin.employees.data');
        Route::get('/employees/all', [EmployeeController::class, 'getAll'])->name('admin.employees.all');
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('admin.employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('admin.employees.store');
        Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('admin.employees.edit');
        Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('admin.employees.update');
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('admin.employees.destroy');
        Route::post('/employees/{id}/activate', [EmployeeController::class, 'activate'])->name('admin.employees.activate');
        Route::post('/employees/{id}/deactivate', [EmployeeController::class, 'deactivate'])->name('admin.employees.deactivate');

        // Roles
        Route::get('/roles', [RoleController::class, 'index'])->name('admin.roles');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('admin.roles.create');
        Route::get('/roles/data', [RoleController::class, 'data'])->name('admin.roles.data');
        Route::post('/roles', [RoleController::class, 'store'])->name('admin.roles.store');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->name('admin.roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
        Route::post('/roles/{id}/activate', [RoleController::class, 'activate'])->name('admin.roles.activate');
        Route::post('/roles/{id}/deactivate', [RoleController::class, 'deactivate'])->name('admin.roles.deactivate');

        // Organization Units
        Route::prefix('org-units')->group(function () {
            Route::get('/', [OrgUnitController::class, 'index'])->name('admin.org-units');
            Route::get('/structure', [OrgUnitController::class, 'structure'])->name('admin.org-units.structure');
            Route::get('/data', [OrgUnitController::class, 'data'])->name('admin.org-units.data');
            Route::get('/all', [OrgUnitController::class, 'getAll'])->name('admin.org-units.all');
            Route::get('/create', [OrgUnitController::class, 'create'])->name('admin.org-units.create');
            Route::post('/', [OrgUnitController::class, 'store'])->name('admin.org-units.store');
            Route::get('/{id}/edit', [OrgUnitController::class, 'edit'])->name('admin.org-units.edit');
            Route::put('/{id}', [OrgUnitController::class, 'update'])->name('admin.org-units.update');
            Route::delete('/{id}', [OrgUnitController::class, 'destroy'])->name('admin.org-units.destroy');
            Route::post('/{id}/activate', [OrgUnitController::class, 'activate'])->name('admin.org-units.activate');
            Route::post('/{id}/deactivate', [OrgUnitController::class, 'deactivate'])->name('admin.org-units.deactivate');

            // Employee management in org units
            Route::get('/{id}/employees', [OrgUnitController::class, 'getEmployees'])->name('admin.org-units.employees');
            Route::post('/employees', [OrgUnitController::class, 'addEmployee'])->name('admin.org-units.employees.store');
            Route::put('/employees/{id}', [OrgUnitController::class, 'updateEmployee'])->name('admin.org-units.employees.update');
            Route::delete('/employees/{id}', [OrgUnitController::class, 'removeEmployee'])->name('admin.org-units.employees.destroy');
        });

        // Org Unit Types
        Route::prefix('org-unit-types')->group(function () {
            Route::get('/', [OrgUnitTypeController::class, 'index'])->name('admin.org-unit-types');
            Route::post('/', [OrgUnitTypeController::class, 'store'])->name('admin.org-unit-types.store');
            Route::put('/{id}', [OrgUnitTypeController::class, 'update'])->name('admin.org-unit-types.update');
            Route::delete('/{id}', [OrgUnitTypeController::class, 'destroy'])->name('admin.org-unit-types.destroy');
        });

        // Org Unit Roles
        Route::prefix('org-unit-roles')->group(function () {
            Route::get('/', [OrgUnitRoleController::class, 'index'])->name('admin.org-unit-roles');
            Route::get('/all', [OrgUnitRoleController::class, 'getAll'])->name('admin.org-unit-roles.all');
            Route::post('/', [OrgUnitRoleController::class, 'store'])->name('admin.org-unit-roles.store');
            Route::put('/{id}', [OrgUnitRoleController::class, 'update'])->name('admin.org-unit-roles.update');
            Route::delete('/{id}', [OrgUnitRoleController::class, 'destroy'])->name('admin.org-unit-roles.destroy');
        });

        // OKR Types
        Route::prefix('okr-types')->group(function () {
            Route::get('/', [OkrTypeController::class, 'index'])->name('admin.okr-types');
            Route::get('/create', [OkrTypeController::class, 'create'])->name('admin.okr-types.create');
            Route::get('/all', [OkrTypeController::class, 'getAll'])->name('admin.okr-types.all');
            Route::post('/', [OkrTypeController::class, 'store'])->name('admin.okr-types.store');
            Route::put('/{id}', [OkrTypeController::class, 'update'])->name('admin.okr-types.update');
            Route::delete('/{id}', [OkrTypeController::class, 'destroy'])->name('admin.okr-types.destroy');
            Route::post('/{id}/deactivate', [OkrTypeController::class, 'deactivate'])->name('admin.okr-types.deactivate');
            Route::post('/{id}/activate', [OkrTypeController::class, 'activate'])->name('admin.okr-types.activate');
        });

        // OKRs (Admin only - sees all OKRs)
        Route::prefix('okrs')->group(function () {
            Route::get('/', [OkrController::class, 'index'])->name('admin.okrs');
            Route::get('/create', [OkrController::class, 'create'])->name('admin.okrs.create');
            Route::get('/{id}/edit', [OkrController::class, 'edit'])->name('admin.okrs.edit');
            Route::post('/', [OkrController::class, 'store'])->name('admin.okrs.store');
            Route::put('/{id}', [OkrController::class, 'update'])->name('admin.okrs.update');
            Route::delete('/{id}', [OkrController::class, 'destroy'])->name('admin.okrs.destroy');
            Route::post('/{id}/activate', [OkrController::class, 'activate'])->name('admin.okrs.activate');
            Route::post('/{id}/deactivate', [OkrController::class, 'deactivate'])->name('admin.okrs.deactivate');
            Route::get('/available-owners', [OkrController::class, 'getAvailableOwners'])->name('admin.okrs.owners');
            Route::get('/employees/all', [OkrController::class, 'getAllEmployees'])->name('admin.okrs.employees');
        });
    });

    // Employee OKR Routes (no admin prefix)
    Route::prefix('okrs')->group(function () {
        Route::get('/', [EmployeeOkrController::class, 'index'])->name('okrs.index');
        Route::get('/create', [EmployeeOkrController::class, 'create'])->name('okrs.create');
        Route::get('/{id}/edit', [EmployeeOkrController::class, 'edit'])->name('okrs.edit');
        Route::post('/', [EmployeeOkrController::class, 'store'])->name('okrs.store');
        Route::put('/{id}', [EmployeeOkrController::class, 'update'])->name('okrs.update');
        Route::delete('/{id}', [EmployeeOkrController::class, 'destroy'])->name('okrs.destroy');
        Route::post('/{id}/activate', [EmployeeOkrController::class, 'activate'])->name('okrs.activate');
        Route::post('/{id}/deactivate', [EmployeeOkrController::class, 'deactivate'])->name('okrs.deactivate');
        Route::get('/available-owners', [EmployeeOkrController::class, 'getAvailableOwners'])->name('okrs.owners');
        Route::get('/employees/all', [EmployeeOkrController::class, 'getAllEmployees'])->name('okrs.employees');
    });

    // Check-ins (accessible by all authenticated users)
    Route::prefix('admin/check-ins')->group(function () {
        Route::get('/', [CheckInController::class, 'index'])->name('admin.check-ins.index');
        Route::get('/create', [CheckInController::class, 'create'])->name('admin.check-ins.create');
        Route::get('/pending', [CheckInController::class, 'pendingApprovals'])->name('admin.check-ins.pending');
        Route::get('/objective/{objectiveId}', [CheckInController::class, 'getByObjective'])->name('admin.check-ins.by-objective');
        Route::get('/objective/{objectiveId}/json', [CheckInController::class, 'getByObjectiveJson'])->name('admin.check-ins.by-objective-json');
        Route::post('/', [CheckInController::class, 'store'])->name('admin.check-ins.store');
        Route::get('/{id}', [CheckInController::class, 'show'])->name('admin.check-ins.show');
        Route::get('/{id}/edit', [CheckInController::class, 'edit'])->name('admin.check-ins.edit');
        Route::put('/{id}', [CheckInController::class, 'update'])->name('admin.check-ins.update');
        Route::delete('/{id}', [CheckInController::class, 'destroy'])->name('admin.check-ins.destroy');
        Route::post('/{id}/approve', [CheckInController::class, 'approve'])->name('admin.check-ins.approve');
        Route::post('/{id}/reject', [CheckInController::class, 'reject'])->name('admin.check-ins.reject');
    });

    // User Profile
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
});

// API Routes (continue using existing api.php)
// These routes are handled by api.php and use 'api' middleware
