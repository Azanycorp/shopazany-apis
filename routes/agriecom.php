<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\B2BSellerController;
use App\Http\Controllers\Api\B2B\B2BAuthController;
use App\Http\Controllers\Api\AgriEcom\AuthController;
use App\Http\Controllers\Api\AgriEcom\SellerController;

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

        Route::prefix('auth')
            ->controller(B2BAuthController::class)
            ->group(function (): void {
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


        Route::controller(B2BSellerController::class)->prefix('seller')->group(function (): void {
            // dashboard
            Route::get('/dashboard', 'dashboard');
        });
    });
