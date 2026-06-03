<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EnvironmentController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\VariableController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // CLI tokens
    Route::get('tokens', [TokenController::class, 'index']);
    Route::post('tokens', [TokenController::class, 'store']);
    Route::delete('tokens/{tokenId}', [TokenController::class, 'destroy']);

    // Projects
    Route::apiResource('projects', ProjectController::class);

    Route::prefix('projects/{project}')->group(function () {
        // Members
        Route::get('members', [MemberController::class, 'index']);
        Route::post('members', [MemberController::class, 'store']);
        Route::delete('members/{user}', [MemberController::class, 'destroy']);

        // Activity log
        Route::get('activity', [ActivityLogController::class, 'index']);

        // Environments
        Route::apiResource('environments', EnvironmentController::class)
            ->only(['index', 'store', 'destroy']);

        // Compare environments
        Route::get('compare', [EnvironmentController::class, 'compare']);

        // Export + Variables
        Route::get('environments/{environment}/export', [EnvironmentController::class, 'export']);
        Route::apiResource('environments/{environment}/variables', VariableController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });
});
