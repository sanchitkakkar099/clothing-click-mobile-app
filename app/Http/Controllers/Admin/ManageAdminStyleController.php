<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\condition;
use App\category;
use App\style;
use App\Http\Requests\Admin\AddAdminConditionRequest;
use App\Http\Requests\Admin\UpdateAdminConditionRequest;
use Config;

class ManageAdminStyleController extends SystemAdminController {

    var $breadcum = ['icon' => 'mdi mdi-account-multiple', 'breadcum' => ['Manage Style']];

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = style::where(function($q)use($filter) {
                            if (!empty($filter['search'])) {
                                $q->where('name', 'LIKE', '%' . $filter['search'] . '%');
                            }
                        })->where('is_delete',0)
                        ->orderBy('id', 'asc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_style.list', compact('breadcum', 'users', 'filter'));
    }

    public function create() {
        $breadcum = $this->breadcum;
         $categorydata = category::getList(); 
        return view('admin.manage_style.create', compact('breadcum','categorydata'));
    }

    public function store(Request $request) {
        $all = $request->all();
        $all['is_active'] = 1;
        $role = style::create($all);
        return redirect()->route('adminStyle')->with('success', 'New admin style added successfully.');
    }

    public function edit($id) {
        $user = style::find($id);
        $breadcum = $this->breadcum;
         $categorydata = category::getList();
        return view('admin.manage_style.update', compact('breadcum', 'user','categorydata'));
    }

    public function update(Request $request) {
        $all = $request->all();
        style::find($all['uid'])->update($all);
        return redirect()->route('adminStyle')->with('success', 'Style updated successfully.');
    }

    public function publish(Request $request) {
        try {
            $record = style::find($request->id);
            if (!empty($record)) {
                if ($record->is_active == 1) {
                    style::find($record->id)->update(['is_active' => 0]);
                } else {
                    style::find($record->id)->update(['is_active' => 1]);
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
        style::find($id)->update(['is_delete' => 1]);
//        condition::destroy($id);
        return redirect()->back()->with('success', 'Admin Style deleted successfully.');
    }

}
