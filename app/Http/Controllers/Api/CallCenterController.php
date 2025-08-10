<?php

namespace App\Http\Controllers\Api;

use App\Models\Finance;
use App\Models\sales_reports;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;
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
            if ($validated['turn_date']) {
                $validated['turn_date'] = Jalalian::fromFormat('Y/m/d', $validated['turn_date'])->toCarbon()->format('Y-m-d');
            }

            if ($validated['turn_type'] === 'turn') {
                if (!$validated['turn_date'] || !$validated['turn_time']) {
                    return response()->json(['success' => false, 'message' => 'تاریخ و زمان نوبت برای نوع turn الزامی است.'], 422);
                }
            } else {
                $validated['turn_date'] = null;
                $validated['turn_time'] = null;
            }

            // افزودن ثانیه به deposit_time
            $validated['deposit_time'] = $validated['deposit_time'] . ':00';
            if ($validated['turn_time']) {
                $validated['turn_time'] = $validated['turn_time'] . ':00';
            }

            // بررسی رکوردهای تکراری در سه روز گذشته از 4 رقم آخر شماره کارت
            $duplicateCount = Finance::where('last_four_digits', $validated['last_four_digits'])
                ->whereBetween('deposit_date', [
                    now()->subDays(2)->toDateString(),
                    $validated['deposit_date']
                ])
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
            \Log::error('Finance Store Error: ' . $e->getMessage());
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

            // افزودن ثانیه به deposit_time
            $validated['deposit_time'] = $validated['deposit_time'] . ':00';

            $validated['financial_approval'] = 0;
            $validated['financial_approval_date'] = null;
            $validated['status'] = 1;

            $salesReport = sales_reports::create($validated);

            $salesReport->appointment_date = Jalalian::fromCarbon(Carbon::parse($salesReport->appointment_date))->format('Y/m/d');
            $salesReport->deposit_date = Jalalian::fromCarbon(Carbon::parse($salesReport->deposit_date))->format('Y/m/d');

            return response()->json([
                'success' => true,
                'message' => 'گزارش فروش با موفقیت ثبت شد.',
                'data' => ['id' => $salesReport->id]
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Sales Report Store Error: ' . $e->getMessage());
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
            \Log::error('Finance List Error: ' . $e->getMessage());
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
    public function getSalesList()
    {
        try {
            $salesReports = sales_reports::orderBy('created_at', 'desc')->get();

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
            \Log::error('Sales List Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطای غیرمنتظره رخ داده است.'], 500);
        }
    }
}
