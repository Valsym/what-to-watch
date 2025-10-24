<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
//use App\Services\AuthService;

class AuthController extends Controller
{
    /**
     * Регистрация юзера
     *
     * @return Response
     */
    public function register(RegisterRequest $request)
    {
        $params = $request->safe()->except('file');
        $user = User::create($params);
        $token = $user->createToken('auth_token');

        return $this->success([
            'user' => $user,
            'token' => $token->plainTextToken,
        ], 201);
    }

    /**
     * Сообщения об ошибках валидации.
     *
     * @param LoginRequest $request
     * @return \App\Http\Responses\Success
     */
        public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->validated())) {
            abort(401, trans('login.failed'));
//            throw new UnauthorizedHttpException('', 'Неверный email или пароль.');
        }

        $token = Auth::user()->createToken('auth_token');

        return $this->success(['token' => $token->plainTextToken]);
    }


    /**
     * logout
     *
     * @return \App\Http\Responses\Success
     */

    public function logout()
    {
        $user = Auth::user();
        Auth::user()->tokens()->delete();

        return $this->success(null, 204);
    }


}
