<?php

namespace App\Services\User;

use App\Enum\BannerType;
use App\Enum\CategoryStatus;
use App\Http\Resources\AdminCategoryResource;
use App\Http\Resources\AdminSubCategoryResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\SubCategoryResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;

class CategoryService
{
    use HttpResponse;

    public function createCategory($request)
    {
        try {
            if ($request->hasFile('image')) {
                $folder = folderName('category');
                $url = uploadFunction($request->file('image'), $folder);
            }

            $slug = Str::slug($request->name);
            if (Category::where('slug', $slug)->exists()) {
                $slug = $slug.'-'.uniqid();
            }

            Category::create([
                'name' => $request->name,
                'slug' => $slug,
                'image' => $url['url'] ?? null,
                'public_id' => $url['public_id'] ?? null,
                'featured' => $request->boolean('featured'),
                'type' => $request->type,
            ]);

            return $this->success(null, 'Created successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function categories()
    {
        $type = request()->query('type', BannerType::B2C);

        if (! in_array($type, [BannerType::B2C, BannerType::B2B, BannerType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$type}", 400);
        }

        $categories = Category::where('type', $type)
            ->where('featured', 1)
            ->orWhere('featured', true)
            ->get();

        $data = CategoryResource::collection($categories);

        return $this->success($data, 'Categories');
    }

    public function adminCategories()
    {
        $type = request()->query('type', BannerType::B2C);

        if (! in_array($type, [BannerType::B2C, BannerType::B2B, BannerType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$type}", 400);
        }

        $search = request()->query('search');

        $categories = Category::with(['products', 'subcategory'])
            ->withCount(['products', 'subcategory'])
            ->where('type', $type)
            ->when($search, function ($query, string $search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->get();

        $data = AdminCategoryResource::collection($categories);

        return $this->success($data, 'Categories retrieved successfully');
    }

    public function createSubCategory($request)
    {
        $category = Category::with('subcategory')->find($request->category_id);

        if (! $category) {
            return $this->error(null, 'Not found', 404);
        }

        try {
            if ($request->hasFile('image')) {
                $folder = folderName('category/subcategory');
                $url = uploadFunction($request->file('image'), $folder);
            }

            $category->subcategory()->create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'image' => $url['url'] ?? null,
                'public_id' => $url['public_id'] ?? null,
                'type' => $request->type,
            ]);

            return $this->success(null, 'Created successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function getSubcategory($id)
    {
        $type = request()->query('type', BannerType::B2C);

        if (! in_array($type, [BannerType::B2C, BannerType::B2B, BannerType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$type}", 400);
        }

        $subcats = SubCategory::with(['products', 'category'])
            ->where('category_id', $id)
            ->where('type', $type)
            ->get();

        $data = SubCategoryResource::collection($subcats);

        return $this->success($data, 'Sub categories');
    }

    public function featuredStatus($request, $id)
    {
        $category = Category::findOrFail($id);

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
        $type = request()->query('type', BannerType::B2C);

        if (! in_array($type, [BannerType::B2C, BannerType::B2B, BannerType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$type}", 400);
        }

        $categories = Category::withCount(['subcategory', 'products'])
            ->where('type', $type)
            ->get();

        $totalActive = $categories->where('status', CategoryStatus::ACTIVE)->count();
        $subCategoryActiveCount = Subcategory::where('status', CategoryStatus::ACTIVE)
            ->where('type', $type)
            ->count();
        $productActiveCount = Product::where('status', CategoryStatus::ACTIVE)
            ->where('type', $type)
            ->count();
        $productInactiveCount = Product::where('status', CategoryStatus::INACTIVE)
            ->where('type', $type)
            ->count();

        $data = [
            'total_count' => $categories->count(),
            'total_active' => $totalActive,
            'sub_category_count' => $categories->sum('subcategory_count'),
            'sub_category_active_count' => $subCategoryActiveCount,
            'product_count' => $productActiveCount,
            'product_inactive_count' => $productInactiveCount,
        ];

        return $this->success($data, 'Category analytics');
    }

    public function getAdminSubcategory()
    {
        $type = request()->query('type', BannerType::B2C);

        if (! in_array($type, [BannerType::B2C, BannerType::B2B, BannerType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$type}", 400);
        }

        $search = request()->query('search');

        $subcats = SubCategory::with(['product', 'category'])
            ->where('type', $type)
            ->withCount(['product', 'category'])
            ->when($search, function ($query, string $search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->get();

        $data = AdminSubCategoryResource::collection($subcats);

        return $this->success($data, 'Sub categories');
    }

    public function subStatus($request, $id)
    {
        $sub = SubCategory::findOrFail($id);

        if ($request->has('status')) {
            $sub->status = $request->input('status') == 1 ? CategoryStatus::ACTIVE : CategoryStatus::INACTIVE;
        }

        $sub->save();

        return $this->success(null, 'Category updated successfully');
    }

    public function editCategory($request)
    {
        $category = Category::findOrFail($request->id);

        if ($request->hasFile('image')) {
            $folder = folderName('category');
            $url = uploadFunction($request->file('image'), $folder, $category);
        }

        $slug = $category->slug;
        if ($request->has('name')) {
            $slug = Str::slug($request->name);
            if (Category::where('slug', $slug)->where('id', '!=', $request->id)->exists()) {
                $slug = $slug.'-'.uniqid();
            }
        }

        $category->update([
            'name' => $request->name ?? $category->name,
            'slug' => $slug,
            'image' => $url['url'] ?? $category->image,
            'public_id' => $url['public_id'] ? $category->public_id : null,
        ]);

        return $this->success(null, 'Updated successfully');
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        deleteFile($category);
        $category->delete();

        return $this->success(null, 'Deleted successfully');
    }

    public function deleteSubCategory($id)
    {
        $subcat = SubCategory::findOrFail($id);
        deleteFile($subcat);
        $subcat->delete();

        return $this->success(null, 'Deleted successfully');
    }
}
