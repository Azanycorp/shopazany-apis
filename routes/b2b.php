<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\B2BController;
use App\Http\Controllers\Api\B2BBuyerController;
use App\Http\Controllers\Api\B2BSellerController;
use App\Http\Controllers\Api\B2B\B2BAccountController;
use App\Http\Controllers\Api\B2B\Seller\SellerOrderController;
use App\Http\Controllers\Api\B2B\Seller\SellerWalletController;
use App\Http\Controllers\Api\B2B\Seller\SellerProductController;
use App\Http\Controllers\Api\B2B\Seller\SellerProfileController;
use App\Http\Controllers\Api\B2B\Seller\SellerDashboardController;
use App\Http\Controllers\Api\B2B\Seller\SellerComplaintsController;
use App\Http\Controllers\Api\B2B\Seller\SellerShippingAddressController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// B2B
Route::middleware(['throttle:apis'])->group(function () {
    Route::prefix('b2b/connect')->controller(B2BAccountController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/login/verify', 'loginVerify');
        Route::post('/seller/signup', 'signup');
        Route::post('/forgot/password', 'forgot');
        Route::post('/reset/password', 'reset');
        Route::post('/signup/resend', 'resendCode');
        Route::post('/logout', 'logout');
        Route::post('/verify', 'verify');

        // Buyer Onboarding
        Route::post('/buyer/signup', 'buyerOnboarding');
    });

    Route::prefix('b2b')->controller(B2BController::class)->group(function () {
        Route::post('/business/information', 'businessInformation');
        Route::get('/products', 'getProducts');
        Route::get('/product/{slug}', 'getProductDetail');
    });
});

Route::group(['middleware' => ['auth:api'], 'prefix' => 'b2b'], function () {
    // Seller
    Route::group(['middleware' => 'b2b_seller.auth', 'prefix' => 'seller'], function () {

        //dashboard
        Route::controller(SellerDashboardController::class)->group(function () {
            Route::get('/dashboard', 'index');
        });

        //Orders
        Route::controller(SellerOrderController::class)->group(function () {
            Route::get('/orders', 'index');
            Route::get('/order-details/{id}', 'details');
        });

        //complaints log
        Route::controller(SellerComplaintsController::class)->group(function () {
            Route::get('/refund/request', 'getComplaints');
        });

        //earning history
        Route::controller(SellerWalletController::class)->group(function () {
            Route::get('/earning-report/{user_id}', 'getEarningReport');
        });

        //profile
        Route::controller(SellerProfileController::class)->group(function () {
            Route::get('/profile', 'profile');
            Route::post('/edit-account', 'editAccount');
            Route::patch('/change-password', 'changePassword');
            Route::post('/edit-company', 'editCompany');
        });

        // Product
        Route::controller(SellerProductController::class)->prefix('product')->group(function () {
            Route::post('/', 'addProduct');
            Route::get('/{user_id}', 'getAllProduct');
            Route::get('/analytic/{user_id}', 'getAnalytics');
            Route::get('/{user_id}/{product_id}', 'getProductById');
            Route::post('/update', 'updateProduct');
            Route::delete('/delete/{user_id}/{product_id}', 'deleteProduct');

            Route::post('import', 'productImport');
            Route::get('export/{user_id}/{type}', 'export');
        });

        // Shipping
        Route::controller(SellerShippingAddressController::class)->prefix('shipping')->group(function () {
            Route::post('/', 'addShipping');
            Route::get('/{user_id}', 'getAllShipping');
            Route::get('/{user_id}/{shipping_id}', 'getShippingById');
            Route::patch('/update/{shipping_id}', 'updateShipping');
            Route::patch('/default/{user_id}/{shipping_id}', 'setDefault');
            Route::delete('/delete/{user_id}/{shipping_id}', 'deleteShipping');
        });
    });

    // Buyer
    Route::group(['middleware' => 'b2b_buyer.auth', 'prefix' => 'buyer', 'controller' => B2BBuyerController::class], function () {
        Route::post('request/refund', 'requestRefund');
    });
});
