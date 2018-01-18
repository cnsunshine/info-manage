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
        $uid = Helper::getUid($request);
        if (!$uid){
            throw new ApiException(20001);
        }
        $request->attributes->add(['uid' => $uid]);
        return $next($request);
    }
}
