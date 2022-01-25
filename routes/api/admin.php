<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProfileController;


Route::group(['prefix' => 'profiles'], function () {
    Route::get('intendents', [ProfileController::class,'indexIntendents']);
    Route::get('surrogates', [ProfileController::class,'indexSurrogates']);
    Route::post('checks', [ProfileController::class,'checks']);
    Route::post('status', [ProfileController::class,'status']);
    Route::get('show', [ProfileController::class,'show']);
});


