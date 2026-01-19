<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RankController;
use App\Http\Controllers\Api\PositionController;
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

    // Rank Routes
    Route::apiResource('ranks', RankController::class);

    // Position Routes
    Route::apiResource('positions', PositionController::class);
});
