<?php

use App\Http\Controllers\Auth\AuthenticationStudentController;
use App\Http\Controllers\Favorite\AnnouncementsController;
use App\Http\Controllers\Group\GroupController;
use App\Http\Controllers\Group\GroupInvitationController;
use App\Http\Controllers\Group\JoinRequestController;
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

        Route::middleware(['leader'])->group(function () {
            //Group APIs
            Route::post('/updateGroup/{group}', [GroupController::class, 'update']);
            Route::post('/groups/{group}/change-leader', [GroupController::class, 'ChangeLeadership']);

            //invitations APIs
            Route::post('/groups/{group}/invitation/join', [GroupInvitationController::class, 'store']);
            Route::delete('/invitations/{invitation}/cancel', [GroupInvitationController::class, 'cancel']);

            //join request APIs
            Route::get('{group}/join-requests', [JoinRequestController::class, 'index']);
            Route::post('join-request/{id}/accept', [JoinRequestController::class, 'accept']);
            Route::post('join-request/{id}/reject', [JoinRequestController::class, 'reject']);
        });

        //Group APIs
        Route::post('/createGroup', [GroupController::class, 'store']);
        Route::get('/showGroupInfo/{group}', [GroupController::class, 'show']);

        //invitations APIs
        Route::get('/groups/invitations/user', [GroupInvitationController::class, 'index']);
        Route::get('groups/{group}/pending-invitations', [GroupInvitationController::class, 'pendingInvitations']);
        Route::post('/invitations/{invitation}/accept', [GroupInvitationController::class, 'accept']);
        Route::post('/invitations/{invitation}/reject', [GroupInvitationController::class, 'reject']);

        //join request APIs
        Route::post('{group}/join-request', [JoinRequestController::class, 'store']);
        Route::get('/join-requests/my', [JoinRequestController::class, 'myRequests']);
        Route::post('join-request/{id}/cancel', [JoinRequestController::class, 'cancel']);

    });
});

