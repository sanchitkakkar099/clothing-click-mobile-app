<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\location_city;
use App\Http\Requests\Admin\AddAdminLocationCityRequest;
use App\Http\Requests\Admin\UpdateAdminLocationCityRequest;
use Config;

class ManageAdminLocationCityController extends SystemAdminController
{
    var $breadcum = ['icon'=>'mdi mdi-account-multiple','breadcum'=>['Manage Location (City)']];

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = location_city::where(function($q)use($filter){
                    if(!empty($filter['search'])){
                        $q->where('name','LIKE','%'.$filter['search'].'%');
                    }
                })
                ->orderBy('created_at','desc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_location_city.list',compact('breadcum','users','filter'));
    }

    public function create()
    {
        $breadcum = $this->breadcum;
        return view('admin.manage_location_city.create',compact('breadcum'));
    }

    public function store(AddAdminLocationCityRequest $request)
    {
        $all = $request->all();
        $all['is_active'] = 1;
        $role = location_city::create($all);
        return redirect()->route('adminLocationCity')->with('success','New admin location city added successfully.');
    }

    public function edit($id)
    {
        $user = location_city::find($id);
        $breadcum = $this->breadcum;
        return view('admin.manage_location_city.update',compact('breadcum','user'));
    }

    public function update(UpdateAdminLocationCityRequest $request)
    {
        $all = $request->all();
        location_city::find($all['uid'])->update($all);
        return redirect()->route('adminLocationCity')->with('success','Location city updated successfully.');
    }

    public function publish(Request $request)
    {
        try{
            $record = location_city::find($request->id);
            if(!empty($record)){
                return 'success';
            }
            return 'Record not found';
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public function delete($id)
    {
//        if($id == 1 || $id == 2){
//            return redirect()->back()->with('error','You can not delete admin location city');
//        }
        location_city::destroy($id);
        return redirect()->back()->with('success','Admin location city deleted successfully.');
    }
}
