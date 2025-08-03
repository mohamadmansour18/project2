<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorSearchRequest;
use App\Http\Requests\StoreDoctorRequest;
use App\Services\DashBoard_Services\HomeDashBoardService;
use App\Services\DashBoard_Services\UserManagementService;
use App\Services\UserService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function searchDoctorsByName(DoctorSearchRequest $request): JsonResponse
    {
        $response = $this->userManagementService->searchDoctorByName($request->search);

        return $this->dataResponse($response , 200);
    }

    public function sortDoctors(Request $request): JsonResponse
    {
        $sortValue = $request->query('sort');

        $response = $this->userManagementService->sortDoctors($sortValue);

        return $this->dataResponse($response , 200);
    }

    public function insertDoctor(StoreDoctorRequest $request): JsonResponse
    {
        $this->userManagementService->insertDoctor($request->validated());

        return $this->successResponse('تمت عملية الاضافة بنجاح !' , 'تم اضافة الدكتور المحدد الى نظام الكلية ليتولى المهام المكلف بها' , 201);
    }
}
