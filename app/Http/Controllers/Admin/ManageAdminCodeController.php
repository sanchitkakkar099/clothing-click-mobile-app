<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddAdminCodeRequest;
use App\Http\Requests\Admin\UpdateAdminCodeRequest;
use App\category;
use App\code;
use App\roles;
use Config;
use Illuminate\Http\Request;

class ManageAdminCodeController extends SystemAdminController {

    var $breadcum = ['icon' => 'mdi mdi-account-multiple', 'breadcum' => ['Manage Access Codes']];

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = code::with('users')->where(function($q)use($filter) {
                            if (!empty($filter['search'])) {
                                $q->where('name', 'LIKE', '%' . $filter['search'] . '%');
                            }
                        })->where('is_delete',0)
                        ->orderBy('id', 'asc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_code.list', compact('breadcum', 'users', 'filter'));
    }

    public function create() {
        $breadcum = $this->breadcum;
         $categorydata = category::getList();
        return view('admin.manage_code.create', compact('breadcum','categorydata'));
    }

    public function store(AddAdminCodeRequest $request) {
        $all = $request->all();
        $all['is_active'] = 1;
        $role = code::create($all);
        return redirect()->route('adminCode')->with('success', 'New admin code added successfully.');
    }

    public function edit($id) {
        $user = code::find($id);
        $breadcum = $this->breadcum;
         $categorydata = category::getList();
        return view('admin.manage_code.update', compact('breadcum', 'user','categorydata'));
    }

    public function update(UpdateAdminCodeRequest $request) {
        $all = $request->all();
        code::find($all['uid'])->update($all);
        return redirect()->route('adminCode')->with('success', 'code updated successfully.');
    }

    public function publish(Request $request) {
        try {
            $record = code::find($request->id);
            if (!empty($record)) {
                if ($record->is_active == 1) {
                    code::find($record->id)->update(['is_active' => 0]);
                } else {
                    code::find($record->id)->update(['is_active' => 1]);
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
        code::find($id)->update(['is_delete' => 1]);
//        code::destroy($id);
        return redirect()->back()->with('success', 'Admin code deleted successfully.');
    }

}
