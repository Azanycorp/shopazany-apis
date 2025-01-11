<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\B2B\ProductCategoryController;
use App\Http\Controllers\Api\AdminAuthController;

Route::prefix('b2b/admin')->group(function () {

    Route::prefix('connect')->controller(AdminAuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/forgot/password', 'forgot');
        Route::post('/reset/password', 'reset');
        Route::post('/logout', 'logout');
        Route::post('/verify/email', 'verify');
    });

    // Route::controller(ApiController::class)->group(function () {
    //     Route::get('brands', 'brands');
    //     Route::get('colors', 'colors');
    //     Route::get('units', 'units');
    //     Route::get('sizes', 'sizes');
    // });

    Route::group(['middleware' => ['auth:sanctum', 'auth-gates']], function () {

        // Route::get('/profile', [ApiController::class, 'adminProfile']);

        // Route::post('/add/slider', [ApiController::class, 'addSlider']);
        // Route::get('/slider', [ApiController::class, 'slider']);
        // Route::resource('brand', BrandController::class);
        // Route::resource('color', ColorController::class);
        // Route::resource('unit', UnitController::class);
        // Route::resource('size', SizeController::class);
        // Route::post('/shop/country', [ApiController::class, 'shopByCountry']);

        // Route::prefix('banner')->controller(BannerPromoController::class)->group(function () {
        //     Route::post('/add', 'addBanner');
        //     Route::get('/', 'banners');
        //     Route::get('/{id}', 'getOneBanner');
        //     Route::post('/edit/{id}', 'editBanner');
        //     Route::delete('/delete/{id}', 'deleteBanner');
        // });

        // Route::prefix('promo')->controller(BannerPromoController::class)->group(function () {
        //     Route::post('/add', 'addPromo');
        //     Route::get('/', 'promos');
        //     Route::delete('/delete/{id}', 'deletePromo');
        // });

        // Route::prefix('dashboard')->controller(DashboardController::class)->group(function () {
        //     Route::get('/analytic', 'dashboardAnalytics');
        //     Route::get('/best-sellers', 'bestSellers');
        //     Route::get('/best-selling-categories', 'bestSellingCat');
        // });

        Route::prefix('category')->controller(ProductCategoryController::class)->group(function () {
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
