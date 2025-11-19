<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddAdminBannerRequest;
use App\Http\Requests\Admin\AddAdminBrandRequest;
use App\Http\Requests\Admin\UpdateAdminBannerRequest;
use App\Http\Requests\Admin\UpdateAdminBrandRequest;
use App\banner;
use App\brand;
use App\category;
use App\roles;
use Auth;
use Config;
use Illuminate\Http\Request;

class ManageAdminBrandController extends SystemAdminController {

    var $breadcum = ['icon' => 'mdi mdi-account-multiple', 'breadcum' => ['Manage Brands']];

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = brand::where(function($q)use($filter) {
                            if (!empty($filter['search'])) {
                                $q->where('name', 'LIKE', '%' . $filter['search'] . '%');
                            }
                        })->orderBy('id', 'asc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_brand.list', compact('breadcum', 'users', 'filter'));
    }

    public function create() {
        $breadcum = $this->breadcum;
         $categorydata = category::getList();
        return view('admin.manage_brand.create', compact('breadcum','categorydata'));
    }

    public function store(AddAdminBrandRequest $request) {
        $all = $request->all();
        $all['is_active'] = 1;
         $img = NULL;
            if($request->hasFile('photo')){
                $all['photo'] = UploadImage($request->file('photo'),'','','');
            }
        $brand = brand::create($all);
        return redirect()->route('adminBrand')->with('success', 'New brand added successfully.');
    }

    public function edit($id) {
        $user = brand::find($id);
        $breadcum = $this->breadcum;
         $categorydata = category::getList();
        return view('admin.manage_brand.update', compact('breadcum', 'user','categorydata'));
    }

    public function update(UpdateAdminBrandRequest $request) {
        $all = $request->all();
         $img = NULL;
            if($request->hasFile('photo')){
                $all['photo'] = UploadImage($request->file('photo'),'','','');
                DeleteImage(Auth::guard('admin')->user()->photo);
            }
        brand::find($all['uid'])->update($all);
        return redirect()->route('adminBrand')->with('success', 'Brand updated successfully.');
    }

    public function publish(Request $request) {
        try {
            $record = brand::find($request->id);
            if (!empty($record)) {
                if ($record->is_active == 1) {
                    brand::find($record->id)->update(['is_active' => 0]);
                } else {
                    brand::find($record->id)->update(['is_active' => 1]);
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
        brand::destroy($id);
        return redirect()->back()->with('success', 'Admin Brand deleted successfully.');
    }

}
