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

    Route::prefix('/doctor')->group(function (){

        Route::post('/register' , 'doctorRegister');
        Route::post('/verifyOtp' , 'verifyDoctorOtp');
        Route::post('/resendOtp' , 'resendDoctorOtp')->middleware('throttle:resendOtp');

        Route::post('/login' , 'doctorLogin')->middleware('throttle:login');

        Route::post('/forgetPassword/sendOtp' , 'forgotPassword')->middleware('throttle:resendOtp');
        Route::post('/forgetPassword/verifyOtp' , 'forgotPasswordOtp');
        Route::post('/forgetPassword/resetPassword' , 'resetPassword');
        Route::post('/forgetPassword/resendOtp' , 'resendPasswordResetOtp')->middleware('throttle:resendOtp');

    });

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



