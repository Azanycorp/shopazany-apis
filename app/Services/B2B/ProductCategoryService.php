<?php

namespace App\Services\B2B;

use App\Enum\CategoryStatus;
use App\Http\Resources\AdminB2BSubCategoryResource;
use App\Http\Resources\AdminCategoryResource;
use App\Http\Resources\B2BCategoryResource;
use App\Http\Resources\B2BSubCategoryResource;
use App\Models\B2BProduct;
use App\Models\B2bProductCategory;
use App\Models\B2bProductSubCategory;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;

class ProductCategoryService
{
    use HttpResponse;

    public function createCategory($request)
    {
        try {
            if ($request->file('image')) {
                $url = uploadImage($request, 'image', 'category');
            }

            B2BProductCategory::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'image' => $url['url'] ?? null,
                'featured' => 1,
            ]);

            return $this->success(null, 'Created successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function updateCategory($request, $id)
    {
        $category = B2BProductCategory::findOrFail($id);

        try {
            if ($request->file('image')) {
                $url = uploadImage($request, 'image', 'category');
            }

            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'image' => $url['url'] ?? $category->image,
            ]);

            return $this->success(null, 'Category updated successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function categories()
    {
        $categories = B2BProductCategory::where('featured', 1)
            ->latest()
            ->take(10)
            ->get();

        return $this->success(B2BCategoryResource::collection($categories), 'Categories');
    }

    public function adminCategories()
    {
        $search = request()->query('search');

        $categories = B2BProductCategory::with(['products', 'subcategory'])
            ->withCount(['products', 'subcategory'])
            ->when($search, function ($query, string $search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->latest()
            ->get();

        return $this->success(AdminCategoryResource::collection($categories), 'Categories retrieved successfully');
    }

    public function createSubCategory($request)
    {
        $category = B2BProductSubCategory::with('subcategory')->find($request->category_id);

        if (! $category) {
            return $this->error(null, 'Not found', 404);
        }

        try {

            if ($request->file('image')) {
                $url = uploadImage($request, 'image', 'subcategory');
            }

            $category->subcategory()->create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'image' => $url['url'] ?? null,
            ]);

            return $this->success(null, 'Created successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function getSubcategory($id)
    {
        $subcats = B2bProductSubCategory::with(['products', 'category'])
            ->where('category_id', $id)
            ->get();

        $data = B2BSubCategoryResource::collection($subcats);

        return $this->success($data, 'Sub categories');
    }

    public function featuredStatus($request, $id)
    {
        $category = B2BProductCategory::findOrFail($id);

        if ($request->has('featured')) {
            $category->featured = $request->input('featured') ? 1 : 0;
        }

        if ($request->has('status')) {
            $category->status = $request->input('status') == 1 ? CategoryStatus::ACTIVE : CategoryStatus::INACTIVE;
        }

        $category->save();

        return $this->success(null, 'Category updated successfully');
    }

    public function categoryAnalytic()
    {
        $categories = B2BProductSubCategory::withCount(['subcategory', 'products'])
            ->get();

        $totalActive = $categories->where('status', CategoryStatus::ACTIVE)->count();

        $productCounts = B2BProduct::selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as inactive_count
            ', [CategoryStatus::ACTIVE, CategoryStatus::INACTIVE])
            ->first();

        $data = [
            'total_count' => $categories->count(),
            'total_active' => $totalActive,
            'sub_category_count' => $categories->sum('subcategory_count'),
            'sub_category_active_count' => $totalActive,
            'product_count' => $productCounts->active_count ?? 0,
            'product_inactive_count' => $productCounts->inactive_count ?? 0,
        ];

        return $this->success($data, 'Category analytics');
    }

    public function getAdminSubcategory()
    {
        $search = request()->query('search');

        $subcats = B2BProductSubCategory::with(['products', 'category'])
            ->withCount('products')
            ->when($search, function ($query, string $search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->latest()
            ->get();

        return $this->success(AdminB2BSubCategoryResource::collection($subcats), 'Sub categories');
    }

    public function subStatus($request, $id)
    {
        $sub = B2BProductSubCategory::findOrFail($id);

        if ($request->filled('status')) {
            $sub->status = $request->input('status') == 1 ? CategoryStatus::ACTIVE : CategoryStatus::INACTIVE;
        }

        $sub->save();

        return $this->success(null, 'Category updated successfully');
    }

    public function deleteCategory($id)
    {
        $category = B2bProductCategory::findOrFail($id);

        $category->delete();

        return $this->success(null, 'Deleted successfully');
    }

    public function deleteSubCategory($id)
    {
        $subcat = B2BProductSubCategory::findOrFail($id);

        $subcat->delete();

        return $this->success(null, 'Deleted successfully');
    }
}
