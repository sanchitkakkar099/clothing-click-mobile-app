<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\UpdateUserProfileRequest;
use App\Http\Requests\Admin\UpdatePasswordRequest;
use App\admins;
use Auth;
use Hash;

class UserProfileController extends SystemAdminController
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        $breadcum = ['icon'=>'mdi mdi-account','breadcum'=>['User Profile']];
        return view('admin.user_profile.user_profile',compact('breadcum'));
    }

    public function update(UpdateUserProfileRequest $request)
    {
        try{
            $all = $request->all();
            $img = NULL;
            if($request->hasFile('photo')){
                $all['image_id'] = UploadImage($request->file('photo'),'user_profile','user_profile',1);
                DeleteImage(Auth::guard('admin')->user()->image_id);
            }
            admins::find(Auth::guard('admin')->user()->id)->update($all);
            return redirect()->back()->with('success','Profile updated successfully.');
        }catch(\Exception $e){
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function changePassword()
    {
        $breadcum = ['icon'=>'mdi mdi-account-key','breadcum'=>['Change Password']];
        return view('admin.user_profile.change_password',compact('breadcum'));
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        if(Hash::check($request->old_password,Auth::guard('admin')->user()->password)){
			admins::find(Auth::guard('admin')->user()->id)->update(["password"=>Hash::make($request->password)]);
			return redirect()->back()->with('success','Password changed successfully');
		}else{
			return redirect()->back()->with('error','Old password incorrect');
		}
    }
}