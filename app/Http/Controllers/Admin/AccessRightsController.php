<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\modules;
use App\role_permissions;
use App\permissions;
use DB;
use Cache;

class AccessRightsController extends SystemAdminController
{
    var $breadcum =  ['icon'=>'mdi mdi-access-point','breadcum'=>['Access Rights']];

    public function __construct()
    {
        parent::__construct();
    }
    
    public function index(Request $request)
    {
        $breadcum = $this->breadcum;
        $roles = roles::getList();
        $modules = [];
        $role = NULL;

        if(!empty($request->role)){
            //Get access
            $role = roles::find(base64_decode($request->role));
            $modules =  modules::getList();
        }

        return view('admin.access_rights.access_rights',compact('breadcum','role','modules','roles'));
    }

    public function update(Request $request)
    {
        try{
            $rights = $request->rights;
            $role = base64_decode($request->role);
            if(!empty($role)){
               DB::beginTransaction();
               
               role_permissions::where('role_id',$role)->delete();

               if(!empty($rights) && is_array($rights)){
                    foreach($rights as $key => $value){

                        $value = explode(',',$value);

                        $module = modules::find($value[0]);

                        if(!empty($module)){

                            $permission = permissions::where('per_slug',$module->module_slug.'-'.$value[1])->orderBy('id','desc')->first();

                            if(!empty($permission)){
                                $createRolePermission = [
                                    'role_id' => $role,
                                    'permission_id' => $permission->id
                                ];
                                role_permissions::create($createRolePermission);
                            }
                        }
                    }
               }
                DB::commit();
                Cache::forget('permissionArray');
                return redirect()->back()->with('success','Access rights updated successfully.');
            }else{
                return redirect()->back()->with('error','Invalid input, please try again');
            }
        }catch(\Exception $e){
            DB::rollback();
            abort(500);
        }
    }
}
