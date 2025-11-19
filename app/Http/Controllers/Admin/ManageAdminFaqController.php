<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\condition;
use App\category;
use App\style;
use App\faq;
use App\Http\Requests\Admin\AddAdminConditionRequest;
use App\Http\Requests\Admin\UpdateAdminConditionRequest;
use Config;

class ManageAdminFaqController extends SystemAdminController {

    var $breadcum = ['icon' => 'mdi mdi-account-multiple', 'breadcum' => ['Manage FAQs']];

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = faq::where(function($q)use($filter) {
                            if (!empty($filter['search'])) {
                                $q->where('name', 'LIKE', '%' . $filter['search'] . '%');
                            }
                        })->where('is_delete',0)
                        ->orderBy('id', 'asc')->paginate(Config::get('constants.pageRecords'));
		//echo "<pre>";print_r($users);exit;
        return view('admin.manage_faq.list', compact('breadcum', 'users', 'filter'));
    }

    public function create() {
        $breadcum = $this->breadcum;
        return view('admin.manage_faq.create', compact('breadcum'));
    }

    public function store(Request $request) {
        $all = $request->all();
        $all['is_active'] = 1;
        $role = faq::create($all);
        return redirect()->route('adminFaq')->with('success', 'New FAQ added successfully.');
    }

    public function edit($id) {
        $user = faq::find($id);
        $breadcum = $this->breadcum;
        return view('admin.manage_faq.update', compact('breadcum', 'user',));
    }

    public function update(Request $request) {
        $all = $request->all();
        faq::find($all['uid'])->update($all);
        return redirect()->route('adminFaq')->with('success', 'Faq updated successfully.');
    }

    public function publish(Request $request) {
        try {
            $record = faq::find($request->id);
            if (!empty($record)) {
				
                if ($record->is_active == 1) {
                    $res = faq::find($record->id)->update(['is_active' => 0]);
                } else {
                     $res = faq::find($record->id)->update(['is_active' => 1]);
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
        $faq = faq::find($id);
		$faq->delete();
//        condition::destroy($id);
        return redirect()->back()->with('success', 'Faq deleted successfully.');
    }

}
