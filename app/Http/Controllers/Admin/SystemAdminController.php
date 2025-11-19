<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Crypt;
use View;
use Config;

class SystemAdminController extends Controller
{
    public function __construct()
    {
        $this->setCookies();
    }

    public function setCookies()
    {
        //Set page records
        if(!is_null(Cookie::get('pageRecords'))){
            $record = explode('|',Crypt::decrypt(Cookie::get('pageRecords'), false));
            $record = end($record);
            View::share('pageRecords', $record);
            Config::set('constants.pageRecords', $record);
        }else{
            View::share('pageRecords', 50);
            Config::set('constants.pageRecords', 50);
        }
    }

    public function setRecords($records)
    {
        if($records > 0){
            return redirect()->back()->withCookie(Cookie::forever('pageRecords', $records));
        }
        return redirect()->back();
    }
}
