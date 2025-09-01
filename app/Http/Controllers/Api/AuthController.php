<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    private function hashPassword($password, $salt = 'jit@2024#')
    {
        $modifiedPassword = $password . substr($password, 0, 4);
        $passwordWithSalt = $modifiedPassword . $salt;
        return hash('sha256', $passwordWithSalt);
    }

    public function register(Request $request): JsonResponse
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
            'enabled' => true,
            // ذخیره رمز عبور قدیم - فعلا کامنت شده
            // 'old_password' => $this->hashPassword($request->password),
        ]);

        $token = $admin->createToken('auth_token')->plainTextToken;

        ActivityLog::record('created', 'Admin', $admin->id);

        return response()->json([
            'message' => 'ثبت‌نام موفق بود',
            'admin' => $admin,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('username', $request->username)->first();

        // بررسی فعال بودن کاربر
        if (!$admin || !$admin->enabled) {
            Log::info("Login failed: User not found or disabled, Username: {$request->username}");
            throw ValidationException::withMessages([
                'username' => ['نام کاربری یا رمز عبور نادرست است یا حساب غیرفعال است.'],
            ]);
        }

        // بررسی رمز عبور جدید و رمز عبور قدیمی
        $isNewPasswordValid = Hash::check($request->password, $admin->password);
        $isOldPasswordValid = $admin->old_password && $this->hashPassword($request->password) === $admin->old_password;

//        Log::info("Login attempt: Username: {$admin->username}, New Password Valid: " . ($isNewPasswordValid ? 'true' : 'false') . ", Old Password Valid: " . ($isOldPasswordValid ? 'true' : 'false'));

        if (!$isNewPasswordValid && !$isOldPasswordValid) {
            Log::info("Login failed: Invalid credentials, Username: {$admin->username}");
            throw ValidationException::withMessages([
                'username' => ['نام کاربری یا رمز عبور نادرست است.'],
            ]);
        }

        // محدود بودن لاگین با ID - فعلا کامنت شده
//        if (!in_array($admin->id, [1, 18])) {
//            Log::info("General Error: حساب کاربری غیر مجاز است., Username: {$admin->username}, ID: {$admin->id}, Time: " . now());
//            throw ValidationException::withMessages([
//                'username' => ['نام کاربری یا رمز عبور نادرست است.'],
//            ]);
//        }

//        Log::info("Input Username: {$admin->username}, Login Time: " . now());

        $token = $admin->createToken('auth_token')->plainTextToken;

        ActivityLog::recordLogin($admin->id);

        return response()->json([
            'message' => 'ورود موفق بود',
            'admin' => $admin,
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
//        Log::info('Logout attempt with token: ' . $request->bearerToken());

        if (!$request->user()) {
            Log::info('No authenticated user found');
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->user()->currentAccessToken()->delete();
        ActivityLog::recordLogout($request->user()->id);
        return response()->json(['message' => 'Logged out successfully']);
    }
}
