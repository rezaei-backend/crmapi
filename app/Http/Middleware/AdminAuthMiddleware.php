<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // بررسی با نگهبان admin
        if (!Auth::guard('admin')->check() || !$request->user('admin')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // بررسی اینکه کاربر از نوع Admin است
        if (!$request->user('admin') instanceof \App\Models\Admin) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return $next($request);
    }
}
