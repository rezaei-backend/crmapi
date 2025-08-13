<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * ثبت ورود ادمین
     */
//    public function recordLogin()
//    {
//        $log = ActivityLog::create([
//            'action' => 'login',
//            'model_type' => 'User',
//            'model_id' => Auth::id(),
//            'description' => 'ادمین وارد شد',
//            'user_id' => Auth::id(),
//            'login_at' => now(),
//        ]);
//
//        return response()->json([
//            'message' => 'ورود ثبت شد',
//            'log_id' => $log->id
//        ]);
//    }

    /**
     * ثبت خروج ادمین
     */
//    public function recordLogout()
//    {
//        // پیدا کردن آخرین لاگ ورود کاربر
//        $log = ActivityLog::where('user_id', Auth::id())
//            ->where('action', 'login')
//            ->latest()
//            ->first();
//
//        if ($log) {
//            $log->update([
//                'action' => 'logout',
//                'description' => 'ادمین خارج شد',
//                'logout_at' => now(),
//            ]);
//        }
//
//        return response()->json([
//            'message' => 'خروج ثبت شد'
//        ]);
//    }

    public function destroy(Request $request)
    {
        // حذف تمام رکوردهای ActivityLog از دیتابیس
        ActivityLog::truncate();

        // بازگشت پاسخ با پیام موفقیت
        return back()->with('success', 'تمامی لاگ‌ها با موفقیت حذف شدند.');
    }

//    ActivityLog::record('ایجاد', 'مقالات', $article->id, 'Created a new article titled: ' . $article->title
//    ActivityLog::record('login', 'User', auth()->id(), 'کاربر وارد سیستم شد');
//    ActivityLog::record('logout', 'User', auth()->id(), 'کاربر از سیستم خارج شد');
}
