<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::guest()){
            $user = new User();
            $user->usuario = uniqid();
            $user->password = Hash::make(uniqid());
            $user->salt = uniqid();
            $user->registrado = 0;
            $user->save();

            Auth::loginUsingId($user->id);
        }
        return $next($request);
    }
}
