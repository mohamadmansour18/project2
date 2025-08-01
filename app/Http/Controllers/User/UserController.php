<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use ApiSuccessTrait;

    public function __construct(private UserService $userService) {}

    public function getUsersWithoutGroup(): JsonResponse
    {
        $students = $this->userService->getStudentsForCurrentYear();

        return $this->dataResponse(['students' => $students]);
    }
}
