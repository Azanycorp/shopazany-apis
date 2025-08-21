<?php

use Illuminate\Support\Facades\Route;

Route::middleware('validate.header')
    ->prefix('agriecom')
    ->group(function () {
        Route::get('/agri', function () {
            return "Welcome to Agriecom";
        });
    });
