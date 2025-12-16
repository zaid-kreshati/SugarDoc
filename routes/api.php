<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::controller(AuthController::class)
    ->group(function () {

    Route::post('initiate_registration','initiate_registration');
    Route::post('confirm_registration', 'confirm_registration');
    Route::post('resend_code', 'resend_code');
    Route::post('reset_password', 'reset_password');
    Route::post('confirm_reset_password','confirm_reset_password');
    Route::post('login', 'Login');

    Route::post('logout', 'logout')->middleware('auth:sanctum');
    });

