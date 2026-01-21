<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ObjectiveController;
use App\Http\Controllers\Api\OkrController;
use App\Http\Controllers\Api\OkrTypeController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\OrgUnitController;
use App\Http\Controllers\Api\OrgUnitTypeController;
use App\Http\Controllers\Api\OrgUnitRoleController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Employee Routes
    Route::apiResource('employees', EmployeeController::class);
    Route::patch('/employees/{id}/deactivate', [EmployeeController::class, 'deactivate']);
    Route::patch('/employees/{id}/activate', [EmployeeController::class, 'activate']);

    // Role Routes
    Route::apiResource('roles', RoleController::class);

    // OrgUnit Routes
    Route::apiResource('orgunits', OrgUnitController::class);
    Route::get('/orgunits/datatables', [OrgUnitController::class, 'datatables']);
    Route::patch('/orgunits/{id}/deactivate', [OrgUnitController::class, 'deactivate']);
    Route::patch('/orgunits/{id}/activate', [OrgUnitController::class, 'activate']);

    // OrgUnitType Routes
    Route::apiResource('orgunit-types', OrgUnitTypeController::class);
    Route::get('/orgunit-types/datatables', [OrgUnitTypeController::class, 'datatables']);

    // OrgUnitRole Routes
    Route::apiResource('orgunit-roles', OrgUnitRoleController::class);
    Route::get('/orgunit-roles/datatables', [OrgUnitRoleController::class, 'datatables']);

    // OKR Type Routes
    Route::apiResource('okr-types', OkrTypeController::class);

    // OKR Routes
    Route::get('/okrs/available-owners', [OkrController::class, 'getAvailableOwners']);
    Route::apiResource('okrs', OkrController::class);
    Route::patch('/okrs/{id}/activate', [OkrController::class, 'activate']);
    Route::patch('/okrs/{id}/deactivate', [OkrController::class, 'deactivate']);

    // Objective Routes
    Route::apiResource('objectives', ObjectiveController::class);
    Route::get('/objectives/by-okr/{okrId}', [ObjectiveController::class, 'getByOkr']);
    Route::get('/objectives/by-tracker/{trackerId}', [ObjectiveController::class, 'getByTracker']);
    Route::get('/objectives/by-approver/{approverId}', [ObjectiveController::class, 'getByApprover']);

    // CheckIn Routes
    Route::get('/check-ins/by-objective/{objectiveId}', [CheckInController::class, 'getByObjective']);
    Route::get('/check-ins/by-tracker/{trackerId}', [CheckInController::class, 'getByTracker']);
    Route::get('/check-ins/pending-approvals', [CheckInController::class, 'getPendingApprovals']);
    Route::get('/check-ins/{id}/approval-logs', [CheckInController::class, 'getApprovalLogs']);
    Route::apiResource('check-ins', CheckInController::class);
    Route::post('/check-ins/{id}/approve', [CheckInController::class, 'approve']);
    Route::post('/check-ins/{id}/reject', [CheckInController::class, 'reject']);
});
