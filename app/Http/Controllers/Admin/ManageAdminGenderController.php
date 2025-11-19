<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\gender;
use App\Http\Requests\Admin\AddAdminGenderRequest;
use App\Http\Requests\Admin\UpdateAdminGenderRequest;
use Config;

class ManageAdminGenderController extends SystemAdminController {

    var $breadcum = ['icon' => 'mdi mdi-account-multiple', 'breadcum' => ['Manage Gender']];

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = gender::where(function($q)use($filter) {
                            if (!empty($filter['search'])) {
                                $q->where('name', 'LIKE', '%' . $filter['search'] . '%');
                            }
                        })
                        ->orderBy('created_at', 'desc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_gender.list', compact('breadcum', 'users', 'filter'));
    }

    public function create() {
        $breadcum = $this->breadcum;
        return view('admin.manage_gender.create', compact('breadcum'));
    }

    public function store(AddAdminGenderRequest $request) {
        $all = $request->all();
        $all['is_active'] = 1;
        $role = gender::create($all);
        return redirect()->route('adminGender')->with('success', 'New admin gender added successfully.');
    }

    public function edit($id) {
        $user = gender::find($id);
        $breadcum = $this->breadcum;
        return view('admin.manage_gender.update', compact('breadcum', 'user'));
    }

    public function update(UpdateAdminGenderRequest $request) {
        $all = $request->all();
        gender::find($all['uid'])->update($all);
        return redirect()->route('adminGender')->with('success', 'Gender updated successfully.');
    }

    public function publish(Request $request) {
        try {
            $record = gender::find($request->id);
            if (!empty($record)) {
                if ($record->is_active == 1) {
                    gender::find($record->id)->update(['is_active' => 0]);
                } else {
                    gender::find($record->id)->update(['is_active' => 1]);
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
        gender::destroy($id);
        return redirect()->back()->with('success', 'Admin gender deleted successfully.');
    }

}
