<?php

namespace App\Http\Middleware;

use Closure;

class CheckOperationIfAuthenticated
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
        $auth = $request->route()->getAction('operation');

        return $next($request);
    }
}
