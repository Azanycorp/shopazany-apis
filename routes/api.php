<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SellerController;
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

    Route::get('/country', [ApiController::class, 'country']);
    Route::get('/states/{country_id}', [ApiController::class, 'states']);
});

Route::get('/banners', [ApiController::class, 'slider']);
Route::get('/featured/categories', [ApiController::class, 'categories']);

Route::prefix('user/category')->controller(CategoryController::class)->group(function () {
    Route::get('/all', 'categories');
    Route::get('/subcategory/{category_id}', 'getSubcategory');
});

Route::get('/user/seller/template', [SellerController::class, 'getTemplate']);

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
        Route::post('/subcategory/create', 'createSubCategory');
    });

    Route::prefix('seller')->controller(SellerController::class)->group(function () {
        // Business Information
        Route::post('/business/information', 'businessInfo');
        Route::post('/update-profile/{user_id}', 'updateProfile');
        Route::get('/dashboard/analytic/{user_id}', 'dashboardAnalytics');

        // Product Routes
        Route::prefix('product')->group(function () {
            Route::post('/create', 'createProduct');
            Route::post('/edit/{product_id}/{user_id}', 'updateProduct');
            Route::delete('/delete/{product_id}/{user_id}', 'deleteProduct');
            Route::get('/top-selling/{user_id}', 'topSelling');
            Route::get('/{user_id}', 'getProduct');
            Route::get('/{product_id}/{user_id}', 'getSingleProduct');

            Route::post('import', 'productImport');
            Route::get('export/{user_id}/{type}', 'export');
        });

        // Orders Routes
        Route::prefix('orders/{user_id}')->group(function () {
            Route::get('/', 'getAllOrders');
            Route::get('/confirmed', 'getConfirmedOrders');
            Route::get('/cancelled', 'getCancelledOrders');
            Route::get('/delivered', 'getDeliveredOrders');
            Route::get('/pending', 'getPendingOrders');
            Route::get('/processing', 'getProcessingOrders');
            Route::get('/shipped', 'getShippedOrders');
            Route::get('/summary', 'getOrderSummary');
        });
    });

});




