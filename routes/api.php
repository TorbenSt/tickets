<?php

use App\Http\Controllers\Api\IframeController;
use App\Http\Controllers\Admin\UserTokenController;
use Illuminate\Support\Facades\Route;

// iFrame Authentication (public API mit API Key)
Route::post('/iframe/authenticate', [IframeController::class, 'authenticate']);

// Token Management (Developer only)
Route::middleware(['auth:sanctum', 'role:developer'])->group(function () {
    Route::post('/admin/users/{user}/generate-token', [UserTokenController::class, 'generate']);
    Route::delete('/admin/users/{user}/revoke-token', [UserTokenController::class, 'revoke']);
    Route::get('/admin/users/{user}/token', [UserTokenController::class, 'show']);
    Route::get('/admin/iframe-tokens', [UserTokenController::class, 'list']);
});