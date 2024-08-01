<?php

use App\Http\Controllers\Api\AdminAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Api\UnitController;

Route::prefix('connect')->controller(AdminAuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/forgot/password', 'forgot');
    Route::post('/reset/password', 'reset');
    Route::post('/logout', 'logout');
    Route::post('/verify/email', 'verify');
});

Route::group(['middleware' => ['auth:admin']], function () {

    Route::post('/add/slider', [ApiController::class, 'addSlider']);
    Route::get('/slider', [ApiController::class, 'slider']);
    Route::resource('brand', BrandController::class);
    Route::resource('color', ColorController::class);
    Route::resource('unit', UnitController::class);
    Route::resource('size', SizeController::class);

});





