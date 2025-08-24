<?php

use App\Http\Controllers\Auth\AuthenticationAdminController;
use App\Http\Controllers\Auth\AuthenticationDoctorController;
use App\Http\Controllers\Conversation\ConversationController;
use App\Http\Controllers\Conversation\FAQController;
use App\Http\Controllers\Conversation\MessageController;
use App\Http\Controllers\Favorite\AnnouncementsController;
use App\Http\Controllers\FormOne\ProjectFormController;
use App\Http\Controllers\FormTwo\ProjectForm2Controller;
use App\Http\Controllers\Grade\ProjectGradeController;
use App\Http\Controllers\Group\GroupController;
use App\Http\Controllers\Interview\InterviewCommitteeController;
use App\Http\Controllers\Interview\InterviewPeriodController;
use App\Http\Controllers\Other\FormSubmissionPeriodController;
use App\Http\Controllers\Other\StatisticsController;
use App\Http\Controllers\User\UserController;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
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

//[----------------------------->>>>>>>>>>[DOCTOR APIS]<<<<<<<<<<-----------------------------]//

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
        Route::prefix('/home')->group(function () {
            Route::get('/showFormDates', [FormSubmissionPeriodController::class, 'getFormDateForDoctor']);
            Route::get('/announcementStatistics', [AnnouncementsController::class, 'getAnnouncementStatistics']);
            Route::get('/showNumbersStatistics', [StatisticsController::class, 'getHomeStatistics']);
            Route::get('/showFlowChartStatistics', [StatisticsController::class, 'getDoctorHomeGroupStatistics']);
        });

        //group APIs
        Route::prefix('/groups')->group(function (){
            Route::get('/showALlGroups' , [GroupController::class , 'showAllGroup']);
            Route::get('/showDoctorGroups' , [GroupController::class , 'showDoctorFormOneGroup']);
            Route::get('/showDoctorInterviewGroups' , [InterviewCommitteeController::class , 'showDoctorInterviewGroup']);
            Route::post('/searchGroupInterview' , [InterviewCommitteeController::class , 'showDoctorInterviewGroupSearched']);
        });

        //group details APIs
        Route::prefix('/groupDetails')->group(function (){
            Route::get('/finalInterview/{group_id}' , [GroupController::class , 'showGroupDetailsInInterview']);
            Route::post('/insertGrade' , [ProjectGradeController::class , 'insertGroupGrade']);
            Route::post('/updateGrade' , [ProjectGradeController::class , 'updateGroupGrade']);
            Route::get('/formRequest/{group_id}' , [GroupController::class , 'showGroupDetailsFormOneRequest']);
        });

        //Form One
        Route::prefix('/formOne')->group(function (){
            Route::get('/approved/{form_id}' , [ProjectFormController::class , 'approveForm']);
            Route::get('/reject/{form_id}' , [ProjectFormController::class , 'rejectForm']);
            Route::get('/download/{form_id}' , [ProjectFormController::class , 'downloadForm'])->middleware('throttle:login');
        });

        //Announcement
        Route::prefix('/announcement')->group(function (){
            Route::get('/showAllImage' , [AnnouncementsController::class , 'showAllImageAnnouncements']);
            Route::get('/showAllFile' , [AnnouncementsController::class , 'showAllFileAnnouncements']);
            Route::get('/showDoctorImage' , [AnnouncementsController::class , 'showProfessorImageAnnouncements']);
            Route::get('/showDoctorFile' , [AnnouncementsController::class , 'showProfessorFileAnnouncements']);
            Route::get('/downloadAnnouncement/{announcement_id}' , [AnnouncementsController::class , 'doctorDownloadAnnouncement'])->middleware('throttle:login');

        });
    });
});


//[----------------------------->>>>>>>>>>[ADMIN APIS]<<<<<<<<<<-----------------------------]//
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

        //HOME
        Route::prefix('/home')->group(function (){
            Route::get('/CurdStats' , [StatisticsController::class , 'getCurdStatistics']);
            Route::get('/showFormDates', [FormSubmissionPeriodController::class, 'getFormDateForDoctor']);
            Route::get('/showDoctors' , [UserController::class , 'showAllDoctorsForAdminHomePage']);

            Route::get('/getCommitteeAndPeriod' , [InterviewPeriodController::class , 'getCommitteesAndPeriods']);
        });

        //USER_MANAGEMENT
        Route::prefix('/userManagement')->group(function (){
            Route::get('/showDoctorsWithProfile' , [UserController::class , 'showAllDoctorsWithProfile']);
            Route::post('/searchDoctorByName' , [UserController::class , 'searchDoctorsByName']);
            Route::get('/sortDoctors' , [UserController::class , 'sortDoctors']);
            Route::post('/insertDoctor' , [UserController::class , 'insertDoctor']);
            Route::post('/insertDoctors' , [UserController::class , 'insertDoctors'])->middleware('throttle:dashBoard');
            Route::post('/editDoctorInfo/{doctor_id}' , [UserController::class , 'editDoctorInfoByAdmin'])->middleware('throttle:dashBoard');
            Route::delete('/deleteDoctor/{doctor_id}', [UserController::class , 'deleteDoctorByAdmin']);

            Route::get('/showStudentsWithProfile' , [UserController::class , 'showAllStudentsWithProfile']);
            Route::post('/searchStudentByName' , [UserController::class , 'searchStudentsByName']);
            Route::get('/sortStudents' , [UserController::class , 'sortStudents']);
            Route::post('/insertStudent' , [UserController::class , 'insertStudent']);
            Route::post('/insertStudents' , [UserController::class , 'insertStudents'])->middleware('throttle:dashBoard');
            Route::post('/editStudentInfo/{student_id}' , [UserController::class , 'editStudentByAdmin'])->middleware('throttle:dashBoard');
            Route::delete('/deleteStudent/{student_id}', [UserController::class , 'deleteStudentByAdmin']);
        });

        //PROJECT_MANAGEMENT
        Route::prefix('/projectManagement')->group(function (){
            //form 1
            Route::get('/getFormOne' , [FormSubmissionPeriodController::class , 'getForm1']);
            Route::post('/createFormOne' , [FormSubmissionPeriodController::class , 'createForm1']);
            Route::post('/updateFormOne/{form_id}' , [FormSubmissionPeriodController::class , 'updateForm1'])->middleware('throttle:dashBoard');
            Route::delete('/deleteFormOne/{form_id}' , [FormSubmissionPeriodController::class , 'deleteForm1'])->middleware('throttle:dashBoard');

            //form 2
            Route::get('/getFormTwo' , [FormSubmissionPeriodController::class , 'getForm2']);
            Route::post('/createFormTwo' , [FormSubmissionPeriodController::class , 'createForm2']);
            Route::post('/updateFormTwo/{form_id}' , [FormSubmissionPeriodController::class , 'updateForm2'])->middleware('throttle:dashBoard');
            Route::delete('/deleteFormTwo/{form_id}' , [FormSubmissionPeriodController::class , 'deleteForm2'])->middleware('throttle:dashBoard');

            //Interview
            Route::get('/getFormInterview' , [FormSubmissionPeriodController::class , 'getFormInterview']);
            Route::post('/createFormInterview' , [FormSubmissionPeriodController::class , 'createFormInterview']);
            Route::post('/updateFormInterview/{interPeriod_id}' , [FormSubmissionPeriodController::class , 'updateFormInterview'])->middleware('throttle:dashBoard');
            Route::delete('/deleteFormInterview/{interPeriod_id}' , [FormSubmissionPeriodController::class , 'deleteFormInterview'])->middleware('throttle:dashBoard');

            Route::get('/downloadFormDate' , [FormSubmissionPeriodController::class , 'generateAndDownload'])->middleware('throttle:dashBoard');

            //Interview Committee
            Route::get('/showAvailableDoctors' , [InterviewCommitteeController::class , 'showAvailableDoctorsNotInCommittee']);
            Route::post('/createInterviewCommittee' , [InterviewCommitteeController::class , 'createInterviewCommittee']);
            Route::get('/showInterviewCommittee' , [InterviewCommitteeController::class , 'getInterviewCommittee']);
            Route::delete('/deleteInterviewCommittee/{committee_id}' , [InterviewCommitteeController::class , 'deleteInterviewCommittee']);
            Route::get('/notifyCommittee' , [InterviewCommitteeController::class , 'notifyCommittee'])->middleware('throttle:dashBoard');
            Route::get('/downloadCommittee', [InterviewCommitteeController::class , 'generateAndDownloadCommittee'])->middleware('throttle:dashBoard');

            //FAQs
            Route::get('/showFAQs' , [FAQController::class , 'showFAQs']);
            Route::post('/createFAQs' , [FAQController::class , 'createFAQ']);
            Route::delete('/deleteFAQs/{question_id}' , [FAQController::class , 'deleteFAQ']);
        });

        //GROUP_MANAGEMENT
        Route::prefix('/groupManagement')->group(function (){
            Route::get('/showGroupDetails/{group_id}' , [GroupController::class , 'showGroupDetails']);
            Route::get('/downloadFormOne/{group_id}' , [ProjectFormController::class , 'downloadFormWeb'])->middleware('throttle:dashBoard');
            Route::get('/downloadFormTwo/{group_id}' , [ProjectForm2Controller::class , 'downloadFormWeb'])->middleware('throttle:dashBoard');
        });

        //GRADE_MANAGEMENT
        Route::prefix('/gradeManagement')->group(function (){
            Route::get('/showGrades' , [ProjectGradeController::class , 'getGrades']);
            Route::get('/downloadGrades' , [ProjectGradeController::class , 'downloadGrades']);
        });
    });

});

//[----------------------------->>>>>>>>>>[STUDENT APIS]<<<<<<<<<<-----------------------------]//
Route::prefix('/student')->group(function (){
    Route::middleware(['auth:api' , 'role:student'])->group(function (){
        //home
        Route::get('/home/showFormDates' , [FormSubmissionPeriodController::class , 'getFormDataForStudent']);
        Route::get('/home/showNumbersStatistics' , [StatisticsController::class , 'getHomeStatistics']);

        //Conversation
        Route::get('/showConversations' , [ConversationController::class , 'showStudentConversations']);
        Route::get('/showConversationOption' , [ConversationController::class , 'selectUserToStartConversation']);
        Route::get('/createConversation/{otherUserId}' , [ConversationController::class , 'createConversation']);
        Route::post('/search' , [ConversationController::class , 'searchConversation']);

        Route::post('/showMessages/{conversation_id}' , [MessageController::class , 'showMessages']);
    });
});


Route::get('/test-fcm', function (FirebaseNotificationService $fcm) {
    try {
        $fcm->send(
            'hellooooo aboooooddddd',
            'Test',
            ['cmPxVuMxTtqUaT5z28ocbL:APA91bGr5yWRwRKWMGcfjlheRU_i8EI4Csti5ETW1Eqi7I9dQhzoQdtKRo-Dq6StZ6BmDJ5feOYUTyMUtirBhriyYHcYT2cg4fr72LoVSJY8601rJDLmc8g']
        );
        return "تمت العملية بنجاح";
    }catch (\Exception $e)
    {
        return response()->json([
            'title' => "خطا اتصال من الشبكة!",
            'body' => $e->getMessage(),
        ], 422);
    }
});
//
//Route::get('/test' , function(){
//    $user = DatabaseNotification::first();
//    return response()->json(['title' => $user->data['title']]);
//});



Route::fallback(function (){
    return response()->json([
        'message' => 'This route not found in APIs !' ,
        'status' => 404
    ] , 404);
});



