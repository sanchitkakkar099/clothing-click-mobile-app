<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\email_otp_requests;
use App\User;

class VerifyEmailController extends Controller
{
    public function verify($userId,$otp)
    {
        $userId = decrypt($userId);
        $otp = decrypt($otp);

        $checkOtp = email_otp_requests::where([['user_id',$userId],['otp',$otp]])->orderby('id','desc')->first();

        if(!empty($checkOtp)){
            User::find($userId)->update(['email_verified_at' => date('Y-m-d H:i:s')]);
            email_otp_requests::destroy($checkOtp->id);
            dd('Success');
        }

        dd('Failed');
    }
    
}
