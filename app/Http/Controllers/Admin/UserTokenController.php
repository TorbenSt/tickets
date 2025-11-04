<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserTokenController extends Controller
{


    public function generate(User $user)
    {
        $token = $user->generateIframeUserToken();
        
        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'iframe_user_token' => $token,
            'created_at' => $user->iframe_token_created_at->toISOString(),
            'message' => 'Token generated successfully'
        ]);
    }

    public function revoke(User $user)
    {
        $user->revokeIframeToken();
        
        return response()->json([
            'message' => 'Token revoked successfully'
        ]);
    }

    public function show(User $user)
    {
        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'has_token' => !is_null($user->iframe_user_token),
            'token_created' => $user->iframe_token_created_at?->toISOString(),
            'last_used' => $user->iframe_token_last_used?->toISOString(),
            'token_preview' => $user->iframe_user_token ? 
                substr($user->iframe_user_token, 0, 8) . '...' . substr($user->iframe_user_token, -8) : null
        ]);
    }

    public function list()
    {
        $users = User::with('firma')
            ->whereNotNull('iframe_user_token')
            ->orderBy('iframe_token_last_used', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'firma' => $user->firma?->name,
                    'token_preview' => substr($user->iframe_user_token, 0, 8) . '...' . substr($user->iframe_user_token, -8),
                    'created_at' => $user->iframe_token_created_at?->toISOString(),
                    'last_used' => $user->iframe_token_last_used?->toISOString()
                ];
            });

        return response()->json($users);
    }
}