<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SubCategoryRequest;
use App\Services\User\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    const MESSAGE = '403 Forbidden';

    protected \App\Services\User\CategoryService $service;

    public function __construct(CategoryService $categoryService)
    {
        $this->service = $categoryService;
    }

    public function createCategory(CategoryRequest $request)
    {
        abort_if(Gate::denies('category_create'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->createCategory($request);
    }

    public function categories()
    {
        return $this->service->categories();
    }

    public function adminCategories()
    {
        abort_if(Gate::denies('categories'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->adminCategories();
    }

    public function createSubCategory(SubCategoryRequest $request)
    {
        abort_if(Gate::denies('sub_category_create'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->createSubCategory($request);
    }

    public function getSubcategory($id)
    {
        abort_if(Gate::denies('sub_category'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->getSubcategory($id);
    }

    public function featuredStatus(Request $request, $id)
    {
        abort_if(Gate::denies('category_featured_status'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->featuredStatus($request, $id);
    }

    public function categoryAnalytic()
    {
        abort_if(Gate::denies('categories'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->categoryAnalytic();
    }

    public function getAdminSubcategory()
    {
        abort_if(Gate::denies('sub_category'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->getAdminSubcategory();
    }

    public function subStatus(Request $request, $id)
    {
        abort_if(Gate::denies('categories'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->subStatus($request, $id);
    }

    public function editCategory(Request $request)
    {
        abort_if(Gate::denies('categories'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        $request->validate([
            'id' => 'required|exists:categories,id',
            'name' => 'nullable|string|max:255',
        ]);

        return $this->service->editCategory($request);
    }

    public function deleteCategory($id)
    {
        abort_if(Gate::denies('categories'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->deleteCategory($id);
    }

    public function deleteSubCategory($id)
    {
        abort_if(Gate::denies('sub_category'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->deleteSubCategory($id);
    }
}
