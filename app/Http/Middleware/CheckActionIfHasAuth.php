<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Service\Auth;
use App\Service\Helper;
use Closure;
use Illuminate\Support\Facades\Route;

class CheckActionIfHasAuth
{
    /**
     * Handle an incoming request.
     * 该中间件用来检测用户访问动作权限
     * 用户业务权限由相应业务层检验
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws ApiException
     */
    public function handle($request, Closure $next)
    {
        $uid = $request->attributes->get('uid');
        $result = Auth::checkAuth('uri', $uid, null, null, null, $request->route()->uri());
        if (!$result){
            throw new ApiException(20000);
        }else{
            return $next($request);
        }
    }
}
