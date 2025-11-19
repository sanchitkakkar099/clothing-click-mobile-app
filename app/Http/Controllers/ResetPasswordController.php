<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Illuminate\Http\Request;
use Hash;

class ResetPasswordController extends Controller
{
    public function resetpassword($id){
        return view('reset_password');
    }
    
     public function updatePassword()
    {
         $id = base64_decode($_REQUEST['userid']);
//         print_r($_REQUEST);
//         print_r(Auth::user());exit;
//       if (Hash::check($_REQUEST['old_password'], Auth::user()->password)) {
			 User::find($id)->update(["password" => Hash::make($_REQUEST['password'])]);
			 echo 'Password changed successfully';
			 exit;
			return redirect()->back()->with('success','Password changed successfully');
//		}else{
//			return redirect()->back()->with('error','Old password incorrect');
//		}
    }
}
