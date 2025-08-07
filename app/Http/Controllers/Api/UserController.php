<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/users/block",
     *     tags={"Users"},
     *     summary="بلاک کردن کاربر",
     *     description="کاربری با شناسه مشخص را بلاک می‌کند.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="نتیجه بلاک کردن"
     *     )
     * )
     */
    public function blockUser(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:users,id'
        ]);

        $affected = DB::table('users')
            ->where('id', $request->id)
            ->where('block', 0)
            ->update(['block' => 1]);

        return response()->json([
            'success' => $affected > 0,
            'message' => $affected > 0 ? 'کاربر با موفقیت بلاک شد.' : 'کاربر قبلا بلاک شده یا پیدا نشد.'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/users/update",
     *     tags={"Users"},
     *     summary="بروزرسانی اطلاعات کاربر",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "fname", "lname", "phone", "birthday"},
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="usersex", type="string"),
     *             @OA\Property(property="fname", type="string"),
     *             @OA\Property(property="lname", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="birthday", type="string", example="1400/01/01"),
     *             @OA\Property(property="ostan", type="string"),
     *             @OA\Property(property="shahr", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="zipcode", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="نتیجه بروزرسانی اطلاعات"
     *     )
     * )
     */

    public function updateUserInfo(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:users,id',
            'fname' => 'required|string',
            'lname' => 'required|string',
            'phone' => 'required|string',
            'birthday' => 'required|string'
        ]);

        // تبدیل تاریخ شمسی به میلادی با Carbon (اگر از Jdf استفاده نشود)
        try {
            [$jy, $jm, $jd] = explode('/', $request->birthday);
            $gregorian = \Morilog\Jalali\CalendarUtils::toGregorian($jy, $jm, $jd);
            $birthday = Carbon::create($gregorian[0], $gregorian[1], $gregorian[2])->toDateString();
        } catch (\Exception $e) {
            return response()->json(['error' => 'تاریخ نامعتبر است.'], 422);
        }

        DB::table('users')->where('id', $request->id)->update([
            'user_sex' => $request->usersex,
            'fname' => $request->fname,
            'lname' => $request->lname,
            'phone' => $request->phone,
            'birthday' => $birthday,
            'password' => $request->phone,
            'updated_at' => now(),
        ]);

        DB::table('usersdetail')->updateOrInsert(
            ['user_id' => $request->id],
            [
                'city' => $request->ostan,
                'town' => $request->shahr,
                'address' => $request->address,
                'zip_code' => $request->zipcode,
            ]
        );

        return response()->json(['success' => true]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/info",
     *     tags={"Users"},
     *     summary="اطلاعات پایه‌ای کاربر",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="اطلاعات کامل کاربر و usersdetail"
     *     )
     * )
     */
    public function getUserInfo(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:users,id'
        ]);

        $user = DB::table('users')
            ->join('usersdetail', 'users.id', '=', 'usersdetail.user_id')
            ->where('users.id', $request->id)
            ->first();

        if (!$user) {
            DB::table('usersdetail')->insert([
                'user_id' => $request->id,
                'city' => '', 'town' => '', 'address' => '', 'zip_code' => ''
            ]);
            $user = DB::table('users')
                ->join('usersdetail', 'users.id', '=', 'usersdetail.user_id')
                ->where('users.id', $request->id)
                ->first();
        }

        // تبدیل تاریخ میلادی به شمسی (اختیاری)
        [$gy, $gm, $gd] = explode('-', $user->birthday);
        [$jy, $jm, $jd] = \Morilog\Jalali\CalendarUtils::toJalali($gy, $gm, $gd);
        $prbirthday = sprintf('%04d/%02d/%02d', $jy, $jm, $jd);

        return response()->json([
            'user' => [
                'user_sex' => $user->user_sex,
                'first_name' => $user->fname,
                'last_name' => $user->lname,
                'phone' => $user->phone,
                'birthday' => $prbirthday,
                'enabled' => $user->enabled
            ],
            'usersdetail' => [
                'city' => $user->city,
                'town' => $user->town,
                'address' => $user->address,
                'zip_code' => $user->zip_code,
                'reign' => $user->reign ?? null,
                'information' => $user->information ?? null,
            ]
        ]);
    }

}
