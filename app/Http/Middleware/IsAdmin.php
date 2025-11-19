<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'admin')
    {
        if(!isset(Auth::guard($guard)->user()->id)){
            return redirect()->route('adminLogin');
        }

        if(Auth::guard($guard)->user()->is_active != 1){
            Auth::guard($guard)->logout();
            return redirect()->route('adminLogin')->with('error','Your account has been deactivated, please contact system admin.');
        }

        return $next($request);
    }
}
