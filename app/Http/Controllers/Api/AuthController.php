<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'username' => 'required|string|unique:admins,username|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = Admin::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        $token = $admin->createToken('auth_token')->plainTextToken;

        ActivityLog::record('created', 'Admin', $admin->id);

        return response()->json([
            'message' => 'ثبت‌نام موفق بود',
            'admin' => $admin,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('username', $request->username)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $admin->createToken('auth_token')->plainTextToken;

        ActivityLog::recordLogin($admin->id);

        return response()->json([
            'message' => 'Login successful',
            'admin' => $admin,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
//        Log::info('Logout attempt with token: ' . $request->bearerToken());

        if (!$request->user()) {
//            Log::info('No authenticated user found');
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->user()->currentAccessToken()->delete();
        ActivityLog::recordLogout($request->user()->id);
        return response()->json(['message' => 'Logged out successfully']);
    }
}
