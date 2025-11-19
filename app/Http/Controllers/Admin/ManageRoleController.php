<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\admins;
use App\role_permissions;
use Cache;
use App\Http\Requests\Admin\AddRoleRequest;

class ManageRoleController extends SystemAdminController
{
    var $breadcum = ['icon'=>'mdi mdi-account-card-details','breadcum'=>['Manage Roles']];

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $breadcum = $this->breadcum;
        $roles = roles::getList();
        return view('admin.manage_roles.list',compact('breadcum','roles'));
    }

    public function create()
    {
        $breadcum = $this->breadcum;
        return view('admin.manage_roles.create',compact('breadcum'));
    }

    public function store(AddRoleRequest $request)
    {
        $all = $request->all();
        $all['is_active'] = 1;
        $role = roles::create($all);
        $route = route('adminAccessRights').'?role='.base64_encode($role->id);
        return redirect($route);
    }

    public function edit($id)
    {
        $role = roles::find($id);
        $breadcum = $this->breadcum;
        return view('admin.manage_roles.update',compact('breadcum','role'));
    }

    public function update(AddRoleRequest $request)
    {
        $all = $request->all();
        roles::find($all['rid'])->update(['name'=>$all['name']]);
        return redirect()->route('adminRoleManager')->with('success','Role updated successfully.');
    }

    public function publish(Request $request)
    {
        try{
            $record = roles::find($request->id);
            if(!empty($record)){
                if($record->is_active == 1){
                    roles::find($record->id)->update(['is_active'=>0]);
                    admins::where('role_id',$record->id)->update(['is_active'=>0]);
                }else{
                    roles::find($record->id)->update(['is_active'=>1]);
                }
                return 'success';
            }
            return 'Record not found';
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public function delete($id)
    {
        if($id == 1 || $id == 2){
            return redirect()->back()->with('error','You can not delete admin role');
        }
        admins::where('role_id',$id)->delete();
        role_permissions::where('role_id',$id)->delete();
        roles::destroy($id);
        Cache::forget('permissionArray');
        return redirect()->back()->with('success','Role deleted successfully.');
    }
}
