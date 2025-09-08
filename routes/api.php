<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\DashboardController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::resource('/survey', SurveyController::class);
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

Route::get('/survey-by-slug/{survey:slug}', [SurveyController::class, 'showForGuest']);
Route::post('/auth/login-or-register', [AuthController::class, 'loginOrRegister']);
