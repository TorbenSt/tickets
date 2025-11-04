<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserTokenController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Token Management (nur für Developer)
Route::middleware(['auth:sanctum', 'role:developer'])->group(function () {
    Route::post('/admin/users/{user}/generate-token', [UserTokenController::class, 'generate']);
    Route::delete('/admin/users/{user}/revoke-token', [UserTokenController::class, 'revoke']);
    Route::get('/admin/users/{user}/token', [UserTokenController::class, 'show']);
    Route::get('/admin/iframe-tokens', [UserTokenController::class, 'list']);
    
    // API für Project User Management
    Route::get('/projects/{project}/available-users', [\App\Http\Controllers\ProjectController::class, 'availableUsers']);
});