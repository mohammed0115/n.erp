<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\Session;


class ExpiredMiddleware
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
        if(Auth::user()){
            if(Auth::user()->organization_id != null) {
                if (Auth::user()->expired()) {
                    return redirect(route('expierd'));
                }
            }
        }
        return $next($request);
    }
}
