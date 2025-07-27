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


    public function register3(FormRequest $request): Success
    {
        $rules = RegisterRequest::rules();

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            abort(401, 'register.failed')->withErrors($validator);//trans('auth.failed'))
        }

//        $params = $request->safe(['email', 'password']);
        $params = $request->safe()->except('file');
        $user = User::create($params);
        $token = $user->createToken('auth_token');

        return $this->success([
            'user' => $user,
            'token' => $token->plainTextToken,
        ], 201);
    }

    /**
     * login
     *
     * @return \Illuminate\Http\Response
     */
    public function login000(Request $request)
    {
        $rule = [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            abort(401, 'auth.failed')->withErrors($validator);//trans('auth.failed'))
//            return redirect('post/create')
//                ->withErrors($validator);
                //->withInput();
        }



//        $validatedData = $request->validate($rule);
        $t = 3;
        $pars = $request->all();
////        $user = User::where('email', $pars['email']);
//        $db_pass = DB::table('users')->where('email', $pars['email'])->value('password');
//        if (Hash::check('passwordToCheck', $user->password)) {
//            // Success
//        }

        $email = $pars['email'];
        //$user = User::find($email);
        $user = User::where('email', $email)->first();
        $id = $user->id ?? 'no';
        $hasher = app('hash');
        if ($id && $hasher->check($pars['password'], $user->password)) {
            // Success
            $t = 1;
//            $token = Auth::user()->createToken('auth-token');
            $token = $user->createToken('auth_token');

            return $this->success(['token' => $token->plainTextToken]);
        }

        $t = 12;
        $t++;
        return new Fail(
            message: 'Переданные данные не корректны',
            data: [
                'email' => ['Неверный email или пароль.'],
                'password' => ['Неверный email или пароль.']
            ],
            code: Response::HTTP_UNAUTHORIZED
        );


        $t = 12;
        $t++;
        $token = Auth::user()->createToken('auth-token');

        return $this->success(['token' => $token->plainTextToken]);
    }
    public function login2(Request $request): Success|Fail
    {

        try {
            $token = $this->authService->loginUser($request->validated(
                [
                    'email' => ['required', 'email'],
                    'password' => ['required', 'string'],
                ]
            ));
            $t = 3;
            $t++;


            return $this->success(['token' => $token]);
//            $token = Auth::user()->createToken('auth-token');
//
//            return $this->success(['token' => $token->plainTextToken]);
        } catch (UnauthorizedHttpException $e) {
            $t = 5;
            $t++;
            abort(401, trans('auth.failed'));
            return new Fail(
                message: '2Переданные данные не корректны '. $e,
                data: [
                    'email' => ['Неверный email или пароль.'],
                    'password' => ['Неверный email или пароль.']
                ],
                code: Response::HTTP_UNAUTHORIZED
            );
        }
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
