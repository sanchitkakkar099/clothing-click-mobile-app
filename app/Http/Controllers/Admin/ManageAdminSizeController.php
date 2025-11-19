<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\size;
use App\category;
use App\Http\Requests\Admin\AddAdminSizeRequest;
use App\Http\Requests\Admin\UpdateAdminSizeRequest;
use Config;

class ManageAdminSizeController extends SystemAdminController {

    var $breadcum = ['icon' => 'mdi mdi-account-multiple', 'breadcum' => ['Manage Size']];

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = size::where(function($q)use($filter) {
                            if (!empty($filter['search'])) {
                                $q->where('name', 'LIKE', '%' . $filter['search'] . '%');
                            }
                        })->where('is_delete',0)
                        ->orderBy('id', 'asc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_size.list', compact('breadcum', 'users', 'filter'));
    }

    public function create() {
        $breadcum = $this->breadcum;
         $categorydata = category::getParentList();
        return view('admin.manage_size.create', compact('breadcum','categorydata'));
    }

    public function store(AddAdminSizeRequest $request) {
        $all = $request->all();
        $catdata = category::getCategory($all['category_id']);
        $all['gender_id'] = $catdata->gender_id;
        $all['is_active'] = 1;
       
        $role = size::create($all);
        return redirect()->route('adminSize')->with('success', 'New admin size added successfully.');
    }

    public function edit($id) {
        $user = size::find($id);
        $breadcum = $this->breadcum;
         $categorydata = category::getParentList();
        return view('admin.manage_size.update', compact('breadcum', 'user','categorydata'));
    }

    public function update(UpdateAdminSizeRequest $request) {
        $all = $request->all();
         $catdata = category::getCategory($all['category_id']);
        $all['gender_id'] = $catdata->gender_id;
        size::find($all['uid'])->update($all);
        return redirect()->route('adminSize')->with('success', 'Size updated successfully.');
    }

    public function publish(Request $request) {
        try {
            $record = size::find($request->id);
            if (!empty($record)) {
                if ($record->is_active == 1) {
                    size::find($record->id)->update(['is_active' => 0]);
                } else {
                    size::find($record->id)->update(['is_active' => 1]);
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
         size::find($id)->update(['is_delete' => 1]);
//        size::destroy($id);
        return redirect()->back()->with('success', 'Admin size deleted successfully.');
    }

}
