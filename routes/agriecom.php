<?php

use App\Http\Controllers\Api\AgriEcom\AuthController;
use App\Http\Controllers\Api\AgriEcom\SellerController;
use Illuminate\Support\Facades\Route;

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

        Route::group(['middleware' => ['auth:api', 'auth.check', 'agriecom_seller.auth']], function (): void {
            Route::controller(SellerController::class)
                ->group(function () {
                    Route::post('/business-information', 'createBusinessInformation');
                });
        });
    });

