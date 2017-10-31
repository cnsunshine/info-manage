<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class CheckIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //检测用户token
        $token = $request->header('x-token');
        if (!isset($token)) {
            return response(
                [
                    'code' => '20001',
                    'errmsg' => 'please refresh token'
                ]
            );
        }
        //token 换取uid
        $username = Redis::get($token);
        if (!isset($username)){
            return response(
                [
                    'code' => '20001',
                    'errmsg' => 'please refresh token'
                ]
            );
        }
        $request->attributes->add(['username' => $username]);
        return $next($request);
    }
}