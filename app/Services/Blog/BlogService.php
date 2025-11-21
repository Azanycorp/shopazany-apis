<?php

namespace App\Services\Blog;

use App\Enum\BannerType;
use App\Http\Resources\B2CBlogResource;
use App\Models\B2CBlog;
use App\Models\B2CBlogCategory;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;

class BlogService
{
    use HttpResponse;

    public function getBlogCategories(\Illuminate\Http\Request $request)
    {
        $type = $request->query('type', BannerType::B2C);

        if (! in_array($type, [BannerType::B2C, BannerType::B2B, BannerType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$type}", 400);
        }

        $categories = B2CBlogCategory::select('id', 'name', 'slug', 'type')
            ->where('type', $type)
            ->latest()
            ->get();

        return $this->success($categories, 'Blog categories fetched successfully');
    }

    public function addBlogCategory($request)
    {
        $slug = Str::slug($request->name);

        if (B2CBlogCategory::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.uniqid();
        }

        B2CBlogCategory::query()->create([
            'name' => $request->name,
            'slug' => $slug,
            'type' => $request->type,
        ]);

        return $this->success(null, 'Blog category created successfully', 201);
    }

    public function getBlogCategory($id)
    {
        $category = B2CBlogCategory::select('id', 'name', 'slug', 'type')->find($id);

        if (! $category) {
            return $this->error(null, 'Blog category not found', 404);
        }

        return $this->success($category, 'Blog category fetched successfully');
    }

    public function updateBlogCategory($id, $request)
    {
        $category = B2CBlogCategory::find($id);

        if (! $category) {
            return $this->error(null, 'Blog category not found', 404);
        }

        $slug = Str::slug($request->name);

        if (B2CBlogCategory::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.uniqid();
        }

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return $this->success($category, 'Blog category updated successfully');
    }

    public function deleteBlogCategory($id)
    {
        $category = B2CBlogCategory::find($id);

        if (! $category) {
            return $this->error(null, 'Blog category not found', 404);
        }

        $category->delete();

        return $this->success(null, 'Blog category deleted successfully');
    }

    public function getBlogs(\Illuminate\Http\Request $request)
    {
        $type = $request->query('type', BannerType::B2C);

        if (! in_array($type, [BannerType::B2C, BannerType::B2B, BannerType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$type}", 400);
        }

        $blogs = B2CBlog::with('blogCategory')
            ->where('type', $type)
            ->latest()
            ->paginate(25);

        $data = B2CBlogResource::collection($blogs);

        return $this->withPagination($data, 'Blogs fetched successfully');
    }

    public function addBlog($request)
    {
        if ($request->hasFile('image')) {
            $blogUrl = uploadFunction($request->file('image'), 'blog');
        }

        if ($request->hasFile('meta_image')) {
            $metaImageUrl = uploadFunction($request->file('meta_image'), 'blog');
        }

        $slug = Str::slug($request->title);

        if (B2CBlog::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.uniqid();
        }

        B2CBlog::query()->create([
            'title' => $request->title,
            'slug' => $slug,
            'b2_c_blog_category_id' => $request->blog_category_id,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'image' => $blogUrl['url'] ?? null,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'meta_image' => $metaImageUrl['url'] ?? null,
            'status' => 'published',
            'type' => $request->type,
            'created_by' => userAuthId(),
        ]);

        return $this->success(null, 'Blog created successfully', 201);
    }

    public function getBlog($id)
    {
        $blog = B2CBlog::with('blogCategory')->find($id);

        if (! $blog) {
            return $this->error(null, 'Blog not found', 404);
        }

        return $this->success(new B2CBlogResource($blog), 'Blog fetched successfully');
    }

    public function updateBlog($id, $request)
    {
        $blog = B2CBlog::find($id);

        if (! $blog) {
            return $this->error(null, 'Blog not found', 404);
        }

        if ($request->hasFile('image')) {
            $blogUrl = uploadFunction($request->file('image'), 'blog');
        }

        if ($request->hasFile('meta_image')) {
            $metaImageUrl = uploadFunction($request->file('meta_image'), 'blog');
        }

        $slug = Str::slug($request->title);

        if (B2CBlog::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.uniqid();
        }

        $blog->update([
            'title' => $request->title,
            'slug' => $slug,
            'b2_c_blog_category_id' => $request->blog_category_id,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'image' => $blogUrl['url'] ?? null,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'meta_image' => $metaImageUrl['url'] ?? null,
            'status' => 'published',
        ]);

        return $this->success(new B2CBlogResource($blog), 'Blog updated successfully');
    }

    public function deleteBlog($id)
    {
        $blog = B2CBlog::find($id);

        if (! $blog) {
            return $this->error(null, 'Blog not found', 404);
        }

        $blog->delete();

        return $this->success(null, 'Blog deleted successfully');
    }

    public function getAllBlogs(\Illuminate\Http\Request $request)
    {
        $type = $request->query('type', BannerType::B2C);

        if (! in_array($type, [BannerType::B2C, BannerType::B2B, BannerType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$type}", 400);
        }

        $blogs = B2CBlog::with('blogCategory')
            ->where('type', $type)
            ->latest()
            ->paginate(25);

        $data = B2CBlogResource::collection($blogs);

        return $this->withPagination($data, 'Blogs fetched successfully');
    }

    public function getBlogDetail($slug)
    {
        $blog = B2CBlog::with('blogCategory')
            ->where('slug', $slug)
            ->first();

        if (! $blog) {
            return $this->error(null, 'Blog not found!', 404);
        }

        return $this->success(new B2CBlogResource($blog), 'Blog fetched successfully');
    }
}
