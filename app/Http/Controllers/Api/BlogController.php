<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Blog\BlogService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        protected BlogService $blogService
    ) {}

    public function getBlogCategories()
    {
        return $this->blogService->getBlogCategories();
    }

    public function addBlogCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string|in:b2c,b2b,agriecom_b2c',
        ]);

        return $this->blogService->addBlogCategory($request);
    }

    public function getBlogCategory($id)
    {
        return $this->blogService->getBlogCategory($id);
    }

    public function updateBlogCategory($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        return $this->blogService->updateBlogCategory($id, $request);
    }

    public function deleteBlogCategory($id)
    {
        return $this->blogService->deleteBlogCategory($id);
    }

    public function getBlogs()
    {
        return $this->blogService->getBlogs();
    }

    public function addBlog(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'short_description' => 'required|string',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'blog_category_id' => 'required|exists:b2_c_blog_categories,id',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'meta_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type' => 'required|string|in:b2c,b2b,agriecom_b2c',
        ]);

        return $this->blogService->addBlog($request);
    }

    public function getBlog($id)
    {
        return $this->blogService->getBlog($id);
    }

    public function updateBlog($id, Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'short_description' => 'required|string',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'blog_category_id' => 'required|exists:b2_c_blog_categories,id',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'meta_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        return $this->blogService->updateBlog($id, $request);
    }

    public function deleteBlog($id)
    {
        return $this->blogService->deleteBlog($id);
    }

    public function getAllBlogs()
    {
        return $this->blogService->getAllBlogs();
    }

    public function getBlogDetail($slug)
    {
        return $this->blogService->getBlogDetail($slug);
    }
}
