<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/hello', function () {
    return "Hello";
});

Route::middleware(['throttle:apis'])->group(function () {

    Route::prefix('connect')->controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/login/verify', 'loginVerify');
        Route::post('/signup', 'signup');
        Route::post('/forgot/password', 'forgot');
        Route::post('/reset/password', 'reset');
        Route::post('/signup/resend', 'resendCode');
        Route::post('/logout', 'logout');
        Route::post('/verify/email', 'verify');
        Route::post('/seller/signup', 'sellerSignup');

        Route::post('/affiliate/signup', 'affiliateSignup');
    });


    Route::group(['middleware' => ['auth:api'], 'prefix' => 'user'], function () {

        Route::controller(UserController::class)->group(function () {
            Route::get('/profile', 'profile');
            Route::post('/bank/account', 'bankAccount');
            Route::delete('/remove/account', 'removeBankAccount');
            Route::post('/withdraw', 'withdraw')
            ->middleware('check.wallet');

            Route::post('/kyc', 'userKyc');
            Route::post('/earning-option', 'earningOption');
        });

        Route::prefix('category')->controller(CategoryController::class)->group(function () {
            Route::post('/create', 'createCategory');
            Route::get('/all', 'categories');
            Route::post('/subcategory/create', 'createSubCategory');
            Route::get('/subcategory/{category_id}', 'getSubcategory');
        });

        Route::prefix('seller')->controller(SellerController::class)->group(function () {
            Route::post('/business/information', 'businessInfo');

            Route::get('/product/{user_id}', 'getProduct');
            Route::get('/get/product/{product_id}/{user_id}', 'getSingleProduct');
            Route::post('/product/create', 'createProduct');
            Route::post('/product/edit/{product_id}/{user_id}', 'updateProduct');
            Route::delete('/delete/product/{product_id}', 'deleteProduct');
        });

    });


    Route::group(['middleware' => ['auth:api'], 'prefix' => 'admin'], function () {

        Route::post('/add/slider', [ApiController::class, 'addSlider']);
        Route::get('/slider', [ApiController::class, 'slider']);
        Route::resource('brand', BrandController::class);
        Route::resource('color', ColorController::class);
        Route::resource('unit', UnitController::class);
        Route::resource('size', SizeController::class);

    });

});
