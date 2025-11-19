<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Illuminate\Http\Request;
use Hash;

class CMSController extends Controller
{
    public function terms(){
        return view('terms');
    }
    public function privacy(){
        return view('privacy');
    }
    public function cookies(){
        return view('cookies');
    }
}
