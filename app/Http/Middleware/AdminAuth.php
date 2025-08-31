<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('api')->check() || !Auth::guard('api')->user() instanceof Admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
