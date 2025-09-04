<?php

use App\Http\Controllers\Auth\AuthenticationStudentController;
use App\Http\Controllers\Favorite\AnnouncementsController;
use App\Http\Controllers\Favorite\FavoriteController;
use App\Http\Controllers\FormOne\ProjectFormController;
use App\Http\Controllers\FormTwo\ProjectForm2Controller;
use App\Http\Controllers\Group\GroupController;
use App\Http\Controllers\Group\GroupInvitationController;
use App\Http\Controllers\Group\GroupMemberController;
use App\Http\Controllers\Group\JoinRequestController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\UserController;
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
        Route::get('/groups/top-projects', [GroupController::class, 'topProjects']);
        Route::get('/home/studentannouncementStatistics' , [AnnouncementsController::class , 'getStudentAnnouncementStatistics']);
        Route::get('/announcements/students/statistics', [AnnouncementsController::class, 'getStudentAnnouncementsStatistics']);

        Route::middleware(['leader'])->group(function () {
            //Group APIs
            Route::post('/updateGroup/{group}', [GroupController::class, 'update']);
            Route::post('/groups/{group}/change-leader', [GroupController::class, 'ChangeLeadership']);

            //invitations APIs
            Route::post('/groups/{group}/invitation/join', [GroupInvitationController::class, 'store']);


            //join request APIs
            Route::get('{group}/join-requests', [JoinRequestController::class, 'index']);
            Route::post('join-request/{id}/accept', [JoinRequestController::class, 'accept']);
            Route::post('join-request/{id}/reject', [JoinRequestController::class, 'reject']);

            //form 1 APIs
            Route::post('/project-form-one/{form}/submit', [ProjectFormController::class, 'submit']);

        });

        //Group APIs
        Route::post('/createGroup', [GroupController::class, 'store']);
        Route::get('/showGroupInfo/{group}', [GroupController::class, 'show']);
        Route::get('/groups/incomplete/public', [GroupController::class, 'getIncompletePublicGroups']);
        Route::get('/my-group', [GroupController::class, 'myGroup']);
        Route::get('/showGroupInfoPublic/{groupId}/details', [GroupController::class, 'showPublic']);
        Route::get('/my-group-details', [GroupController::class, 'myGroupDetails']);
        Route::get('/groups/five-members', [GroupController::class, 'groupsWithFiveMembers']);
        Route::get('/groups/my/project', [GroupController::class, 'showMyGroupProject']);
        Route::get('/groups/{id}/project', [GroupController::class, 'showGroupProject']);
        Route::delete('/groups/{groupId}/leave', [GroupController::class, 'leave']);


        //group member APIs
        Route::get('/my-group-members', [GroupMemberController::class, 'myGroupMembers']);
        Route::get('/my-group/members', [GroupMemberController::class, 'myGroupMembersFormOne']);

        //invitations APIs
        Route::get('/groups/invitations/user', [GroupInvitationController::class, 'index']);
        Route::get('groups/{group}/pending-invitations', [GroupInvitationController::class, 'pendingInvitations']);
        Route::post('/invitations/{invitation}/accept', [GroupInvitationController::class, 'accept']);
        Route::post('/invitations/{invitation}/reject', [GroupInvitationController::class, 'reject']);
        Route::delete('/invitations/{invitedUser}/cancel', [GroupInvitationController::class, 'cancel']);

        //join request APIs
        Route::post('{group}/join-request', [JoinRequestController::class, 'store']);
        Route::get('/join-requests/my', [JoinRequestController::class, 'myRequests']);
        Route::post('join-request/group/{groupId}/cancel', [JoinRequestController::class, 'cancelByGroup']);


        //users APIs
        Route::get('/students-without-group', [UserController::class, 'getUsersWithoutGroup']);
        Route::get('/doctors', [UserController::class, 'index']);

        //form 1 APIs
        Route::post('/project-form-one', [ProjectFormController::class, 'store']);
        Route::post('/project-form-one/{form}', [ProjectFormController::class, 'update']);
        Route::post('/project-form-one/{form}/sign', [ProjectFormController::class, 'sign']);
        Route::get('/form-1/{form}/download', [ProjectFormController::class, 'downloadForm']);
        Route::get('/form-1/{form}/preview', [ProjectFormController::class, 'preview']);


        //form 2 APIs
        Route::post('/project-form-two', [ProjectForm2Controller::class, 'store']);
        Route::get('/form-2/{form}/download', [ProjectForm2Controller::class, 'download']);
        Route::get('/form-2/{form}/preview', [ProjectForm2Controller::class, 'preview']);

        //Sixth student APIs
        Route::post('/groups/{groupId}/join-request-sixth', [JoinRequestController::class, 'storeSixthMemberRequest']);

        //Announcement APIs
        Route::prefix('announcement')->group(function () {
            Route::get('/{announcement}/download', [AnnouncementsController::class, 'download']);
            Route::get('/{announcement}/preview', [AnnouncementsController::class, 'preview']);
            Route::get('/images', [AnnouncementsController::class, 'images']);
            Route::get('/files', [AnnouncementsController::class, 'files']);
            Route::get('/images/latest', [AnnouncementsController::class, 'latestImages']);
            Route::get('/files/latest', [AnnouncementsController::class, 'latestFiles']);
            Route::get('/last-year/images', [AnnouncementsController::class, 'lastYearImages']);
            Route::get('/last-year/files', [AnnouncementsController::class, 'lastYearFiles']);
        });

        //favorites APIs
        Route::prefix('favorites')->group(function () {
            Route::get('/images', [FavoriteController::class, 'imageFavorites']);
            Route::get('/files', [FavoriteController::class, 'fileFavorites']);
            Route::post('/{announcement}', [FavoriteController::class, 'store']);
            Route::delete('/{announcement}', [FavoriteController::class, 'destroy']);
        });

        //Profile APIs
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::get('/users/{user}/profile', [ProfileController::class, 'showUser']);
        Route::post('/profile', [ProfileController::class, 'updateProfile']);

    });
});

Route::prefix('/doctor')->group(function (){

    Route::middleware(['auth:api' , 'role:doctor'])->group(function (){

        //Announcement APIs
        Route::prefix('announcement')->group(function () {
            Route::get('/{announcement}/download', [AnnouncementsController::class, 'download']);
            Route::get('/{announcement}/preview', [AnnouncementsController::class, 'preview']);
            Route::get('/images/current-year', [AnnouncementsController::class, 'getCurrentYearImages']);
            Route::get('/files/current-year', [AnnouncementsController::class, 'getCurrentYearFiles']);
            Route::get('/images/professor', [AnnouncementsController::class, 'getAdminImages']);
            Route::get('/files/professor', [AnnouncementsController::class, 'getAdminFiles']);
            Route::get('/images/latest', [AnnouncementsController::class, 'getLatestImages']);
            Route::get('/files/latest', [AnnouncementsController::class, 'getLatestFiles']);
        });

        //favorites APIs
        Route::prefix('favorites')->group(function () {
            Route::get('/images', [FavoriteController::class, 'imageFavorites']);
            Route::get('/files', [FavoriteController::class, 'fileFavorites']);
            Route::post('/{announcement}', [FavoriteController::class, 'store']);
            Route::delete('/{announcement}', [FavoriteController::class, 'destroy']);
        });

    });
});

Route::prefix('/admin')->group(function (){
    Route::middleware(['auth:api' , 'role:admin'])->group(function (){

        //Sixth student APIs
        Route::get('/requests',[JoinRequestController::class, 'headRequests']);
        Route::post('/requests/{requestId}/approve', [JoinRequestController::class, 'headApprove']);
        Route::post('/requests/{requestId}/reject',  [JoinRequestController::class, 'headReject']);

        //Announcement APIs
        Route::prefix('announcement')->group(function () {
            Route::post('/', [AnnouncementsController::class, 'store']);
            Route::delete('/{announcement}', [AnnouncementsController::class, 'destroy']);
            Route::get('/{announcement}/download', [AnnouncementsController::class, 'download']);
            Route::get('/{announcement}/preview', [AnnouncementsController::class, 'preview']);
            Route::get('/images/current-year', [AnnouncementsController::class, 'getCurrentYearImages']);
            Route::get('/files/current-year', [AnnouncementsController::class, 'getCurrentYearFiles']);
            Route::get('/latest', [AnnouncementsController::class, 'getLatestAnnouncements']);
        });

        //groups APIs
        Route::get('/groups', [GroupController::class, 'index']);
        Route::get('/groups/search', [GroupController::class, 'search']);
    });
});

