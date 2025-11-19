<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\LoginRequest;
use Auth;

class LoginController extends Controller
{
    var $guard = 'admin';

    public function showLoginForm()
    {
        Auth::guard($this->guard)->logout();
        if(isset(Auth::guard($this->guard)->user()->id)){
            return redirect()->route('appRegistation');
        }

        return view('admin.auth.login');
    }

    public function doLogin(LoginRequest $request)
    {
        $credentials = $request->only(['email','password']);
        if (Auth::guard($this->guard)->attempt($credentials)) {
            if(Auth::guard($this->guard)->user()->is_active != 1){
                Auth::guard($this->guard)->logout();
                return redirect()->back()->withInput()->with('error','Your account has been deactivated, please contact system admin.');
            }
            return redirect()->route('appRegistation');
        }else{
            return redirect()->back()->withInput()->with('error','The credentials does not match our records');
        }
    }

    public function logout()
    {
        Auth::guard($this->guard)->logout();
        return redirect()->route('adminLogin');
    }

}
