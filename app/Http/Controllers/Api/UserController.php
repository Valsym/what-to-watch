<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Responses\Success;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function show(): Success
    {
        $user = $this->authService->getCurrentUser(auth()->id());

        return $this->success(['user' => $user]);
    }

    public function update(RegisterRequest $request): Success
    {
        $data = $request->safe()->except('file');
        $avatar = $request->file('file');

        $user = $this->authService->updateUser(auth()->id(), $data, $avatar);

        return $this->success(['user' => $user]);
    }
}
