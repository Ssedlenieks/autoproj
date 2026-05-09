<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || $request->user()->role_id !== 2) {
            return response()->json(['message' => 'Forbidden', 'success' => false], 403);
        }

        return $next($request);
    }
}
