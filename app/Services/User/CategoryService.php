<?php

namespace App\Services\User;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
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
                'featured' => $request->featured,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description
            ]);

            return $this->success(null, "Created successfully");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function categories()
    {
        $categories = Cache::rememberForever('featured_categories', function () {
            return Category::where('featured', 1)->take(10)->get();
        });

        $data = CategoryResource::collection($categories);

        return $this->success($data, "Categories");
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
        $subcats = SubCategory::where('category_id', $id)->get(['name', 'slug', 'image']);

        return $this->success($subcats, "Sub categories");
    }
}


