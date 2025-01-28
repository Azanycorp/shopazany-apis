<?php

namespace App\Services\B2B;

use App\Models\Category;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Enum\CategoryStatus;
use App\Models\B2bProductCategory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\B2BCategoryResource;
use App\Http\Resources\AdminCategoryResource;
use App\Http\Resources\B2BSubCategoryResource;
use App\Http\Resources\AdminSubCategoryResource;
use App\Models\B2BProduct;
use App\Models\B2bProductSubCategory;

class ProductCategoryService
{
    use HttpResponse;

    public function createCategory($request)
    {
        try {
            $folder = null;

            if (App::environment('production')) {
                $folder = '/prod/category';
            } elseif (App::environment(['staging', 'local'])) {
                $folder = '/stag/category';
            }

            if ($request->file('image')) {
                $path = $request->file('image')->store($folder, 's3');
                $url = Storage::disk('s3')->url($path);
            }

            B2BProductCategory::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'image' => $request->file('image') ? $url : null,
                'featured' => 1,
            ]);

            return $this->success(null, "Created successfully");
        } catch (\Exception $e) {
            return $e;
            return $this->error(null, $e, 500);
        }
    }

    public function updateCategory($request,$id)
    {
        $category = B2BProductCategory::findOrFail($id);
        try {
            $folder = null;

            if (App::environment('production')) {
                $folder = '/prod/category';
            } elseif (App::environment(['staging', 'local'])) {
                $folder = '/stag/category';
            }

            if ($request->file('image')) {
                $path = $request->file('image')->store($folder, 's3');
                $url = Storage::disk('s3')->url($path);
            }

            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'image' => $request->image ? $url : $category->image,
            ]);
            return $this->success(null, "Category updated successfully");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
    public function categories()
    {
        $categories = B2BProductCategory::where('featured', 1)
            ->take(10)
            ->get();

        $data = B2BCategoryResource::collection($categories);

        return $this->success($data, "Categories");
    }

    public function adminCategories()
    {
        $search = request()->query('search');

        $categories = B2BProductCategory::with(['products', 'subcategory'])
            ->withCount(['products', 'subcategory'])
            ->when($search, function ($query, string $search): void {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->latest('id')
            ->get();

        $data = AdminCategoryResource::collection($categories);

        return $this->success($data, "Categories retrieved successfully");
    }

    public function createSubCategory($request)
    {
        $category = B2BProductSubCategory::with('subcategory')->find($request->category_id);

        if (!$category) {
            return $this->error(null, "Not found", 404);
        }

        try {
            $folder = null;
            $url = null;

            if (App::environment('production')) {
                $folder = '/prod/category/subcategory';
            } elseif (App::environment(['staging', 'local'])) {
                $folder = '/stag/category/subcategory';
            }

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store($folder, 's3');
                $url = Storage::disk('s3')->url($path);
            }

            $category->subcategory()->create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'image' => $url
            ]);

            return $this->success(null, "Created successfully");
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

        return $this->success($data, "Sub categories");
    }


    public function featuredStatus($request, $id)
    {
        $category = B2BProductSubCategory::findOrFail($id);

        if ($request->has('featured')) {
            $category->featured = $request->input('featured') ? 1 : 0;
        }

        if ($request->has('status')) {
            $category->status = $request->input('status') == 1 ? CategoryStatus::ACTIVE : CategoryStatus::INACTIVE;
        }

        $category->save();

        return $this->success(null, "Category updated successfully");
    }

    public function categoryAnalytic()
    {
        $categories = B2BProductSubCategory::withCount(['subcategory', 'products'])
            ->get();

        $totalActive = $categories->where('status', CategoryStatus::ACTIVE)->count();
        $subCategoryActiveCount = B2BProductSubCategory::where('status', CategoryStatus::ACTIVE)->count();
        $productActiveCount = B2BProduct::where('status', CategoryStatus::ACTIVE)->count();
        $productInactiveCount = B2BProduct::where('status', CategoryStatus::INACTIVE)->count();

        $data = [
            'total_count' => $categories->count(),
            'total_active' => $totalActive,
            'sub_category_count' => $categories->sum('subcategory_count'),
            'sub_category_active_count' => $subCategoryActiveCount,
            'product_count' => $productActiveCount,
            'product_inactive_count' => $productInactiveCount,
        ];

        return $this->success($data, "Category analytics");
    }

    public function getAdminSubcategory()
    {
        $search = request()->query('search');

        $subcats = B2BProductSubCategory::with(['product', 'category'])
            ->withCount(['product', 'category'])
            ->when($search, function ($query, string $search): void {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->get();

        $data = AdminSubCategoryResource::collection($subcats);

        return $this->success($data, "Sub categories");
    }

    public function subStatus($request, $id)
    {
        $sub = B2BProductSubCategory::findOrFail($id);

        if ($request->has('status')) {
            $sub->status = $request->input('status') == 1 ? CategoryStatus::ACTIVE : CategoryStatus::INACTIVE;
        }

        $sub->save();

        return $this->success(null, "Category updated successfully");
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
