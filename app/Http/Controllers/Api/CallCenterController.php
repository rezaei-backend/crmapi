<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\CallcenterCall;
use App\Models\CustomerSource;
use App\Models\Finance;
use App\Models\LidManagerLog;
use App\Models\OnlineVisitReminder;
use App\Models\SalesReport;
use App\Models\User;
use App\Models\UsersDetail;
use App\Models\Visit;
use App\Models\VisitDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Finance",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="full_name", type="string"),
 *     @OA\Property(property="phone", type="string"),
 *     @OA\Property(property="record_turn", type="string", enum={"consultation", "site", "face-to-face"}),
 *     @OA\Property(property="unit", type="string", enum={"qom", "tehran"}),
 *     @OA\Property(property="deposit_date", type="string", format="date"),
 *     @OA\Property(property="deposit_time", type="string", format="time"),
 *     @OA\Property(property="amount", type="integer"),
 *     @OA\Property(property="refund_reason", type="string"),
 *     @OA\Property(property="card_number", type="string"),
 *     @OA\Property(property="last_four_digits", type="string"),
 *     @OA\Property(property="turn_type", type="string", enum={"turn", "medicine"}),
 *     @OA\Property(property="turn_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="turn_time", type="string", format="time", nullable=true),
 *     @OA\Property(property="dbank", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="SalesReport",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="full_name", type="string"),
 *     @OA\Property(property="phone", type="string"),
 *     @OA\Property(property="appointment_date", type="string", format="date"),
 *     @OA\Property(property="amount", type="integer"),
 *     @OA\Property(property="deposit_date", type="string", format="date"),
 *     @OA\Property(property="deposit_time", type="string", format="time"),
 *     @OA\Property(property="tracking", type="string"),
 *     @OA\Property(property="last_four_digits", type="string"),
 *     @OA\Property(property="report_type", type="string", enum={"turns", "medicines", "phone_visit"}),
 *     @OA\Property(property="dbank", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CallCenterController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/callcenter/finance",
     *     tags={"CallCenter"},
     *     summary="ثبت درخواست عودت وجه",
     *     description="ثبت یک درخواست عودت وجه جدید در سیستم",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"full_name", "phone", "record_turn", "unit", "deposit_date", "deposit_time", "amount", "refund_reason", "card_number", "last_four_digits", "turn_type", "dbank"},
     *             @OA\Property(property="full_name", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="record_turn", type="string", enum={"consultation", "site", "face-to-face"}),
     *             @OA\Property(property="unit", type="string", enum={"qom", "tehran"}),
     *             @OA\Property(property="deposit_date", type="string", format="date", example="1404/05/20"),
     *             @OA\Property(property="deposit_time", type="string", format="time", example="14:30"),
     *             @OA\Property(property="amount", type="integer"),
     *             @OA\Property(property="refund_reason", type="string"),
     *             @OA\Property(property="card_number", type="string"),
     *             @OA\Property(property="last_four_digits", type="string"),
     *             @OA\Property(property="turn_type", type="string", enum={"turn", "medicine"}),
     *             @OA\Property(property="turn_date", type="string", format="date", example="1404/05/21", nullable=true),
     *             @OA\Property(property="turn_time", type="string", format="time", example="15:00", nullable=true),
     *             @OA\Property(property="dbank", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="درخواست عودت با موفقیت ثبت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="id", type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="داده‌های نامعتبر"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
//    ok
    public function storeFinance(Request $request)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string|regex:/^09\d{9}$/',
                'record_turn' => 'required|string|in:consultation,site,face-to-face',
                'unit' => 'required|string|in:qom,tehran',
                'deposit_date' => 'required|regex:/^\d{4}\/\d{2}\/\d{2}$/',
                'deposit_time' => 'required|date_format:H:i',
                'amount' => 'required|numeric|min:1',
                'refund_reason' => 'required|string',
                'card_number' => 'required|string|regex:/^\d{16}$/',
                'last_four_digits' => 'required|string|regex:/^\d{4}$/',
                'turn_type' => 'required|string|in:turn,medicine',
                'turn_date' => 'nullable|regex:/^\d{4}\/\d{2}\/\d{2}$/',
                'turn_time' => 'nullable|date_format:H:i',
                'dbank' => 'required|string|max:100',
            ]);

            $validated['deposit_date'] = Jalalian::fromFormat('Y/m/d', $validated['deposit_date'])->toCarbon()->format('Y-m-d');
            if (isset($validated['turn_date'])) {
                $validated['turn_date'] = Jalalian::fromFormat('Y/m/d', $validated['turn_date'])->toCarbon()->format('Y-m-d');
            }

            if ($validated['turn_type'] === 'turn') {
                if (!isset($validated['turn_date']) || !isset($validated['turn_time'])) {
                    return response()->json(['success' => false, 'message' => 'تاریخ و زمان نوبت برای نوع turn الزامی است.'], 422);
                }
            } else {
                $validated['turn_date'] = null;
                $validated['turn_time'] = null;
            }

            $validated['deposit_time'] = $validated['deposit_time'] . ':00';
            if (isset($validated['turn_time'])) {
                $validated['turn_time'] = $validated['turn_time'] . ':00';
            }

            $duplicateCount = Finance::where('last_four_digits', $validated['last_four_digits'])
                ->whereBetween('deposit_date', [now()->subDays(2)->toDateString(), $validated['deposit_date']])
                ->where('status', 1)
                ->count();

            if ($duplicateCount > 0) {
                return response()->json(['success' => false, 'message' => 'رکورد تکراری، در سه روز گذشته وجود دارد.'], 422);
            }

            $validated['financial_approval'] = 0;
            $validated['refund_approved'] = 0;
            $validated['status'] = 1;

            $finance = Finance::create($validated);

            $finance->deposit_date = Jalalian::fromCarbon(Carbon::parse($finance->deposit_date))->format('Y/m/d');
            if ($finance->turn_date) {
                $finance->turn_date = Jalalian::fromCarbon(Carbon::parse($finance->turn_date))->format('Y/m/d');
            }

            return response()->json([
                'success' => true,
                'message' => 'درخواست عودت وجه با موفقیت ثبت شد.',
                'data' => ['id' => $finance->id]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Finance Store Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/callcenter/sales",
     *     tags={"CallCenter"},
     *     summary="ثبت گزارش فروش",
     *     description="ثبت یک گزارش فروش جدید در سیستم",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"full_name", "phone", "appointment_date", "amount", "deposit_date", "deposit_time", "tracking", "last_four_digits", "report_type", "dbank"},
     *             @OA\Property(property="full_name", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="appointment_date", type="string", format="date", example="1404/05/20"),
     *             @OA\Property(property="amount", type="integer"),
     *             @OA\Property(property="deposit_date", type="string", format="date", example="1404/05/20"),
     *             @OA\Property(property="deposit_time", type="string", format="time", example="14:30"),
     *             @OA\Property(property="tracking", type="string"),
     *             @OA\Property(property="last_four_digits", type="string"),
     *             @OA\Property(property="report_type", type="string", enum={"turns", "medicines", "phone_visit"}),
     *             @OA\Property(property="dbank", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="گزارش فروش با موفقیت ثبت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="id", type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="داده‌های نامعتبر"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
//    ok
    public function storeSalesReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string|regex:/^09\d{9}$/',
                'appointment_date' => 'required|regex:/^\d{4}\/\d{2}\/\d{2}$/',
                'amount' => 'required|numeric|min:1',
                'deposit_date' => 'required|regex:/^\d{4}\/\d{2}\/\d{2}$/',
                'deposit_time' => 'required|date_format:H:i',
                'tracking' => 'required|string|max:255',
                'last_four_digits' => 'required|string|regex:/^\d{4}$/',
                'report_type' => 'required|string|in:turns,medicines,phone_visit',
                'dbank' => 'required|string|max:100',
            ]);

            $validated['appointment_date'] = Jalalian::fromFormat('Y/m/d', $validated['appointment_date'])->toCarbon()->format('Y-m-d');
            $validated['deposit_date'] = Jalalian::fromFormat('Y/m/d', $validated['deposit_date'])->toCarbon()->format('Y-m-d');
            $validated['deposit_time'] = $validated['deposit_time'] . ':00';
            $validated['financial_approval'] = 0;
            $validated['financial_approval_date'] = null;
            $validated['status'] = 1;

            $salesReport = SalesReport::create($validated);

            $salesReport->appointment_date = Jalalian::fromCarbon(Carbon::parse($salesReport->appointment_date))->format('Y/m/d');
            $salesReport->deposit_date = Jalalian::fromCarbon(Carbon::parse($salesReport->deposit_date))->format('Y/m/d');

            return response()->json([
                'success' => true,
                'message' => 'گزارش فروش با موفقیت ثبت شد.',
                'data' => ['id' => $salesReport->id]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Sales Report Store Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/callcenter/finance",
     *     tags={"CallCenter"},
     *     summary="لیست تمام درخواست‌های عودت وجه",
     *     description="دریافت لیست تمام درخواست‌های عودت وجه بدون محدودیت زمانی",
     *     @OA\Response(
     *         response=200,
     *         description="لیست درخواست‌های عودت",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Finance"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
//    ok
    public function getFinanceList()
    {
        try {
            $finances = Finance::orderBy('created_at', 'desc')->get();

            $finances->transform(function ($finance) {
                $finance->deposit_date = Jalalian::fromCarbon(Carbon::parse($finance->deposit_date))->format('Y/m/d');
                if ($finance->turn_date) {
                    $finance->turn_date = Jalalian::fromCarbon(Carbon::parse($finance->turn_date))->format('Y/m/d');
                }
                $finance->created_at = Jalalian::fromCarbon(Carbon::parse($finance->created_at))->format('Y/m/d H:i:s');
                $finance->updated_at = Jalalian::fromCarbon(Carbon::parse($finance->updated_at))->format('Y/m/d H:i:s');
                return $finance;
            });

            return response()->json([
                'success' => true,
                'message' => 'لیست تمام درخواست‌های عودت با موفقیت دریافت شد.',
                'data' => $finances
            ]);
        } catch (\Exception $e) {
            Log::error('Finance List Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/callcenter/sales",
     *     tags={"CallCenter"},
     *     summary="لیست تمام گزارش‌های فروش",
     *     description="دریافت لیست تمام گزارش‌های فروش بدون محدودیت زمانی",
     *     @OA\Response(
     *         response=200,
     *         description="لیست گزارش‌های فروش",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SalesReport"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
//    ok
    public function getSalesList()
    {
        try {
            $salesReports = SalesReport::orderBy('created_at', 'desc')->get();

            $salesReports->transform(function ($salesReport) {
                $salesReport->appointment_date = Jalalian::fromCarbon(Carbon::parse($salesReport->appointment_date))->format('Y/m/d');
                $salesReport->deposit_date = Jalalian::fromCarbon(Carbon::parse($salesReport->deposit_date))->format('Y/m/d');
                $salesReport->created_at = Jalalian::fromCarbon(Carbon::parse($salesReport->created_at))->format('Y/m/d H:i:s');
                $salesReport->updated_at = Jalalian::fromCarbon(Carbon::parse($salesReport->updated_at))->format('Y/m/d H:i:s');
                return $salesReport;
            });

            return response()->json([
                'success' => true,
                'message' => 'لیست تمام گزارش‌های فروش با موفقیت دریافت شد.',
                'data' => $salesReports
            ]);
        } catch (\Exception $e) {
            Log::error('Sales List Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/callcenter/online-visits-manager-log-detail",
     *     tags={"CallCenter"},
     *     summary="جزئیات لاگ مدیر ویزیت‌های آنلاین",
     *     description="دریافت جزئیات لاگ مدیر ویزیت‌های آنلاین شامل تعداد کل ویزیت‌ها، ویزیت‌های امروز و وضعیت تماس‌ها",
     *     @OA\Response(
     *         response=200,
     *         description="جزئیات لاگ با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="totalOnlineVisits", type="integer"),
     *                 @OA\Property(property="todayOnlineVisits", type="integer"),
     *                 @OA\Property(property="called_count", type="integer"),
     *                 @OA\Property(property="not_called_count", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
    public function onlineVisitsManagerLogDetail(Request $request)
    {
        try {
            $today = Carbon::today()->format('Y-m-d');

            $totalOnlineVisits = Visit::where('visit_type', 2)
                ->where('working', 0)
                ->where('admin_id', 0)
                ->count();

            $todayOnlineVisits = Visit::where('visit_type', 2)
                ->whereDate('created_at', $today)
                ->count();

            $callStatus = DB::table('visits as v')
                ->leftJoin('callcenter_calls as c', function ($join) use ($today) {
                    $join->on('v.id', '=', 'c.visit_id')
                        ->whereDate('c.created_at', $today);
                })
                ->where('v.visit_type', 2)
                ->where('v.admin_id', '!=', 0)
                ->whereDate('v.sent_at', $today)
                ->select([
                    DB::raw('SUM(CASE WHEN c.id IS NOT NULL THEN 1 ELSE 0 END) AS called_count'),
                    DB::raw('SUM(CASE WHEN c.id IS NULL THEN 1 ELSE 0 END) AS not_called_count'),
                ])
                ->first();

            $data = [
                'totalOnlineVisits' => $totalOnlineVisits,
                'todayOnlineVisits' => $todayOnlineVisits,
                'called_count' => $callStatus->called_count ?? 0,
                'not_called_count' => $callStatus->not_called_count ?? 0,
            ];

            return response()->json([
                'success' => true,
                'message' => 'جزئیات لاگ مدیر ویزیت‌های آنلاین با موفقیت دریافت شد.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('OnlineVisitsManagerLogDetail Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/callcenter/send-cc-lids-to-admins",
     *     tags={"CallCenter"},
     *     summary="ارسال لیدها به ادمین‌ها",
     *     description="ارسال لیدهای کال سنتر به ادمین‌های مشخص",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ids", "adminId"},
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="adminId", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="لیدها با موفقیت به‌روزرسانی شدند",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="updated_count", type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="داده‌های نامعتبر"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
    public function sendccLidsToAdmins(Request $request)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|min:1',
                'adminId' => 'required|integer|min:1',
            ]);

            $updatedCount = Visit::whereIn('id', $validated['ids'])->update([
                'admin_id' => $validated['adminId'],
                'working' => 1,
                'sent_at' => now(),
            ]);

            $lidManagerLog = LidManagerLog::create([
                'admin_id' => null, // Session::get('admin.id') حذف شده
                'to_admin_id' => $validated['adminId'],
                'quantity' => count($validated['ids']),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'لیدها با موفقیت به‌روزرسانی شدند.',
                'data' => ['updated_count' => $updatedCount]
            ], 201);
        } catch (\Exception $e) {
            Log::error('sendccLidsToAdmins Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/callcenter/callcenter-update-lids-calls",
     *     tags={"CallCenter"},
     *     summary="به‌روزرسانی تماس‌های لیدها",
     *     description="به‌روزرسانی وضعیت لیدها بر اساس تماس‌ها",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"visitId"},
     *             @OA\Property(property="visitId", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="پاکسازی لید با موفقیت انجام شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="updated_count", type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="داده‌های نامعتبر"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
    public function callcenterUpdateLidsCalls(Request $request)
    {
        try {
            $validated = $request->validate([
                'visitId' => 'required|integer|min:1',
            ]);

            // $adminId = Session::get('admin.id'); // حذف شده
            $visitIds = CallcenterCall::whereDate('created_at', Carbon::today())
                ->pluck('visit_id');

            if ($visitIds->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'لطفا با لیدهایتان تماس بگیرید و مجدد تلاش کنید!'], 422);
            }

            $updatedCount = Visit::whereIn('id', $visitIds)->update(['working' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'پاکسازی لید با موفقیت انجام شد.',
                'data' => ['updated_count' => $updatedCount]
            ], 201);
        } catch (\Exception $e) {
            Log::error('callcenterUpdateLidsCalls Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/callcenter/callcenters-add-call",
     *     tags={"CallCenter"},
     *     summary="اضافه کردن تماس",
     *     description="ثبت یک تماس جدید در کال سنتر",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"state", "visitId"},
     *             @OA\Property(property="state", type="integer"),
     *             @OA\Property(property="stateap", type="integer", nullable=true),
     *             @OA\Property(property="statevo", type="integer", nullable=true),
     *             @OA\Property(property="information", type="string", nullable=true),
     *             @OA\Property(property="visitId", type="integer"),
     *             @OA\Property(property="follow_up", type="integer", nullable=true),
     *             @OA\Property(property="date", type="string", format="date", nullable=true),
     *             @OA\Property(property="time", type="string", format="time", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="تماس با موفقیت ثبت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="id", type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="داده‌های نامعتبر"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
    public function callcentersAddCall(Request $request)
    {
        try {
            $validated = $request->validate([
                'state' => 'required|integer',
                'stateap' => 'nullable|integer',
                'statevo' => 'nullable|integer',
                'information' => 'nullable|string',
                'visitId' => 'required|integer|min:1',
                'follow_up' => 'nullable|integer',
                'date' => 'nullable|regex:/^\d{4}\/\d{2}\/\d{2}$/',
                'time' => 'nullable|date_format:H:i',
            ]);

            $validated['information'] = htmlspecialchars($validated['information'] ?? '', ENT_QUOTES, 'UTF-8');
            $validated['follow_up'] = filter_var($validated['follow_up'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $validated['state'] = filter_var($validated['state'], FILTER_SANITIZE_NUMBER_INT);
            $validated['stateap'] = filter_var($validated['stateap'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $validated['statevo'] = filter_var($validated['statevo'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $validated['visit_id'] = $validated['visitId'];
            $validated['admin_id'] = null; // Session::get('admin.id') حذف شده

            unset($validated['visitId']);

            $call = CallcenterCall::create($validated);

            if ($validated['state'] == 1) {
                $reminderData = [
                    'visit_id' => $validated['visit_id'],
                    'admin_id' => null, // Session::get('admin.id') حذف شده
                    'date' => isset($validated['date']) ? Jalalian::fromFormat('Y/m/d', $validated['date'])->toCarbon()->format('Y-m-d') : null,
                    'time' => htmlspecialchars($validated['time'] ?? null, ENT_QUOTES, 'UTF-8'),
                    'state' => 1,
                ];
                OnlineVisitReminder::create($reminderData);
            }

            return response()->json([
                'success' => true,
                'message' => 'تماس با موفقیت ثبت شد.',
                'data' => ['id' => $call->id]
            ], 201);
        } catch (\Exception $e) {
            Log::error('callcentersAddCall Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/callcenter/visitinfo",
     *     tags={"CallCenter"},
     *     summary="اطلاعات ویزیت",
     *     description="دریافت اطلاعات ویزیت بر اساس آیدی",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="اطلاعات ویزیت با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="داده‌های نامعتبر"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
    public function visitinfo(Request $request)
    {
//        try {
            $validated = $request->validate([
                'id' => 'required',
            ]);

            $visit = Visit::where('id', $validated['id'])
                ->with(['user' => function ($query) {
                    $query->select('id', 'fname', 'lname', 'phone', 'birthday');
                }, 'visitDetail' => function ($query) {
                    $query->select('visit_id', 'problems_info', 'abstinence_info', 'doctor_info');
                }])
                ->first(['id', 'user_id', 'order_id', 'state']);

            if (!$visit) {
                return response()->json([
                    'success' => true,
                    'message' => 'هیچ نتیجه‌ای یافت نشد.',
                    'data' => []
                ]);
            }

            $data = [
                'visit' => [
                    'id' => $visit->id,
                    'user_id' => $visit->user_id,
                    'order_id' => $visit->order_id,
                ],
                'user' => [
                    'first_name' => $visit->user->fname ?? null,
                    'last_name' => $visit->user->lname ?? null,
                    'phone' => $visit->user->phone ?? null,
//                    'birthday' => $visit->user->birthday ? Jalalian::fromCarbon(Carbon::parse($visit->user->birthday))->format('Y/m/d') : null,
                ],
                'visit_details' => [
                    'state' => $visit->state,
                    'problems_info' => $visit->visitDetail->problems_info ?? null,
                    'abstinence_info' => $visit->visitDetail->abstinence_info ?? null,
                    'doctor_info' => $visit->visitDetail->doctor_info ?? null,
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات ویزیت با موفقیت دریافت شد.',
                'data' => [$data]
            ]);
//        } catch (\Exception $e) {
//            dd($validated['id']);
//            Log::error('visitinfo Error: ' . $e->getMessage());
//            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
//        }
    }

    /**
     * @OA\Get(
     *     path="/api/callcenter/online-visits-manager-log",
     *     tags={"CallCenter"},
     *     summary="لاگ مدیر ویزیت‌های آنلاین",
     *     description="دریافت لاگ مدیر ویزیت‌های آنلاین",
     *     @OA\Response(
     *         response=200,
     *         description="لاگ با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
    public function onlineVisitsManagerLog(Request $request)
    {
        try {
            $logs = LidManagerLog::whereDate('created_at', Carbon::today())
                ->with(['admin' => function ($query) {
                    $query->select('id', 'fname', 'lname');
                }])
                ->orderBy('created_at', 'desc')
                ->get(['id', 'to_admin_id', 'quantity', 'created_at']);

            $logs->transform(function ($log) {
                $log->name = $log->admin->fname . ' ' . $log->admin->lname;
                $log->created_at = Jalalian::fromCarbon(Carbon::parse($log->created_at))->format('Y/m/d H:i:s');
                return $log;
            });

            return response()->json([
                'success' => true,
                'message' => 'لاگ مدیر ویزیت‌های آنلاین با موفقیت دریافت شد.',
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            Log::error('onlineVisitsManagerLog Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/callcenter/online-visits-manager",
     *     tags={"CallCenter"},
     *     summary="مدیر ویزیت‌های آنلاین",
     *     description="دریافت لیست ویزیت‌های آنلاین مدیر",
     *     @OA\Response(
     *         response=200,
     *         description="لیست با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
    public function onlineVisitsManager(Request $request)
    {
        try {
            $visits = Visit::where('admin_id', 0)
                ->where('working', 0)
                ->where('visit_type', 2)
                ->with(['user' => function ($query) {
                    $query->select('id', 'fname', 'lname', 'phone');
                }])
                ->orderBy('created_at', 'desc')
                ->get(['id', 'user_id', 'created_at']);

            $visits->transform(function ($visit) {
                $visit->name = $visit->user->fname . ' ' . $visit->user->lname;
                $visit->created_at = Jalalian::fromCarbon(Carbon::parse($visit->created_at))->format('Y/m/d H:i:s');
                return $visit;
            });

            return response()->json([
                'success' => true,
                'message' => 'لیست ویزیت‌های آنلاین مدیر با موفقیت دریافت شد.',
                'data' => $visits
            ]);
        } catch (\Exception $e) {
            Log::error('onlineVisitsManager Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/callcenter/online-visits",
     *     tags={"CallCenter"},
     *     summary="ویزیت‌های آنلاین",
     *     description="دریافت لیست ویزیت‌های آنلاین",
     *     @OA\Response(
     *         response=200,
     *         description="لیست با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
    public function onlineVisits(Request $request)
    {
        try {
            // $adminId = Session::get('admin.id'); // حذف شده

            $visits = Visit::where('working', 1)
                ->where('visit_type', 2)
                ->with(['user' => function ($query) {
                    $query->select('id', 'fname', 'lname', 'phone')
                        ->with(['userDetail' => function ($query) {
                            $query->select('user_id', 'description', 'customer_sources_id')
                                ->with(['customerSource' => function ($query) {
                                    $query->select('id', 'name');
                                }]);
                        }]);
                }])
                ->orderBy('created_at', 'desc')
                ->get(['id', 'user_id', 'created_at']);

            $visits->transform(function ($visit) {
                $visit->name = $visit->user->fname . ' ' . $visit->user->lname;
                $visit->customer_sources_id = $visit->user->userDetail->customerSource->name ?? null;
                $visit->description = $visit->user->userDetail->description ?? null;
                $visit->created_at = Jalalian::fromCarbon(Carbon::parse($visit->created_at))->format('Y/m/d H:i:s');
                return $visit;
            });

            return response()->json([
                'success' => true,
                'message' => 'لیست ویزیت‌های آنلاین با موفقیت دریافت شد.',
                'data' => $visits
            ]);
        } catch (\Exception $e) {
            Log::error('onlineVisits Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/callcenter/last-day-online-visits",
     *     tags={"CallCenter"},
     *     summary="ویزیت‌های آنلاین روز گذشته",
     *     description="دریافت ویزیت‌های آنلاین روز گذشته",
     *     @OA\Response(
     *         response=200,
     *         description="لیست با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
    public function lastDayOnlineVisits(Request $request)
    {
        try {
            $yesterday = Carbon::yesterday()->format('Y-m-d');
            // $adminId = Session::get('admin.id'); // حذف شده

            $visits = Visit::whereDate('created_at', $yesterday)
                ->whereDate('updated_at', $yesterday)
                ->with(['calls' => function ($query) {
                    $query->select('id', 'visit_id', 'admin_id', 'information', 'state')
                        ->latest('id')
                        ->limit(1);
                }, 'calls.admin' => function ($query) {
                    $query->select('id', 'lname');
                }, 'user' => function ($query) {
                    $query->select('id', 'fname', 'lname', 'phone');
                }])
                ->get(['id', 'user_id']);

            $transformed = $visits->map(function ($visit) {
                $latestCall = $visit->calls->first();
                return [
                    'visit_id' => $visit->id,
                    'call_id' => $latestCall->id ?? null,
                    'admin_id' => $latestCall->admin_id ?? null,
                    'admin_lname' => $latestCall->admin->lname ?? null,
                    'user_fname' => $visit->user->fname ?? null,
                    'user_lname' => $visit->user->lname ?? null,
                    'user_phone' => $visit->user->phone ?? null,
                    'information' => $latestCall->information ?? null,
                    'state' => $latestCall->state ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'ویزیت‌های آنلاین روز گذشته با موفقیت دریافت شد.',
                'data' => $transformed
            ]);
        } catch (\Exception $e) {
            Log::error('lastDayOnlineVisits Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/callcenter/unanswered-calls",
     *     tags={"CallCenter"},
     *     summary="تماس‌های بی‌پاسخ",
     *     description="دریافت لیست تماس‌های بی‌پاسخ",
     *     @OA\Response(
     *         response=200,
     *         description="لیست با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
    public function unansweredCalls(Request $request)
    {
        try {
            // $adminId = Session::get('admin.id'); // حذف شده

            $visits = Visit::whereHas('calls', function ($query) {
                $query->where('state', 7)
                    ->whereDate('created_at', '<', Carbon::today())
                    ->latest('id');
            })
                ->with(['user' => function ($query) {
                    $query->select('id', 'fname', 'lname', 'phone');
                }])
                ->groupBy('user_id')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'user_id', 'created_at']);

            $unanswered = $visits->map(function ($visit) {
                return [
                    'id' => $visit->id,
                    'name' => $visit->user->fname . ' ' . $visit->user->lname,
                    'phone' => $visit->user->phone,
                    'created_at' => Jalalian::fromCarbon(Carbon::parse($visit->created_at))->format('Y/m/d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'لیست تماس‌های بی‌پاسخ با موفقیت دریافت شد.',
                'data' => $unanswered
            ]);
        } catch (\Exception $e) {
            Log::error('unansweredCalls Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/callcenter/follow-up-calls",
     *     tags={"CallCenter"},
     *     summary="تماس‌های پیگیری",
     *     description="دریافت لیست تماس‌های پیگیری",
     *     @OA\Response(
     *         response=200,
     *         description="لیست با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
    public function followUpCalls(Request $request)
    {
        try {
            // $adminId = Session::get('admin.id'); // حذف شده

            $visits = Visit::whereHas('calls', function ($query) {
                $query->where('follow_up', '!=', 0)
                    ->whereDate('updated_at', Carbon::today());
            })
                ->with(['user' => function ($query) {
                    $query->select('id', 'fname', 'lname', 'phone');
                }, 'calls.admin' => function ($query) {
                    $query->select('id', 'lname');
                }])
                ->orderBy('created_at', 'desc')
                ->get(['id', 'user_id', 'created_at']);

            $followUps = $visits->map(function ($visit) {
                $latestCall = $visit->calls->first();
                return [
                    'id' => $visit->id,
                    'name' => $visit->user->fname . ' ' . $visit->user->lname,
                    'phone' => $visit->user->phone,
                    'admin_lname' => $latestCall->admin->lname ?? null,
                    'created_at' => Jalalian::fromCarbon(Carbon::parse($visit->created_at))->format('Y/m/d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'لیست تماس‌های پیگیری با موفقیت دریافت شد.',
                'data' => $followUps
            ]);
        } catch (\Exception $e) {
            Log::error('followUpCalls Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/callcenter/customer-source-list",
     *     tags={"CallCenter"},
     *     summary="لیست منابع مشتری",
     *     description="دریافت لیست منابع مشتری فعال",
     *     @OA\Response(
     *         response=200,
     *         description="لیست با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data_exists", type="boolean"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"), nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطای سرور"
     *     )
     * )
     */
    public function customerSourceList(Request $request)
    {
        try {
            $sources = CustomerSource::where('status', 1)->get();
            $data_exists = $sources->isNotEmpty();

            return response()->json([
                'success' => true,
                'message' => $data_exists ? 'داده‌ها یافت شدند.' : 'هیچ داده‌ای یافت نشد.',
                'data_exists' => $data_exists,
                'data' => $data_exists ? $sources : []
            ]);
        } catch (\Exception $e) {
            Log::error('customerSourceList Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }
}
