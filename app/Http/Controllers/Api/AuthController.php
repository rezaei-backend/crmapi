<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * ثبت‌نام ادمین
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'fname'     => 'required|string|max:255',
            'lname'     => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:admins,username',
            'password'  => 'required|string|min:6|confirmed',
        ]);

        $admin = Admin::create([
            'fname'        => $data['fname'],
            'lname'        => $data['lname'],
            'username'     => $data['username'],
            'password'     => Hash::make($data['password']),
            'enabled'      => true,
        ]);

        $token = $admin->createToken('auth_token')->plainTextToken;

        // ثبت لاگ ثبت‌نام
        ActivityLog::record('created', 'Admin', $admin->id);

        return response()->json([
            'message' => 'ثبت‌نام موفق بود',
            'admin'   => $admin,
            'token'   => $token,
        ], 201);
    }

    /**
     * ورود ادمین با پسورد جدید و قدیم
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        \Illuminate\Support\Facades\Log::info('Login attempt:', ['username' => $data['username']]);

        // پیدا کردن کاربر
        $admin = Admin::where('username', $data['username'])->first();

        if (!$admin) {
            \Illuminate\Support\Facades\Log::warning('Login failed: User not found', ['username' => $data['username']]);
            return response()->json(['message' => 'نام کاربری یا رمز عبور اشتباه است.'], 401);
        }

        // بررسی رمز عبور جدید
        if (Hash::check($data['password'], $admin->password)) {
            $token = $admin->createToken('auth_token')->plainTextToken;

            \Illuminate\Support\Facades\Log::info('Login successful:', ['admin_id' => $admin->id]);
            ActivityLog::record('login', 'Admin', $admin->id); // اصلاح فراخوانی متد

            return response()->json([
                'message' => 'ورود موفق بود',
                'admin' => $admin,
                'token' => $token,
            ]);
        }

        // بررسی رمز عبور قدیمی
        if ($admin->old_password && Hash::check($data['password'], $admin->old_password)) {
            $token = $admin->createToken('auth_token')->plainTextToken;

            \Illuminate\Support\Facades\Log::info('Login successful with old password:', ['admin_id' => $admin->id]);
            ActivityLog::record('login', 'Admin', $admin->id); // اصلاح فراخوانی متد

            return response()->json([
                'message' => 'ورود موفق بود (با رمز قدیمی)',
                'admin' => $admin,
                'token' => $token,
            ]);
        }

        \Illuminate\Support\Facades\Log::warning('Login failed: Invalid password', ['username' => $data['username']]);
        return response()->json(['message' => 'نام کاربری یا رمز عبور اشتباه است.'], 401);
    }

    /**
     * خروج ادمین
     */
    public function logout(Request $request)
    {
        $admin = $request->user('admin');

        if ($admin) {
            \Illuminate\Support\Facades\Log::info('Logout successful:', ['admin_id' => $admin->id]);
            ActivityLog::record('logout', 'Admin', $admin->id); // اصلاح فراخوانی متد
            $admin->currentAccessToken()->delete();
            return response()->json(['message' => 'خروج موفق بود']);
        }

        \Illuminate\Support\Facades\Log::warning('Logout failed: User not found');
        return response()->json(['message' => 'کاربر یافت نشد'], 401);
    }
}
