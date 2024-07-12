<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/hello', function () {
    return "Hello";
});

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
    });

});


Route::group(['middleware' => ['auth:api'], 'prefix' => 'admin'], function () {

    Route::post('/add/slider', [ApiController::class, 'addSlider']);
    Route::get('/slider', [ApiController::class, 'slider']);

});
