<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\condition;
use App\category;
use App\Http\Requests\Admin\AddAdminConditionRequest;
use App\Http\Requests\Admin\UpdateAdminConditionRequest;
use Config;

class ManageAdminConditionController extends SystemAdminController {

    var $breadcum = ['icon' => 'mdi mdi-account-multiple', 'breadcum' => ['Manage Conditions']];

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = condition::where(function($q)use($filter) {
                            if (!empty($filter['search'])) {
                                $q->where('name', 'LIKE', '%' . $filter['search'] . '%');
                            }
                        })->where('is_delete',0)
                        ->orderBy('id', 'asc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_condition.list', compact('breadcum', 'users', 'filter'));
    }

    public function create() {
        $breadcum = $this->breadcum;
         $categorydata = category::getList();
        return view('admin.manage_condition.create', compact('breadcum','categorydata'));
    }

    public function store(AddAdminConditionRequest $request) {
        $all = $request->all();
        $all['is_active'] = 1;
        $role = condition::create($all);
        return redirect()->route('adminCondition')->with('success', 'New admin condition added successfully.');
    }

    public function edit($id) {
        $user = condition::find($id);
        $breadcum = $this->breadcum;
         $categorydata = category::getList();
        return view('admin.manage_condition.update', compact('breadcum', 'user','categorydata'));
    }

    public function update(UpdateAdminConditionRequest $request) {
        $all = $request->all();
        condition::find($all['uid'])->update($all);
        return redirect()->route('adminCondition')->with('success', 'Condition updated successfully.');
    }

    public function publish(Request $request) {
        try {
            $record = condition::find($request->id);
            if (!empty($record)) {
                if ($record->is_active == 1) {
                    condition::find($record->id)->update(['is_active' => 0]);
                } else {
                    condition::find($record->id)->update(['is_active' => 1]);
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
        condition::find($id)->update(['is_delete' => 1]);
//        condition::destroy($id);
        return redirect()->back()->with('success', 'Admin condition deleted successfully.');
    }

}
