<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgriEcom\B2BSellerController;
use App\Http\Controllers\Api\AgriEcom\AuthController;
use App\Http\Controllers\Api\AgriEcom\SellerController;
use App\Http\Controllers\Api\AgriEcom\B2BAuthController;

Route::middleware('validate.header')
    ->prefix('agriecom')
    ->group(function () {
        Route::prefix('/auth/seller')
            ->controller(AuthController::class)
            ->group(function () {
                Route::post('/login', 'login');
                Route::post('/register', 'register');
                Route::post('/verify', 'verify');
                Route::post('/resend-code', 'resendCode');
            });

        // Buisness Info
        Route::post('/business/information', [SellerController::class, 'createBusinessInformation']);

        Route::group(['middleware' => ['auth:api', 'auth.check', 'agriecom_seller.auth']], function (): void {
            Route::prefix('/seller')
                ->controller(SellerController::class)
                ->group(function () {
                    Route::prefix('/product')
                        ->group(function () {
                            Route::post('/create', 'createProduct');
                            Route::post('/edit/{product_id}/{user_id}', 'updateProduct')
                                ->middleware('ensure.user');
                            Route::get('/{user_id}', 'getProduct')
                                ->middleware('ensure.user');
                            Route::get('/top-selling/{user_id}', 'topSelling')
                                ->middleware('ensure.user');
                            Route::delete('/delete/{product_id}/{user_id}', 'deleteProduct')
                                ->middleware('ensure.user');
                            Route::get('/template', 'getTemplate');
                            Route::post('/import', 'productImport');
                            Route::get('/export/{user_id}/{type}', 'export');

                            // Product Attributes
                            Route::prefix('attribute')->group(function (): void {
                                Route::post('/create', 'createAttribute');
                                Route::get('/{user_id}', 'getAttribute')
                                    ->middleware('ensure.user');
                                Route::get('/{id}/{user_id}', 'getSingleAttribute')
                                    ->middleware('ensure.user');
                                Route::patch('/edit/{id}/{user_id}', 'updateAttribute')
                                    ->middleware('ensure.user');
                                Route::delete('/delete/{id}/{user_id}', 'deleteAttribute')
                                    ->middleware('ensure.user');
                            });
                        });

                    // Withdrawal
                    Route::prefix('withdrawal')->group(function (): void {
                        Route::post('/', 'addMethod');
                        Route::get('/history/{user_id}', 'withdrawalHistory');
                        Route::get('/method/{user_id}', 'withdrawalMethod');
                        Route::post('/request', 'withdrawalRequest')
                            ->middleware(['tx.replay', 'burst.guard']);
                    });

                    // Profile
                    Route::prefix('profile')->group(function (): void {
                        Route::get('/{user_id}', 'profile');
                    });
                });
        });

        Route::prefix('b2b')->group(function () {
            Route::controller(B2BAuthController::class)
                ->prefix('auth')
                ->group(function () {
                    Route::post('/login', 'login');
                    Route::post('/login/verify', 'loginVerify');
                    Route::post('/seller/signup', 'signup');
                    Route::post('/forgot-password', 'forgot');
                    Route::post('/reset-password', 'reset');
                    Route::post('/resend', 'resendCode');
                    Route::post('/logout', 'logout');
                    Route::post('/verify', 'verify');

                    // Buyer Onboarding
                    Route::post('/buyer/signup', 'buyerOnboarding');
                });


            Route::group(['middleware' => ['auth:api', 'auth.check', 'b2b_agriecom_seller.auth']], function () {
                Route::controller(B2BSellerController::class)
                    ->prefix('seller')
                    ->group(function () {
                        // dashboard
                        Route::get('/dashboard', 'dashboard');
                        Route::get('/withdrawals', 'withdrawalHistory');
                        Route::post('/withdrawal-request', 'makeWithdrawalRequest');
                        Route::get('/earning-report', 'getEarningReport');

                        // profile
                        Route::get('/profile', 'profile');
                        Route::post('/edit-account', 'editAccount');
                        Route::patch('/change-password', 'changePassword');
                        Route::post('/edit-company', 'editCompany');

                        // Shipping
                        Route::prefix('shipping')->group(function () {
                            Route::post('/', 'addShipping');
                            Route::get('/{user_id}', 'getAllShipping');
                            Route::get('/details/{user_id}/{shipping_id}', 'getShippingById');
                            Route::patch('/update/{shipping_id}', 'updateShipping');
                            Route::patch('/default/{user_id}/{shipping_id}', 'setDefault');
                            Route::delete('/delete/{user_id}/{shipping_id}', 'deleteShipping');
                        });

                        // payment method
                        Route::prefix('withdrawal-method')->group(function (): void {
                            Route::get('/', 'allWithdrawalMethods');
                            Route::post('/add', 'addWithdrawalMethod');
                            Route::get('/details/{id}', 'getWithdrawalMethod');
                            Route::post('/update/{id}', 'updateWithdrawalMethod');
                            Route::post('/make-default', 'makeDefaultAccount');
                            Route::delete('/delete/{id}', 'deleteWithdrawalMethod');
                        });
                    });
            });
        });
    });
