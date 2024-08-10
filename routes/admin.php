<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AdminSellerController;
use App\Http\Controllers\Api\DashboardController;

Route::prefix('connect')->controller(AdminAuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/forgot/password', 'forgot');
    Route::post('/reset/password', 'reset');
    Route::post('/logout', 'logout');
    Route::post('/verify/email', 'verify');
});

Route::controller(ApiController::class)->group(function () {
    Route::get('brands', 'brands');
    Route::get('colors', 'colors');
    Route::get('units', 'units');
    Route::get('sizes', 'sizes');
});

Route::group(['middleware' => ['auth:admin']], function () {

    Route::post('/add/slider', [ApiController::class, 'addSlider']);
    Route::get('/slider', [ApiController::class, 'slider']);
    Route::resource('brand', BrandController::class);
    Route::resource('color', ColorController::class);
    Route::resource('unit', UnitController::class);
    Route::resource('size', SizeController::class);

    Route::prefix('dashboard')->controller(DashboardController::class)->group(function () {
        Route::get('/analytic', 'dashboardAnalytics');
        Route::get('/best-sellers', 'bestSellers');
        Route::get('/best-selling-categories', 'bestSellingCat');
    });

    Route::prefix('order')->controller(OrderController::class)->group(function () {
        Route::get('/analytic', 'orderAnalytics');
        Route::get('/local', 'localOrder');
        Route::get('/international', 'intOrder');
        Route::get('/detail/{id}', 'orderDetail');
        Route::get('/search', 'searchOrder');
    });

    Route::prefix('seller')->controller(AdminSellerController::class)->group(function () {
        Route::get('/', 'allSellers');
    });
});





