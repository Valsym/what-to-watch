<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

//use App\Services\AuthService;

class AuthController extends Controller
{
    /**
     * Регистрация юзера
     *
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        $user = Auth::user();
        Auth::user()->tokens()->delete();

        return $this->success(null, 204);
    }


}
