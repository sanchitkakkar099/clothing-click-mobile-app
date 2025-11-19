<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends SystemAdminController
{
    var $breadcum = ['icon'=>'mdi mdi-home','breadcum'=>['Dashboard']];
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        $breadcum = $this->breadcum;
        return view('admin.dashboard.dashboard',compact('breadcum'));
    }
}
