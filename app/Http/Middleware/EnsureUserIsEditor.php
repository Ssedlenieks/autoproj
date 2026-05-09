<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsEditor
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user || !in_array($user->role->name, ['Editor', 'Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Editor access required.'
            ], 403);
        }

        return $next($request);
    }
}