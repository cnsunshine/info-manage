<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Service\Helper;
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
     * @throws ApiException
     */
    public function handle($request, Closure $next)
    {
        //检测用户token
        $token = $request->header('x-token');
        if (!isset($token)){
            $token = cookie('x-token');
            //如无检测cookie中token
            if (!isset($token)){
                throw new ApiException(20001);
            }
        }
        //token 换取uid
        $uid = Redis::get($token);
        if (!isset($uid)){
            throw new ApiException(20001);
        }
        $request->attributes->add(['uid' => $uid]);
        return $next($request);
    }
}
