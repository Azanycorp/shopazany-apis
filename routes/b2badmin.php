<?php

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\B2B\ProductCategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('validate.header')
    ->group(function (): void {
        Route::prefix('b2b/admin')->group(function (): void {
            Route::prefix('connect')->controller(AdminAuthController::class)->group(function (): void {
                Route::post('/login', 'login');
                Route::post('/forgot/password', 'forgot');
                Route::post('/reset/password', 'reset');
                Route::post('/logout', 'logout');
                Route::post('/verify/email', 'verify');
            });

            Route::group(['middleware' => ['auth:sanctum', 'auth-gates', 'auth.check']], function (): void {

                Route::prefix('category')->controller(ProductCategoryController::class)->group(function (): void {
                    Route::post('/create', 'createCategory');
                    Route::get('/all', 'adminCategories');
                    Route::get('/analytics', 'categoryAnalytic');
                    Route::patch('/change/{category_id}', 'featuredStatus');
                    Route::delete('/delete/{id}', 'deleteCategory');

                    Route::post('/create/subcategory', 'createSubCategory');
                    Route::get('/subcategory', 'getAdminSubcategory');
                    Route::get('/{category_id}/subcategory', 'getSubcategory');
                    Route::patch('/subcategory/status/{sub_category_id}', 'subStatus');
                    Route::delete('/subcategory/delete/{id}', 'deleteSubCategory');
                });
            });
        });
    });
