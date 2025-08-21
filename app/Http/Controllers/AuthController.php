<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;

use App\Http\Responses\Fail;
use App\Http\Responses\Success;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpFoundation\Response;

//use App\Services\AuthService;
use Validator;
use Illuminate\Foundation\Http\FormRequest;

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
