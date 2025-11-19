<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\category;
use App\gender;
use App\Http\Requests\Admin\AddAdminCategoryRequest;
use App\Http\Requests\Admin\UpdateAdminCategoryRequest;
use Config;
use Auth;

class ManageAdminCategoryController extends SystemAdminController {

    var $breadcum = ['icon' => 'mdi mdi-account-multiple', 'breadcum' => ['Manage Category']];

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = category::where(function($q)use($filter) {
                            if (!empty($filter['search'])) {
                                $q->where('name', 'LIKE', '%' . $filter['search'] . '%');
                            }
                        })->where('is_delete',0)
                        ->orderBy('id', 'asc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_category.list', compact('breadcum', 'users', 'filter'));
    }

    public function create() {
        $breadcum = $this->breadcum;
        $genderdata = gender::getList();
        $subcatdata = category::getParentList();
        return view('admin.manage_category.create', compact('breadcum', 'genderdata', 'subcatdata'));
    }

    public function store(AddAdminCategoryRequest $request) {
        $all = $request->all();
        $all['is_active'] = 1;
        $img = NULL;
            if($request->hasFile('photo')){
                $all['photo'] = UploadImage($request->file('photo'),'','','');
                 DeleteImage(Auth::guard('admin')->user()->photo);
            } if($request->hasFile('icon')){
                $all['icon'] = UploadImage($request->file('icon'),'','','');
                 DeleteImage(Auth::guard('admin')->user()->icon);
            }
        $role = category::create($all);
        return redirect()->route('adminCategory')->with('success', 'New admin category added successfully.');
    }

    public function edit($id) {
        $user = category::find($id);
        $breadcum = $this->breadcum;
        $genderdata = gender::getList();
        $subcatdata = category::getParentList();
        return view('admin.manage_category.update', compact('breadcum', 'user', 'genderdata', 'subcatdata'));
    }

    public function update(UpdateAdminCategoryRequest $request) {
        $all = $request->all();
         $img = NULL;
            if($request->hasFile('photo')){
                $all['photo'] = UploadImage($request->file('photo'),'','','');
                DeleteImage(Auth::guard('admin')->user()->photo);
            }
            if($request->hasFile('icon')){
                $all['icon'] = UploadImage($request->file('icon'),'','','');
                DeleteImage(Auth::guard('admin')->user()->icon);
            }        category::find($all['uid'])->update($all);
        return redirect()->route('adminCategory')->with('success', 'Category updated successfully.');
    }

    public function publish(Request $request) {
        try {
            $record = category::find($request->id);
            if (!empty($record)) {
                if ($record->is_active == 1) {
                    category::find($record->id)->update(['is_active' => 0]);
                } else {
                    category::find($record->id)->update(['is_active' => 1]);
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
         category::find($id)->update(['is_delete' => 1]);
//        category::destroy($id);
        return redirect()->back()->with('success', 'Admin category deleted successfully.');
    }

}
