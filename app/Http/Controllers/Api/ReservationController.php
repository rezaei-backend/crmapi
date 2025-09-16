<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservationsAdminsStorage;
use App\Models\User;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderAdd;
use App\Models\UsersDetail;
use App\Models\Visit;
use App\Models\CallcenterCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ReservationController extends Controller
{
    // Helper: Parse Jalali date (Y/m/d) to Gregorian string (Y-m-d)
    protected function jalaliToGregorianDateString(string $jalaliYmd): string
    {
        return Jalalian::fromFormat('Y/m/d', $jalaliYmd)->toCarbon()->format('Y-m-d');
    }

    // Helper: Format Gregorian date string (Y-m-d) to Jalali (Y/m/d)
    protected function gregorianToJalali(string $gregorianYmd): string
    {
        return Jalalian::fromCarbon(Carbon::parse($gregorianYmd))->format('Y/m/d');
    }

    // Reservations Report
    public function reservationsReport(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'mode' => 'nullable|integer',
                'length' => 'nullable|integer|min:1',
                'start' => 'nullable|integer|min:0',
                'search.value' => 'nullable|string',
            ]);

            $mode = $validated['mode'] ?? 0;
            $limit = $validated['length'] ?? 10;
            $start = $validated['start'] ?? 0;
            $searchValue = $validated['search']['value'] ?? '';

            $query = Reservation::query()
                ->with(['user', 'admin'])
                ->select([
                    'reservations.*',
                    'users.id as user_id',
                    'users.phone',
                    'users.fname as user_fname',
                    'users.lname as user_lname',
                    'admins.fname as admin_fname',
                    'admins.lname as admin_lname',
                ])
                ->leftJoin('users', 'reservations.user_id', '=', 'users.id')
                ->leftJoin('admins', 'reservations.admin_id', '=', 'admins.id');

            if ($mode === 2) {
                $query->whereDate('reservations.date', '>=', now()->toDateString())
                    ->where('reservations.status', 1);
            }

            if ($searchValue) {
                $query->where(function ($q) use ($searchValue) {
                    $like = "%{$searchValue}%";
                    $q->where('users.id', 'like', $like)
                        ->orWhere('users.phone', 'like', $like)
                        ->orWhere('reservations.date', 'like', $like)
                        ->orWhere('reservations.time', 'like', $like)
                        ->orWhere('reservations.information', 'like', $like)
                        ->orWhere('reservations.com_state', 'like', $like)
                        ->orWhere('reservations.status', 'like', $like)
                        ->orWhere('reservations.created_at', 'like', $like)
                        ->orWhere('admins.fname', 'like', $like)
                        ->orWhere('admins.lname', 'like', $like)
                        ->orWhere('users.fname', 'like', $like)
                        ->orWhere('users.lname', 'like', $like);
                });
            }

            $total = $query->count();
            $rows = $query->orderBy('users.id', 'asc')
                ->skip($start)
                ->take($limit)
                ->get();

            $data = $rows->map(function ($row) {
                $shamsi_date = $this->gregorianToJalali($row->date);
                $createdAt = Carbon::parse($row->created_at);
                $shamsi_created_at = Jalalian::fromCarbon($createdAt)->format('Y/m/d') . ' ' . $createdAt->format('H:i');

                return [
                    'id' => $row->user_id,
                    'operator' => trim(($row->admin_fname ?? '') . ' ' . ($row->admin_lname ?? '')),
                    'name' => trim(($row->user_fname ?? '') . ' ' . ($row->user_lname ?? '')),
                    'phone' => $row->phone,
                    'date' => $shamsi_date,
                    'hour' => $row->time,
                    'info' => $row->information,
                    'exists' => $row->com_state ? '✅' : '❌',
                    'status' => $row->status === 1
                        ? '<a class="btn btn-info btn-xs alert-success">فعال</a>'
                        : '<a class="btn btn-info btn-xs alert-danger">حذف شده</a>',
                    'createat' => $shamsi_created_at,
                ];
            })->values();

            return response()->json([
                'draw' => (int) ($request->query('draw') ?? 1),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('ReservationsReport error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }

    // Delete Manual Reservation
    public function delMReservation(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate(['id' => 'required|integer|exists:reservations,id']);
            $id = $validated['id'];

            DB::beginTransaction();

            $reservation = Reservation::findOrFail($id);
            $reservation->update(['status' => 3]);

            ReservationsAdminsStorage::create([
                'admin_id' => auth('admin')->id(),
                'reservations_id' => $id,
                'status' => 1,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'رزرو با موفقیت حذف شد.']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'داده ورودی معتبر نیست.'], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DelMReservation error: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }

    // Delete Reservation
    public function delReservation(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer|exists:reservations,id',
                'reason' => 'nullable|string|max:255',
            ]);

            $id = $validated['id'];
            $reason = $validated['reason'] ?? 'ندارد';

            $affected = Reservation::where('id', $id)->update([
                'status' => 0,
                'reason' => $reason,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => $affected > 0,
                'message' => $affected > 0 ? 'رزرو با موفقیت حذف شد.' : 'هیچ رزروی حذف نشد.'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'داده ورودی معتبر نیست.'], 422);
        } catch (\Exception $e) {
            Log::error('DelReservation error: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }

    // Get 7 Days State
    public function get7DaysState(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate(['mode' => 'nullable|integer']);
            $mode = $validated['mode'] ?? 0;

            $flag = 7 * $mode;
            $start = now()->addDays($flag);
            $end = now()->addDays($flag + 6);

            $query = Reservation::select('date', 'time', DB::raw('COUNT(*) as count'))
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->where('status', 1)
                ->groupBy('date', 'time');

            if ($mode >= 0) {
                $query->havingRaw('COUNT(*) >= 3');
            }

            $results = $query->get()->map(function ($row) {
                $row->date = $this->gregorianToJalali($row->date);
                return $row;
            });

            return response()->json($results, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Get7DaysState error: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }

    // Get 7 Days State 2
    public function get7DaysState2(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate(['mode' => 'nullable|integer']);
            $mode = $validated['mode'] ?? 0;

            $flag = 7 * $mode;
            $start = now()->addDays($flag);
            $end = now()->addDays($flag + 6);

            $query = Reservation::select('date', 'time', DB::raw('COUNT(*) as count'))
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->where('status', 1)
                ->groupBy('date', 'time');

            if ($mode >= 0) {
                $query->havingRaw('COUNT(*) < 3 AND COUNT(*) > 0');
            }

            $results = $query->get()->map(function ($row) {
                $row->date = $this->gregorianToJalali($row->date);
                return $row;
            });

            return response()->json($results, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Get7DaysState2 error: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }

    // Fetch Slot Details
    public function fetchSlotDetails(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'date' => 'required|string|regex:/^\d{4}\/\d{2}\/\d{2}$/',
                'time' => 'required|string',
            ]);

            $gDate = $this->jalaliToGregorianDateString($validated['date']);

            $rows = Reservation::with('user')
                ->where('date', $gDate)
                ->where('time', $validated['time'])
                ->where('status', 1)
                ->get();

            $data = $rows->map(function ($row) {
                return [
                    'actions' => '<a class="btn btn-danger btn-xs" onclick="deleteslotReservation(' . $row->id . ')">حذف</a> ' .
                        '<a class="btn btn-warning btn-xs" onclick="moveslotReservation(' . $row->id . ')">حذف و جا به جایی</a>',
                    'comstate' => $row->com_state ? '✅' : '❌',
                    'name' => trim(($row->user->fname ?? '') . ' ' . ($row->user->lname ?? '')),
                    'lname' => $row->user->lname,
                    'phone' => $row->user->phone,
                ];
            });

            return response()->json(['data' => $data], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'داده ورودی معتبر نیست.'], 422);
        } catch (\Exception $e) {
            Log::error('FetchSlotDetails error: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }

    // Add Manual Reservation
    public function addMReservation(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'date' => 'required|string|regex:/^\d{4}\/\d{2}\/\d{2}$/',
                'time' => 'required|string',
            ]);

            $gDate = $this->jalaliToGregorianDateString($validated['date']);
            $adminId = auth('admin')->id();

            DB::beginTransaction();

            $reservationAdmin = ReservationsAdminsStorage::where('admin_id', $adminId)
                ->where('status', 1)
                ->first();

            if (!$reservationAdmin || !$reservationAdmin->reservations_id) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'داده ورودی معتبر نیست.'], 422);
            }

            $reservationAdmin->update(['status' => 0, 'updated_at' => now()]);

            $reservation = Reservation::findOrFail($reservationAdmin->reservations_id);

            $newReservation = Reservation::create([
                'user_id' => $reservation->user_id,
                'admin_id' => $adminId,
                'order_id' => $reservation->order_id,
                'reserv_city' => $reservation->reserv_city,
                'date' => $gDate,
                'time' => $validated['time'],
                'information' => $reservation->information,
                'com_state' => $reservation->com_state,
                'status' => 1,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'id' => $newReservation->id, 'message' => 'رزرو با موفقیت ثبت شد.']);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'داده ورودی معتبر نیست.'], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AddMReservation error: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }

    // Add Reservation
    public function addReservation(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'date' => 'required|string|regex:/^\d{4}\/\d{2}\/\d{2}$/',
                'time' => 'required|string',
                'fname' => 'required|string|max:255',
                'lname' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'reserv_city' => 'required|string|max:255',
                'paymentmethod' => 'required|integer|in:1,2,3',
            ]);

            $gDate = $this->jalaliToGregorianDateString($validated['date']);

            DB::beginTransaction();

            $user = Admin::where('phone', $validated['phone'])->first();

            if ($user) {
                $user->update([
                    'fname' => $validated['fname'],
                    'lname' => $validated['lname'],
                ]);
                $userId = $user->id;

                if (!UsersDetail::where('user_id', $userId)->exists()) {
                    UsersDetail::create([
                        'user_id' => $userId,
                        'city' => '',
                        'town' => '',
                        'address' => '',
                        'zip_code' => '',
                        'reign' => '',
                        'information' => '',
                        'description' => '',
                        'day_count' => 0,
                    ]);
                }
            } else {
                $user = Admin::create([
                    'phone' => $validated['phone'],
                    'fname' => $validated['fname'],
                    'lname' => $validated['lname'],
                    'password' => Hash::make($validated['phone']),
                ]);
                $userId = $user->id;

                UsersDetail::create([
                    'user_id' => $userId,
                    'city' => '',
                    'town' => '',
                    'address' => '',
                    'zip_code' => '',
                    'reign' => '',
                    'information' => '',
                    'description' => '',
                    'day_count' => 0,
                ]);
            }

            $pos = $cart = $cash = 0;
            switch ($validated['paymentmethod']) {
                case 1: $pos = 200000; break;
                case 2: $cart = 200000; break;
                case 3: $cash = 200000; break;
            }

            if (Reservation::where('user_id', $userId)->where('date', $gDate)->where('status', 1)->exists()) {
                DB::commit();
                return response()->json(['success' => false, 'message' => 'امکان ثبت نوبت تکراری وجود ندارد.'], 422);
            }

            $rowCount = Reservation::where('time', $validated['time'])
                ->where('date', $gDate)
                ->where('status', 1)
                ->count();

            $rowCount2 = Reservation::where('date', $gDate)
                ->where('status', 1)
                ->count();

            if ($rowCount >= 3 || $rowCount2 >= 108) {
                DB::commit();
                return response()->json(['success' => false, 'message' => 'نوبت‌ها به حداکثر رسیده است.'], 422);
            }

            $order = Order::create([
                'user_id' => $userId,
                'admin_id' => auth('admin')->id(),
                'order_type' => 2,
                'state' => 2,
                'cart' => $cart,
                'cash' => $cash,
                'pos' => $pos,
                'information' => 'رزرو جدید',
            ]);

            OrderAdd::create([
                'order_id' => $order->id,
                'product_id' => 1,
                'quantity' => 1,
                'price' => 200000,
                'state' => 1,
                'type' => 1,
            ]);

            Reservation::create([
                'user_id' => $userId,
                'admin_id' => auth('admin')->id(),
                'order_id' => $order->id,
                'reserv_city' => $validated['reserv_city'],
                'date' => $gDate,
                'time' => $validated['time'],
                'information' => 'نوبت',
                'com_state' => 0,
                'status' => 1,
            ]);

            $visit = Visit::where('visit_type', 2)
                ->where('user_id', $userId)
                ->orderByDesc('id')
                ->first();

            if (!$visit) {
                $visit = Visit::create([
                    'order_id' => $order->id,
                    'user_id' => $userId,
                    'information' => 'تماس ورودی داشته',
                    'admin_id' => auth('admin')->id(),
                    'state' => 2,
                    'visit_type' => 2,
                    'working' => 0,
                    'return_number' => 0,
                ]);

                $adminId = auth('admin')->id();
                if (in_array($adminId, [1, 4, 5, 9])) {
                    CallcenterCall::create([
                        'visit_id' => $visit->id,
                        'admin_id' => $adminId,
                        'state' => 3,
                        'statevo' => 0,
                        'stateap' => 0,
                        'follow_up' => 1,
                        'information' => 'لید ورود',
                    ]);
                }
            } else {
                CallcenterCall::where('visit_id', $visit->id)->update(['state' => 3]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'رزرو با موفقیت ثبت شد.']);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'داده ورودی معتبر نیست.'], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AddReservation error: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }

    // Get 7 Days
    public function get7Days(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate(['mode' => 'nullable|integer']);
            $mode = $validated['mode'] ?? 0;

            $flag = 7 * $mode;
            $startDate = now()->addDays($flag);

            $out = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $startDate->copy()->addDays($i);
                $dater = $day->toDateString();

                $daycount = Reservation::whereDate('date', $dater)
                    ->where('status', 1)
                    ->count();

                $jalaliDate = Jalalian::fromCarbon($day)->format('Y/m/d');
                $dayOfWeek = Jalalian::fromCarbon($day)->format('l');

                $out[] = [
                    'dayName' => $dayOfWeek,
                    'jalaliDate' => $jalaliDate,
                    'daycount' => $daycount,
                ];
            }

            return response()->json($out, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Get7Days error: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }

    // Get User Info
    public function getUserInfo(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate(['mode' => 'nullable|integer|in:1,2,3']);
            $mode = $validated['mode'] ?? 1;

            $rows = Admin::where('enabled', 1)
                ->select('id', 'fname', 'lname', 'phone')
                ->get()
                ->map(function ($row) use ($mode) {
                    $action = match ($mode) {
                        1 => '<a class="btn btn-info btn-xs" onclick="SelectUserForReservation(' . $row->id . ')">انتخاب</a>',
                        2 => '<a class="btn btn-info btn-xs" onclick="SelectUserForNewReservation(' . $row->id . ')">انتخاب</a>',
                        3 => '<a class="btn btn-info btn-xs" onclick="SelectUserForonlinevisit(' . $row->id . ')">انتخاب</a>',
                        default => '',
                    };

                    return [
                        'actions' => $action,
                        'name' => trim(($row->fname ?? '') . ' ' . ($row->lname ?? '')),
                        'lname' => $row->lname,
                        'phone' => $row->phone,
                    ];
                });

            return response()->json(['data' => $rows], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('GetUserInfo error: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }

    // Update Reservation Info
    public function updateReservationInfo(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer|exists:reservations,id',
                'fname' => 'required|string|max:255',
                'lname' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'ostan' => 'nullable|string|max:255',
                'shahr' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'zipcode' => 'nullable|string|max:20',
            ]);

            $id = $validated['id'];

            DB::beginTransaction();

            $user = Admin::where('phone', $validated['phone'])->first();
            if ($user) {
                $user->update([
                    'fname' => $validated['fname'],
                    'lname' => $validated['lname'],
                ]);
                $userId = $user->id;

                $details = UsersDetail::where('user_id', $userId)->first();
                if ($details) {
                    $details->update([
                        'city' => $validated['ostan'] ?? '',
                        'town' => $validated['shahr'] ?? '',
                        'address' => $validated['address'] ?? '',
                        'zip_code' => $validated['zipcode'] ?? '',
                        'reign' => '',
                        'information' => '',
                        'description' => '',
                        'day_count' => 0,
                    ]);
                } else {
                    UsersDetail::create([
                        'user_id' => $userId,
                        'city' => $validated['ostan'] ?? '',
                        'town' => $validated['shahr'] ?? '',
                        'address' => $validated['address'] ?? '',
                        'zip_code' => $validated['zipcode'] ?? '',
                        'reign' => '',
                        'information' => '',
                        'description' => '',
                        'day_count' => 0,
                    ]);
                }
            } else {
                $user = Admin::create([
                    'phone' => $validated['phone'],
                    'fname' => $validated['fname'],
                    'lname' => $validated['lname'],
                    'password' => Hash::make($validated['phone']),
                ]);
                $userId = $user->id;

                UsersDetail::create([
                    'user_id' => $userId,
                    'city' => $validated['ostan'] ?? '',
                    'town' => $validated['shahr'] ?? '',
                    'address' => $validated['address'] ?? '',
                    'zip_code' => $validated['zipcode'] ?? '',
                    'reign' => '',
                    'information' => '',
                    'description' => '',
                    'day_count' => 0,
                ]);
            }

            Reservation::where('id', $id)->update(['user_id' => $userId]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'اطلاعات رزرو با موفقیت بروزرسانی شد.']);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'داده ورودی معتبر نیست.'], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('UpdateReservationInfo error: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }

    // Reservation Info
    public function reservationInfo(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate(['id' => 'required|integer|exists:reservations,id']);
            $id = $validated['id'];

            $row = Reservation::with(['user', 'user.details'])
                ->where('id', $id)
                ->first();

            if (!$row) {
                return response()->json(['message' => 'رزرو یافت نشد.'], 404);
            }

            $result = [[
                'user' => [
                    'id' => $id,
                    'user_sex' => $row->user->user_sex,
                    'fname' => $row->user->fname,
                    'lname' => $row->user->lname,
                    'phone' => $row->user->phone,
                    'ostan' => $row->user->details->city ?? '',
                    'shahr' => $row->user->details->town ?? '',
                    'address' => $row->user->details->address ?? '',
                    'zipcode' => $row->user->details->zip_code ?? '',
                ],
            ]];

            return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'داده ورودی معتبر نیست.'], 422);
        } catch (\Exception $e) {
            Log::error('ReservationInfo error: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }

    // Reservations Visited
    public function reservationsVisited(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $rows = Reservation::with('user')
                ->where('com_state', 1)
                ->whereDate('date', now()->toDateString())
                ->where('status', 1)
                ->orderBy('time', 'asc')
                ->get();

            $counter = 0;
            $data = $rows->map(function ($row) use (&$counter) {
                $counter++;
                return [
                    'id' => $counter,
                    'name' => trim(($row->user->fname ?? '') . ' ' . ($row->user->lname ?? '')),
                    'time' => $row->time,
                    'lname' => $row->user->lname,
                    'actions' => '<button class="btn btn-info btn-xs" data-toggle="modal" data-target=".reservationtovisit" onclick="reservationtovisit(' . $row->id . ')">افزودن ویزیت</button>',
                ];
            });

            return response()->json(['data' => $data], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('ReservationsVisited error: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }

    // Reservations Not Visited
    public function reservationsNotVisited(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $rows = Reservation::with('user')
                ->where('com_state', 0)
                ->whereDate('date', now()->toDateString())
                ->where('status', 1)
                ->orderBy('time', 'asc')
                ->get();

            $counter = 0;
            $data = $rows->map(function ($row) use (&$counter) {
                $counter++;
                return [
                    'id' => $counter,
                    'name' => trim(($row->user->fname ?? '') . ' ' . ($row->user->lname ?? '')),
                    'time' => $row->time,
                    'lname' => $row->user->lname,
                    'actions' => '<button class="btn btn-info btn-xs" data-toggle="modal" data-target=".reservationtovisit" onclick="reservationtovisit(' . $row->id . ')">افزودن ویزیت</button>',
                ];
            });

            return response()->json(['data' => $data], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('ReservationsNotVisited error: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در پایگاه داده'], 500);
        }
    }
}
