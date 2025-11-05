<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;
    /**
     * Show iframe token information for a user (Developer only).
     */
    public function showToken(User $user): View
    {
        // Only developers can access this (middleware handles this)
        // but let's add extra check
        if (!\Illuminate\Support\Facades\Auth::user()->role->isDeveloper()) {
            abort(403);
        }
        
        return view('admin.users.token', compact('user'));
    }
}