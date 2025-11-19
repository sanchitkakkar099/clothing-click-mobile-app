<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\banner;
use App\category;
use App\Http\Requests\Admin\AddAdminBannerRequest;
use App\Http\Requests\Admin\UpdateAdminBannerRequest;
use Config;
use Auth;

class ManageAdminBannerController extends SystemAdminController {

    var $breadcum = ['icon' => 'mdi mdi-account-multiple', 'breadcum' => ['Manage Banners']];

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = banner::orderBy('id', 'asc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_banner.list', compact('breadcum', 'users', 'filter'));
    }

    public function create() {
        $breadcum = $this->breadcum;
         $categorydata = category::getList();
        return view('admin.manage_banner.create', compact('breadcum','categorydata'));
    }

    public function store(AddAdminBannerRequest $request) {
        $all = $request->all();
        $all['is_active'] = 1;
         $img = NULL;
            if($request->hasFile('photo')){
                $all['photo'] = UploadImage($request->file('photo'),'','','');
                 DeleteImage(Auth::guard('admin')->user()->photo);
            }
        $role = banner::create($all);
        return redirect()->route('adminBanner')->with('success', 'New admin banner added successfully.');
    }

    public function edit($id) {
        $user = banner::find($id);
        $breadcum = $this->breadcum;
         $categorydata = category::getList();
        return view('admin.manage_banner.update', compact('breadcum', 'user','categorydata'));
    }

    public function update(UpdateAdminBannerRequest $request) {
        $all = $request->all();
         $img = NULL;
            if($request->hasFile('photo')){
                $all['photo'] = UploadImage($request->file('photo'),'','','');
                DeleteImage(Auth::guard('admin')->user()->photo);
            }
        banner::find($all['uid'])->update($all);
        return redirect()->route('adminBanner')->with('success', 'Banner updated successfully.');
    }

    public function publish(Request $request) {
        try {
            $record = banner::find($request->id);
            if (!empty($record)) {
                if ($record->is_active == 1) {
                    banner::find($record->id)->update(['is_active' => 0]);
                } else {
                    banner::find($record->id)->update(['is_active' => 1]);
                }
                return 'success';
            }
            return 'Record not found';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function delete($id) {
//        if($id == 1 || $id == 2){
//            return redirect()->back()->with('error','You can not delete admin location city');
//        }
        banner::destroy($id);
        return redirect()->back()->with('success', 'Admin banner deleted successfully.');
    }

}
