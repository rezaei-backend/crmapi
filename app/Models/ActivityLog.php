<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Admin;
use Carbon\Carbon;

class ActivityLog extends Model
{
    protected $fillable = [
        'action',
        'model_type',
        'model_id',
        'description',
        'admin_id',
        'login_at',
        'logout_at',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    /**
     * ارتباط با مدل Admin
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * ثبت لاگ عمومی برای عملیات CRUD
     */
    public static function record($action, $model, $modelId)
    {
        $modelType = is_string($model) ? $model : class_basename($model);

        $admin = Admin::find($modelId);

        $adminName = $admin ? "{$admin->fname} {$admin->lname}" : 'Unknown';
        $adminId = $admin ? $admin->id : null;

        if ($action === 'created') {
            $message = "ادمین {$adminName} ایجاد شد.";
        } elseif ($action === 'login') {
            $message = "ادمین {$adminName} وارد شد.";
        } elseif ($action === 'logout') {
            $message = "ادمین {$adminName} خارج شد.";
        } else {
            $actionText = match ($action) {
                'created' => 'ایجاد شد',
                'updated' => 'ویرایش شد',
                'deleted' => 'حذف شد',
                default => $action
            };
            $message = "رکوردی از مدل {$modelType} توسط {$adminName} {$actionText} شد.";
        }

        $log = self::create([
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $message,
            'admin_id' => $adminId,
        ]);

        if ($action === 'login') {
            $log->update(['login_at' => Carbon::now()]);
        }

        self::writeToFile($log, $message);

        return $log;
    }

    /**
     * ثبت لاگ زمان ورود ادمین
     */
    public static function recordLogin($adminId)
    {
        $admin = Admin::find($adminId);
        $adminName = $admin ? "{$admin->fname} {$admin->lname}" : 'Unknown';
        $message = "ادمین {$adminName} وارد شد.";

        $log = self::create([
            'action' => 'login',
            'model_type' => 'Admin',
            'model_id' => $admin ? $admin->id : null,
            'description' => $message,
            'admin_id' => $admin ? $admin->id : null,
            'login_at' => Carbon::now(),
        ]);

        self::writeToFile($log, $message);
    }

    /**
     * ثبت لاگ زمان خروج ادمین
     */
    public static function recordLogout($adminId)
    {
        $admin = Admin::find($adminId);
        $adminName = $admin ? "{$admin->fname} {$admin->lname}" : 'Unknown';
        $message = "ادمین {$adminName} خارج شد.";

        $lastLog = self::where('admin_id', $admin ? $admin->id : null)
            ->whereNull('logout_at')
            ->latest()
            ->first();

        if ($lastLog) {
            $lastLog->update([
                'logout_at' => Carbon::now(),
                'action' => 'logout',
                'description' => $message,
            ]);

            self::writeToFile($lastLog, $message);
        }
    }

    /**
     * نوشتن لاگ در فایل logs.txt
     */
    protected static function writeToFile($log, $message)
    {
        $admin = $log->admin_id ? Admin::find($log->admin_id) : null;
        $adminName = $admin ? "{$admin->fname} {$admin->lname}" : 'Unknown';

        $logEntry = sprintf(
            "[%s] User: %s, Action: %s, Model: %s, Model ID: %s, Description: %s\n",
            now()->timezone('Asia/Tehran')->format('Y/m/d H:i:s'),
            $adminName,
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
