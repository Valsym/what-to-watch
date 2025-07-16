<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Регистрация юзера
     *
     * @return \Illuminate\Http\Response
     */
    public function register()
    {
        return $this->success([]);
    }

    /**
     * login
     *
     * @return \Illuminate\Http\Response
     */
    public function login()
    {
        return $this->success([]);
    }

    /**
     * logout
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        return $this->success([]);
    }
}
