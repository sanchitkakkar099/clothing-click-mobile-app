<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\color;
use App\category;
use App\Http\Requests\Admin\AddAdminColorRequest;
use App\Http\Requests\Admin\UpdateAdminColorRequest;
use Config;
use Auth;

class ManageAdminColorController extends SystemAdminController {

    var $breadcum = ['icon' => 'mdi mdi-account-multiple', 'breadcum' => ['Manage Colors']];

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = color::where(function($q)use($filter) {
                            if (!empty($filter['search'])) {
                                $q->where('name', 'LIKE', '%' . $filter['search'] . '%');
                            }
                        })->where('is_delete',0)
                        ->orderBy('id', 'asc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_color.list', compact('breadcum', 'users', 'filter'));
    }

    public function create() {
        $breadcum = $this->breadcum;
         $categorydata = category::getList();
        return view('admin.manage_color.create', compact('breadcum','categorydata'));
    }

    public function store(AddAdminColorRequest $request) {
        $all = $request->all();
        $all['is_active'] = 1;
        $img = NULL;
            if($request->hasFile('photo')){
                $all['photo'] = UploadImage($request->file('photo'),'','','');
                 DeleteImage(Auth::guard('admin')->user()->photo);
            }
        $role = color::create($all);
        return redirect()->route('adminColor')->with('success', 'New admin color added successfully.');
    }

    public function edit($id) {
        $user = color::find($id);
        $breadcum = $this->breadcum;
         $categorydata = category::getList();
        return view('admin.manage_color.update', compact('breadcum', 'user','categorydata'));
    }

    public function update(UpdateAdminColorRequest $request) {
        $all = $request->all();
         $img = NULL;
            if($request->hasFile('photo')){
                $all['photo'] = UploadImage($request->file('photo'),'','','');
                DeleteImage(Auth::guard('admin')->user()->photo);
            }
        color::find($all['uid'])->update($all);
        return redirect()->route('adminColor')->with('success', 'Color updated successfully.');
    }

    public function publish(Request $request) {
        try {
            $record = color::find($request->id);
            if (!empty($record)) {
                if ($record->is_active == 1) {
                    color::find($record->id)->update(['is_active' => 0]);
                } else {
                    color::find($record->id)->update(['is_active' => 1]);
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
        color::find($id)->update(['is_delete' => 1]);
//        color::destroy($id);
        return redirect()->back()->with('success', 'Admin color deleted successfully.');
    }

}
