<?php

namespace App\Http\Controllers\Api\B2B;

use App\Http\Controllers\Controller;
use App\Http\Requests\B2BSubCategoryRequest;
use App\Http\Requests\CategoryRequest;
use App\Services\B2B\ProductCategoryService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductCategoryController extends Controller
{
    public const string MESSAGE = '403 Forbidden';

    public function __construct(
        private readonly ProductCategoryService $service,
        private readonly Gate $gate,
    ) {}

    public function createCategory(CategoryRequest $request)
    {
        abort_if($this->gate->denies('category_create'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->createCategory($request);
    }

    public function updateCategory(CategoryRequest $request, int $id)
    {
        abort_if($this->gate->denies('category_update'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->updateCategory($request, $id);
    }

    public function categories()
    {
        return $this->service->categories();
    }

    public function singleCategory(int $id)
    {
        return $this->service->singleCategory($id);
    }

    public function adminCategories(Request $request)
    {
        abort_if($this->gate->denies('categories'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->adminCategories($request);
    }

    public function createSubCategory(B2BSubCategoryRequest $request)
    {
        abort_if($this->gate->denies('sub_category_create'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->createSubCategory($request);
    }

    public function getSubcategory(Request $request, int $id)
    {
        $request->validate([
            'type' => ['required', 'in:b2b,b2b_agriecom'],
        ]);

        return $this->service->getSubcategory($request, $id);
    }

    public function featuredStatus(Request $request, int $id)
    {
        abort_if($this->gate->denies('category_featured_status'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->featuredStatus($request, $id);
    }

    public function categoryAnalytic()
    {
        return $this->service->categoryAnalytic();
    }

    public function getAdminSubcategory(Request $request)
    {
        abort_if($this->gate->denies('sub_category'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        $request->validate([
            'type' => ['required', 'in:b2b,b2b_agriecom'],
        ]);

        return $this->service->getAdminSubcategory($request);
    }

    public function subStatus(Request $request, int $id)
    {
        return $this->service->subStatus($request, $id);
    }

    public function deleteCategory(int $id)
    {
        return $this->service->deleteCategory($id);
    }

    public function deleteSubCategory(int $id)
    {
        return $this->service->deleteSubCategory($id);
    }
}
