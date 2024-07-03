<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('connect')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/login/verify', 'loginVerify');
    Route::post('/signup', 'signup');
    Route::post('/forgot/password', 'forgot');
    Route::post('/reset/password', 'reset');
    Route::post('/logout', 'logout');
    Route::post('/verify/email', 'verify');

    Route::post('/affiliate/signup', 'affiliateSignup');
});

Route::get('/banners', [ApiController::class, 'banner']);
Route::get('/featured/categories', [ApiController::class, 'categories']);



