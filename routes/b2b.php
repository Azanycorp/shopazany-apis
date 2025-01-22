<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\B2BController;
use App\Http\Controllers\Api\B2BSellerController;
use App\Http\Controllers\Api\B2BPaymentController;
use App\Http\Controllers\Api\B2B\B2BBuyerController;
use App\Http\Controllers\Api\B2B\B2BAccountController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// B2B
Route::middleware(['throttle:apis'])->group(function () {

    Route::prefix('b2b/connect')
        ->controller(B2BAccountController::class)
        ->group(function () {
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

        Route::controller(B2BSellerController::class)->group(function () {
            //dashboard
            Route::get('/dashboard', 'dashboard');
            Route::get('/withdrawals', 'withdrawalHistory');
            Route::post('/withdrawal-request', 'makeWithdrawalRequest');
            Route::get('/earning-report', 'getEarningReport');

            //Orders and rfqs
            Route::prefix('rfq')->group(function () {
                Route::get('/', 'allRfq');
                Route::get('/details/{id}', 'rfqDetails');
                Route::post('/mark-as-shipped', 'shippedRfq');
                Route::post('/mark-as-delivered', 'markDelivered');
                Route::post('/reply-review', 'replyReview');
                Route::post('/confirm-payment', 'confirmPayment');
                Route::post('/rate-order', 'rateOrder');
                Route::post('/order-feeback', 'orderFeeback');
            });
            //payment method
            Route::prefix('withdrawal-method')->group(function () {
                Route::get('/', 'allWithdrawalMethods');
                Route::post('/add', 'addWithdrawalMethod');
                Route::get('/details/{id}', 'getWithdrawalMethod');
                Route::post('/update/{id}', 'updateWithdrawalMethod');
                Route::delete('/delete/{id}', 'deleteWithdrawalMethod');
            });
            //complaints log
            Route::get('/refund/request', 'getComplaints');

            //profile
            Route::get('/profile', 'profile');
            Route::post('/edit-account', 'editAccount');
            Route::patch('/change-password', 'changePassword');
            Route::post('/edit-company', 'editCompany');

            // Product
            Route::prefix('product')->group(function () {
                Route::post('/add', 'addProduct');
                Route::get('/analytic/{user_id}', 'getAnalytics');
                Route::get('/details/{product_id}/{user_id}', 'getProductById');
                Route::post('/update', 'updateProduct');
                Route::delete('/delete/{user_id}/{product_id}', 'deleteProduct');
                Route::post('import', 'productImport');
                Route::get('export/{user_id}/{type}', 'export');
                Route::get('/{user_id}', 'getAllProduct');
            });

            // Shipping
            Route::prefix('shipping')->group(function () {
                Route::post('/', 'addShipping');
                Route::get('/{user_id}', 'getAllShipping');
                Route::get('/{user_id}/{shipping_id}', 'getShippingById');
                Route::patch('/update/{shipping_id}', 'updateShipping');
                Route::patch('/default/{user_id}/{shipping_id}', 'setDefault');
                Route::delete('/delete/{user_id}/{shipping_id}', 'deleteShipping');
            });
        });
    });

    // Buyer
    Route::group(['middleware' => 'b2b_buyer.auth', 'prefix' => 'buyer', 'controller' => B2BBuyerController::class], function () {
        Route::post('request/refund', 'requestRefund');
        Route::post('add-quote', 'requestQuote');
        Route::get('quotes', 'allQuotes');
        Route::get('send-all-quotes', 'sendAllQuotes');
        Route::post('send-rfq', 'sendSingleQuote');
        Route::delete('remove-rfq/{id}', 'removeQuote');
        Route::get('dashboard', 'dashboard');
        Route::get('rfq', 'getAllRfqs');
        Route::get('rfq-details/{id}', 'getRfqDetails');
        Route::post('request-review', 'reviewRequest');
        Route::post('add-review', 'addReview');
        Route::post('accept-quote', 'acceptQuote');
        Route::post('/add-to-wish', 'addTowishList');
        Route::post('/like-product', 'likeProduct');
        Route::get('/wish-list', 'wishList');
        Route::delete('/wish/remove-item/{id}', 'removeItem');
        Route::post('/wish/send-quote', 'sendFromWishList');

        //profile
        Route::get('/profile', 'profile');
        Route::post('/edit-account', 'editAccount');
        Route::patch('/change-password', 'changePassword');
        Route::post('/change-2fa', 'change2Fa');
        Route::get('/company-info', 'companyInfo');
        Route::post('/edit-company', 'editCompany');
    });
});
