<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Product",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="qtitle", type="string"),
 *     @OA\Property(property="quantity", type="number"),
 *     @OA\Property(property="price", type="number"),
 *     @OA\Property(property="discount", type="number"),
 *     @OA\Property(property="discount_price", type="number"),
 *     @OA\Property(property="information", type="string"),
 * )
 */
class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('enabled', 1)->get();
        return response()->json($products);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="دریافت اطلاعات یک محصول",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="شناسه محصول",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="اطلاعات محصول",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="محصول پیدا نشد"
     *     )
     * )
     */
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'محصول پیدا نشد'], 404);
        }

        return response()->json($product);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="افزودن محصول جدید",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "qtitle", "quantity", "price"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="qtitle", type="string"),
     *             @OA\Property(property="quantity", type="number"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="information", type="string"),
     *             @OA\Property(property="discount", type="number"),
     *             @OA\Property(property="discount_price", type="number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="محصول با موفقیت ثبت شد"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="داده‌های نامعتبر"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'qtitle' => 'required|string',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'information' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'discount_price' => 'nullable|numeric'
        ]);

        $validated['enabled'] = 1;

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'محصول با موفقیت ثبت شد',
            'product_id' => $product->id
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="ویرایش اطلاعات محصول",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "qtitle", "quantity", "price"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="qtitle", type="string"),
     *             @OA\Property(property="quantity", type="number"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="information", type="string"),
     *             @OA\Property(property="discount", type="number"),
     *             @OA\Property(property="discount_price", type="number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="محصول بروزرسانی شد"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="محصول پیدا نشد"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'محصول پیدا نشد'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string',
            'qtitle' => 'required|string',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'information' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'discount_price' => 'nullable|numeric'
        ]);

        $product->update($validated);

        return response()->json(['success' => true, 'message' => 'محصول با موفقیت بروزرسانی شد']);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="حذف یا غیرفعالسازی محصول",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="محصول غیرفعال شد"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="محصول پیدا نشد"
     *     )
     * )
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'محصول پیدا نشد'], 404);
        }

        $product->enabled = null;
        $product->save();

        return response()->json(['success' => true, 'message' => 'محصول غیرفعال شد']);
    }
}
