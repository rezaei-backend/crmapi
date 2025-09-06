<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use App\Models\DiseaseCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DiseasesController extends Controller
{
    /* ==================== CRUD for diseases_category ==================== */

    public function addCategory(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'category_title' => 'required|string|max:256',
            ]);

            DiseaseCategory::create($validated);

            return response()->json(['success' => true, 'message' => 'دسته‌بندی با موفقیت اضافه شد.']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'عنوان دسته الزامی است.'], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database Error'], 500);
        }
    }

    public function getCategories(): \Illuminate\Http\JsonResponse
    {
        try {
            $categories = DiseaseCategory::orderBy('id', 'DESC')->get();

            return response()->json($categories, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database Error'], 500);
        }
    }

    public function updateCategory(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $category = DiseaseCategory::findOrFail($id);

            $validated = $request->validate([
                'category_title' => 'required|string|max:256',
            ]);

            $category->update($validated);

            return response()->json(['success' => true, 'message' => 'دسته‌بندی بروزرسانی شد.']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'داده ورودی معتبر نیست.'], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database Error'], 500);
        }
    }

    public function deleteCategory($id): \Illuminate\Http\JsonResponse
    {
        try {
            $category = DiseaseCategory::findOrFail($id);
            $category->delete();

            return response()->json(['success' => true, 'message' => 'دسته‌بندی حذف شد.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database Error'], 500);
        }
    }

    /* ==================== CRUD for diseases ==================== */

    public function addDisease(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'diseases_title' => 'required|string|max:255',
                'category_id' => 'required|integer|exists:diseases_category,id',
            ]);

            Disease::create($validated);

            return response()->json(['success' => true, 'message' => 'بیماری با موفقیت اضافه شد.']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'اطلاعات ورودی معتبر نیست.'], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database Error'], 500);
        }
    }

    public function getDiseases(): \Illuminate\Http\JsonResponse
    {
        try {
            $diseases = Disease::select('diseases.*', 'diseases_category.category_title')
                ->leftJoin('diseases_category', 'diseases.category_id', '=', 'diseases_category.id')
                ->orderBy('diseases.id', 'DESC')
                ->get();

            return response()->json($diseases, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database Error'], 500);
        }
    }

    public function updateDisease(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $disease = Disease::findOrFail($id);

            $validated = $request->validate([
                'diseases_title' => 'required|string|max:255',
                'category_id' => 'required|integer|exists:diseases_category,id',
            ]);

            $disease->update($validated);

            return response()->json(['success' => true, 'message' => 'بیماری بروزرسانی شد.']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'داده ورودی معتبر نیست.'], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database Error'], 500);
        }
    }

    public function deleteDisease($id): \Illuminate\Http\JsonResponse
    {
        try {
            $disease = Disease::findOrFail($id);
            $disease->delete();

            return response()->json(['success' => true, 'message' => 'بیماری حذف شد.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database Error'], 500);
        }
    }
}
