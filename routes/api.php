<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth2Controller;

use Illuminate\Support\Facades\Log;

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



// Route::controller(AuthController::class)
//     ->group(function () {

        
//     Route::post('initiate_registration','initiate_registration');
//     Route::post('confirm_registration', 'confirm_registration');
//     Route::post('resend_code', 'resend_code');
//     Route::post('reset_password', 'reset_password');
//     Route::post('confirm_reset_password','confirm_reset_password');
//     Route::post('login', 'Login');
//     Route::post('logout', 'logout')->middleware('auth:sanctum');
//     });


    Route::controller(Auth2Controller::class)
    ->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'Login');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
    });




Route::middleware('auth:sanctum')->controller(ProfileController::class)->group(function () {
    Route::get('/profile/show', 'show');
    Route::post('/profile/update', 'update');
});



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/patients', [PatientController::class, 'index']);
});


Route::middleware('auth:sanctum')->post('/firebase-token', function (Request $request) {
   
     $validated = $request->validate([
        'firebase_token' => 'required|string'
    ]);
    
    $user = $request->user();
    $user->firebase_token = $validated['firebase_token'];
    $user->save();
    
    return response()->json(['message' => 'Token saved']);
});
Route::middleware('auth:sanctum')->controller(NotificationController::class)->group(function () {
    Route::get('/notifications', 'index');
});


