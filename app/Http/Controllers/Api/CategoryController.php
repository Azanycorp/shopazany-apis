<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SubCategoryRequest;
use App\Services\User\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $service;

    public function __construct(CategoryService $categoryService)
    {
        $this->service = $categoryService;
    }

    public function createCategory(CategoryRequest $request)
    {
        return $this->service->createCategory($request);
    }

    public function categories()
    {
        return $this->service->categories();
    }

    public function createSubCategory(SubCategoryRequest $request)
    {
        return $this->service->createSubCategory($request);
    }

    public function getSubcategory($id)
    {
        return $this->service->getSubcategory($id);
    }
}
