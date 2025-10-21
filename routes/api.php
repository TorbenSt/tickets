<?php

use App\Http\Controllers\IframeController;
use Illuminate\Support\Facades\Route;

// iframe Integration API
Route::post('/iframe/token', [IframeController::class, 'generateToken']);