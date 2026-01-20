<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
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
});
