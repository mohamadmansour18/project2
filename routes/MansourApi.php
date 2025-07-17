<?php

use App\Http\Controllers\Auth\AuthenticationAdminController;
use App\Http\Controllers\Auth\AuthenticationDoctorController;
use App\Http\Controllers\Favorite\AnnouncementsController;
use App\Http\Controllers\Other\FormSubmissionPeriodController;
use App\Http\Controllers\Other\StatisticsController;
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
Route::prefix('/doctor')->group(function (){

    Route::controller(AuthenticationDoctorController::class)->group(function (){

            Route::post('/register' , 'doctorRegister');
            Route::post('/verifyOtp' , 'verifyDoctorOtp');
            Route::post('/resendOtp' , 'resendDoctorOtp')->middleware('throttle:resendOtp');

            Route::post('/login' , 'doctorLogin')->middleware('throttle:login');

            Route::post('/forgetPassword/sendOtp' , 'forgotPassword')->middleware('throttle:resendOtp');
            Route::post('/forgetPassword/verifyOtp' , 'forgotPasswordOtp');
            Route::post('/forgetPassword/resetPassword' , 'resetPassword');
            Route::post('/forgetPassword/resendOtp' , 'resendPasswordResetOtp')->middleware('throttle:resendOtp');

    });

    Route::middleware(['auth:api' , 'role:doctor'])->group(function (){

        Route::get('/logout' , [AuthenticationDoctorController::class , 'logout']);

        //home APIs
        Route::get('/home/showFormDates' , [FormSubmissionPeriodController::class , 'getFormDateForDoctor']);
        Route::get('/home/announcementStatistics' , [AnnouncementsController::class , 'getAnnouncementStatistics']);
        Route::get('/home/showNumbersStatistics' , [StatisticsController::class , 'getHomeStatistics']);
        Route::get('/home/showFlowChartStatistics' , [StatisticsController::class , 'getDoctorHomeGroupStatistics']);
    });
});



Route::prefix('/admin')->group(function (){

    Route::controller(AuthenticationAdminController::class)->group(function (){

        Route::post('/login' , 'AdminLogin')->middleware('throttle:login');

        Route::post('/forgetPassword/sendOtp' , 'forgotPassword')->middleware('throttle:resendOtp');
        Route::post('/forgetPassword/verifyOtp' , 'forgotPasswordOtp');
        Route::post('/forgetPassword/resetPassword' , 'resetPassword');
        Route::post('/forgetPassword/resendOtp' , 'resendPasswordResetOtp')->middleware('throttle:resendOtp');
    });

    Route::middleware(['auth:api' , 'role:admin'])->group(function (){

        Route::get('/logout' , [AuthenticationAdminController::class , 'logout']);

    });

});


Route::prefix('/student')->group(function (){
    Route::middleware(['auth:api' , 'role:student'])->group(function (){
        //home
        Route::get('/home/showFormDates' , [FormSubmissionPeriodController::class , 'getFormDataForStudent']);
        Route::get('/home/showNumbersStatistics' , [StatisticsController::class , 'getHomeStatistics']);
    });
});





Route::fallback(function (){
    return response()->json([
        'message' => 'This route not found in APIs !' ,
        'status' => 404
    ] , 404);
});



