<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorSearchRequest;
use App\Http\Requests\ExcelImportRequest;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorFromDashboardRequest;
use App\Jobs\ProcessDoctorExcelImportJob;
use App\Services\DashBoard_Services\HomeDashBoardService;
use App\Services\DashBoard_Services\UserManagementService;
use App\Services\UserService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    use ApiSuccessTrait;

    public function __construct(
        protected UserService $userService ,
        protected HomeDashBoardService $homeDashBoardService,
        protected UserManagementService $userManagementService,
    ) {}

    public function getUsersWithoutGroup(): JsonResponse
    {
        $students = $this->userService->getStudentsForCurrentYear();

        return $this->dataResponse(['students' => $students]);
    }

    //----------------------< DASH BOARD >----------------------//

    public function showAllDoctorsForAdminHomePage(): JsonResponse
    {
        $response = $this->homeDashBoardService->getAllDoctorsForAdminHomePage();

        return $this->dataResponse($response , 200);
    }

    public function showAllDoctorsWithProfile(): JsonResponse
    {
        $response = $this->userManagementService->getAllDoctorsDetailed();

        return $this->dataResponse($response , 200);
    }

    public function showAllStudentsWithProfile(): JsonResponse
    {
        $response = $this->userManagementService->getAllStudentsDetailed();

        return $this->dataResponse($response , 200);
    }

    public function searchDoctorsByName(DoctorSearchRequest $request): JsonResponse
    {
        $response = $this->userManagementService->searchDoctorByName($request->search);

        return $this->dataResponse($response , 200);
    }

    public function searchStudentsByName(DoctorSearchRequest $request): JsonResponse
    {
        $response = $this->userManagementService->searchStudentByName($request->search);

        return $this->dataResponse($response , 200);
    }

    public function sortDoctors(Request $request): JsonResponse
    {
        $sortValue = $request->query('sort');

        $response = $this->userManagementService->sortDoctors($sortValue);

        return $this->dataResponse($response , 200);
    }

    public function sortStudents(Request $request): JsonResponse
    {
        $sortValue = $request->query('sort' , 'university_number');

        $response = $this->userManagementService->sortStudents($sortValue);

        return $this->dataResponse($response , 200);
    }

    public function insertDoctor(StoreDoctorRequest $request): JsonResponse
    {
        $this->userManagementService->insertDoctor($request->validated());

        return $this->successResponse('تمت عملية الاضافة بنجاح !' , 'تم اضافة الدكتور المحدد الى نظام الكلية ليتولى المهام المكلف بها' , 201);
    }

    public function insertDoctors(ExcelImportRequest $request): JsonResponse
    {
        $fileName = 'doctor_import_' . Str::random(10) . '.' . $request->file('file')->getClientOriginalExtension();

        //store path = storage/app/public/temp_excel/filename.Extension
        //variable $path = temp_excel/filename.Extension
        $path = $request->file('file')->storeAs('temp_excel' , $fileName , 'public');

        $adminEmail = Auth::user()->email;

        ProcessDoctorExcelImportJob::dispatch($path , '360mohamad360@gmail.com');

        return $this->successResponse('تمت عملية الاضافة بنجاح !' , 'يتم معالجة عملية ترحيل بيانات المستخدمين الى قاعدة البيانات في الخلفية' , 201);
    }

    public function editDoctorInfoByAdmin(int $doctorId , UpdateDoctorFromDashboardRequest $request): JsonResponse
    {
        $this->userManagementService->updateDoctorInfo($doctorId , $request->validated());

        return $this->successResponse('تمت العملية بنجاح !' , 'تم تعديل بيانات الدكتور المحددة بنجاح');
    }

    public function deleteDoctorByAdmin(int $doctorId): JsonResponse
    {
        $this->userManagementService->deleteDoctorById($doctorId);

        return $this->successResponse('تمت العملية بنجاح !' , 'تم حذف الدكتور المحدد من النظام الخاص بكلية الهندسة المعلوماتية');
    }
}
