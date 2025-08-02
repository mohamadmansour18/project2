<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\DashBoard_Services\HomeDashBoardService;
use App\Services\UserService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use ApiSuccessTrait;

    public function __construct(
        protected UserService $userService ,
        protected HomeDashBoardService $homeDashBoardService
    ) {}

    public function getUsersWithoutGroup(): JsonResponse
    {
        $students = $this->userService->getStudentsForCurrentYear();

        return $this->dataResponse(['students' => $students]);
    }

    public function showAllDoctorsForAdminHomePage(): JsonResponse
    {
        $response = $this->homeDashBoardService->getAllDoctorsForAdminHomePage();

        return $this->dataResponse($response , 200);
    }
}
