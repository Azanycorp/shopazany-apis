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
        Route::post('/business-information', [SellerController::class, 'createBusinessInformation']);

        Route::group(['middleware' => ['auth:api', 'auth.check', 'agriecom_seller.auth']], function (): void {
            Route::controller(SellerController::class)
                ->group(function () {
                    Route::get('/', function () {
                        return "";
                    });
                });
        });

        Route::prefix('b2b')->group(function () {

            Route::controller(B2BAuthController::class)->prefix('auth')->group(function () {
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
                Route::controller(B2BSellerController::class)->prefix('seller')->group(function () {
                    // dashboard
                    Route::get('/dashboard', 'dashboard');

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
                });
            });
        });
    });
