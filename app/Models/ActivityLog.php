<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Carbon\Carbon;

class ActivityLog extends Model
{
    protected $fillable = [
        'action',
        'model_type',
        'model_id',
        'description',
        'user_id',
        'login_at',
        'logout_at',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    /**
     * ارتباط با مدل User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ثبت لاگ عمومی برای عملیات CRUD
     */
    public static function record($action, $model, $modelId)
    {
        $modelType = class_basename($model);

        $actionText = match ($action) {
            'created' => 'ایجاد شد',
            'updated' => 'ویرایش شد',
            'deleted' => 'حذف شد',
            default => $action
        };

        $message = "رکوردی از مدل {$modelType} {$actionText} شد.";

        $log = self::create([
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $message,
            'user_id' => Auth::id(),
        ]);

        self::writeToFile($log, $message);

        return $log;
    }

    /**
     * ثبت لاگ زمان ورود ادمین
     */
    public static function recordLogin()
    {
        $log = self::create([
            'action' => 'login',
            'model_type' => 'User',
            'model_id' => Auth::id(),
            'description' => 'ورود ادمین',
            'user_id' => Auth::id(),
            'login_at' => Carbon::now(),
        ]);

        self::writeToFile($log, 'ورود ادمین');
    }

    /**
     * ثبت لاگ زمان خروج ادمین
     */
    public static function recordLogout()
    {
        $lastLog = self::where('user_id', Auth::id())
            ->whereNull('logout_at')
            ->latest()
            ->first();

        if ($lastLog) {
            $lastLog->update([
                'logout_at' => Carbon::now(),
                'action' => 'logout',
                'description' => 'خروج ادمین',
            ]);

            self::writeToFile($lastLog, 'خروج ادمین');
        }
    }

    /**
     * نوشتن لاگ در فایل logs.txt
     */
    protected static function writeToFile($log, $message)
    {
        $logEntry = sprintf(
            "[%s] User: %s, Action: %s, Model: %s, Model ID: %s, Description: %s\n",
            now()->timezone('Asia/Tehran')->format('Y/m/d H:i:s'),
            Auth::user() ? Auth::user()->name : 'Unknown',
            $log->action,
            $log->model_type,
            $log->model_id,
            $message
        );

        try {
            $logFilePath = base_path('public/logs.txt');
            file_put_contents($logFilePath, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            Log::error('Failed to write to logs.txt: ' . $e->getMessage());
        }
    }
}
