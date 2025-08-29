<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckModerator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
//    public function handle0(Request $request, Closure $next): Response
//    {
//        $user = Auth::user();
//        if (!$user->isModerator()) {//} !auth()->check() || || !auth()->user()->isModerator()) {
//            return redirect('/login');
//        }
//
//        return $next($request);
//    }

    /**
     * Проверяет, является ли пользователь модератором
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next)//, string $role): Response
    {
//        if (!method_exists(User::class, $role) ) {
//            throw new \LogicException("Метод $role отсутствует у модели пользователя");
//        }

        if (!$request->user() || !$request->user()->isModerator()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
