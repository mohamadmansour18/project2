<?php

use App\Http\Controllers\Auth\AuthenticationDoctorController;
use Illuminate\Support\Facades\Route;

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

Route::controller(AuthenticationDoctorController::class)->group(function (){
    Route::post('/doctor/register' , 'doctorRegister');
    Route::post('/doctor/verifyOtp' , 'verifyDoctorOtp');
    Route::post('/doctor/resendOtp' , 'resendDoctorOtp')->middleware('throttle:resendOtp');

    Route::post('/doctor/login' , 'doctorLogin')->middleware('throttle:login');
});

//    Route::middleware(['throttle:mansour'])->group(function () {
//        Route::get('/man' , function (){
//            return response()->json('hello world');
//        });
//    });



Route::fallback(function (){
    return response()->json([
        'message' => 'This route not found in APIs !' ,
        'status' => 404
    ] , 404);
});



