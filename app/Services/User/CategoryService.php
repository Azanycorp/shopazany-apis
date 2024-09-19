<?php

namespace App\Services\User;

use App\Http\Resources\AdminCategoryResource;
use App\Http\Resources\AdminSubCategoryResource;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryService
{
    use HttpResponse;

    public function createCategory($request)
    {
        try {

            $folder = null;

            if(App::environment('production')){
                $folder = '/prod/category';
            } elseif(App::environment(['staging', 'local'])) {
                $folder = '/stag/category';
            }

            if ($request->file('image')) {
                $path = $request->file('image')->store($folder, 's3');
                $url = Storage::disk('s3')->url($path);
            }

            Category::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'image' => $url,
                'featured' => 1,
            ]);

            return $this->success(null, "Created successfully");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function categories()
    {
        $categories = Category::where('featured', 1)
        ->take(10)
        ->get();

        $data = CategoryResource::collection($categories);

        return $this->success($data, "Categories");
    }

    public function adminCategories()
    {
        $search = request()->query('search');

        $categories = Category::with(['product', 'subcategory'])
            ->withCount(['product', 'subcategory'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->get();

        $data = AdminCategoryResource::collection($categories);

        return $this->success($data, "Categories retrieved successfully");
    }

    public function createSubCategory($request)
    {
        $category = Category::with('subcategory')->find($request->category_id);

        if(!$category){
            return $this->error(null, "Not found", 404);
        }

        try {

            $folder = null;
            $url = null;

            if(App::environment('production')){
                $folder = '/prod/category/subcategory';
            } elseif(App::environment(['staging', 'local'])) {
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
        $subcats = SubCategory::where('category_id', $id)
        ->select(['id', 'name', 'slug', 'image', 'status'])
        ->get();

        return $this->success($subcats, "Sub categories");
    }

    public function featuredStatus($request, $id)
    {
        $category = Category::findOrFail($id);

        if ($request->has('featured')) {
            $category->featured = $request->input('featured') ? 1 : 0;
        }

        if ($request->has('status')) {
            $category->status = $request->input('status') == 1 ? 'active' : 'inactive';
        }

        $category->save();

        return $this->success(null, "Category updated successfully");
    }

    public function categoryAnalytic()
    {
        $categories = Category::withCount(['subcategory', 'product'])
            ->get();

        $totalActive = $categories->where('status', 'active')->count();
        $subCategoryActiveCount = Subcategory::where('status', 'active')->count();
        $productActiveCount = Product::where('status', 'active')->count();
        $productInactiveCount = Product::where('status', 'inactive')->count();

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

        $subcats = SubCategory::with(['product', 'category'])
            ->withCount(['product', 'category'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->get();

        $data = AdminSubCategoryResource::collection($subcats);

        return $this->success($data, "Sub categories");
    }

    public function subStatus($request, $id)
    {
        $sub = SubCategory::findOrFail($id);

        if ($request->has('status')) {
            $sub->status = $request->input('status') == 1 ? 'active' : 'inactive';
        }

        $sub->save();

        return $this->success(null, "Category updated successfully");
    }
}


