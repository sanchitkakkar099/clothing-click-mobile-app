<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class AdminPermission
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
        $perFlag = 0;
        if(Auth::guard($guard)->user()->role_id == 1){
            $perFlag = 1;
        }else{
            if(isset($request->segments()[1])){
        
                $askPermission = $request->segments()[1].'-list';
                if(isset($request->segments()[2])){
                    $askPermission = $request->segments()[1].'-'.$request->segments()[2];
                }

                if(ManagePermission($askPermission)){
                    $perFlag = 1;
                }
            }
        }
        
        if(empty($perFlag)){
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
