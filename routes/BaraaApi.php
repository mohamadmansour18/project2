<?php

use App\Http\Controllers\Auth\AuthenticationStudentController;
use App\Http\Controllers\Favorite\AnnouncementsController;
use App\Http\Controllers\Group\GroupController;
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
Route::prefix('/student')->group(function (){

    Route::controller(AuthenticationStudentController::class)->group(function (){

        Route::post('/register' , 'studentRegister');
        Route::post('/verifyOtp' , 'verifyStudentOtp');
        Route::post('/resendOtp' , 'resendStudentOtp')->middleware('throttle:resendOtp');

        Route::post('/login' , 'studentLogin')->middleware('throttle:login');

        Route::post('/forgetPassword/sendOtp' , 'forgotPassword')->middleware('throttle:resendOtp');
        Route::post('/forgetPassword/verifyOtp' , 'forgotPasswordOtp');
        Route::post('/forgetPassword/resetPassword' , 'resetPassword');
        Route::post('/forgetPassword/resendOtp' , 'resendPasswordResetOtp')->middleware('throttle:resendOtp');

    });
    Route::middleware(['auth:api' , 'role:student'])->group(function (){

        Route::get('/logout' , [AuthenticationStudentController::class , 'logout']);

        //home APIs
        Route::get('/home/studentannouncementStatistics' , [AnnouncementsController::class , 'getStudentAnnouncementStatistics']);


        //Group APIs
        Route::post('/createGroup', [GroupController::class, 'store']);
    });
});

