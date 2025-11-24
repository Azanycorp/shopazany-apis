<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SubCategoryRequest;
use App\Services\User\CategoryService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public const MESSAGE = '403 Forbidden';

    public function __construct(
        protected CategoryService $service,
        private readonly Gate $gate
    ) {}

    public function createCategory(CategoryRequest $request)
    {
        abort_if($this->gate->denies('category_create'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->createCategory($request);
    }

    public function categories(Request $request)
    {
        return $this->service->categories($request);
    }

    public function adminCategories(Request $request)
    {
        abort_if($this->gate->denies('categories'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->adminCategories($request);
    }

    public function createSubCategory(SubCategoryRequest $request)
    {
        abort_if($this->gate->denies('sub_category_create'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->createSubCategory($request);
    }

    public function getSubcategory($id, Request $request)
    {
        // abort_if($this->gate->denies('sub_category'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->getSubcategory($id, $request);
    }

    public function featuredStatus(Request $request, $id)
    {
        abort_if($this->gate->denies('category_featured_status'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->featuredStatus($request, $id);
    }

    public function categoryAnalytic(Request $request)
    {
        abort_if($this->gate->denies('categories'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->categoryAnalytic($request);
    }

    public function getAdminSubcategory(Request $request)
    {
        abort_if($this->gate->denies('sub_category'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->getAdminSubcategory($request);
    }

    public function subStatus(Request $request, $id)
    {
        abort_if($this->gate->denies('categories'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->subStatus($request, $id);
    }

    public function editCategory(Request $request)
    {
        abort_if($this->gate->denies('categories'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        $request->validate([
            'id' => ['required', 'exists:categories,id'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        return $this->service->editCategory($request);
    }

    public function deleteCategory($id)
    {
        abort_if($this->gate->denies('categories'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->deleteCategory($id);
    }

    public function deleteSubCategory($id)
    {
        abort_if($this->gate->denies('sub_category'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->deleteSubCategory($id);
    }
}
