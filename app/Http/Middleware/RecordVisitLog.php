<?php

namespace App\Http\Middleware;

use App\Service\Helper;
use App\Service\Log;
use Closure;

class RecordVisitLog
{
    /**
     * Handle an incoming request.
     * 记录访问日志，属于第一层
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $uid = Helper::getUid($request);
        Log::addVisitLog($uid, $request->route()->uri());
        return $next($request);
    }
}
