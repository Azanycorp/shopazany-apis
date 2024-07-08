<?php

namespace App\Services\User;

use App\Models\Category;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\App;
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
}


