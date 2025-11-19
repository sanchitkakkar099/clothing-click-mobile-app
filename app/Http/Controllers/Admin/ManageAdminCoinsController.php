<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\coins;
use App\gender;
use App\Http\Requests\Admin\AddAdminCoinsRequest;
use App\Http\Requests\Admin\UpdateAdminCoinsRequest;
use Config;
use Auth;

class ManageAdminCoinsController extends SystemAdminController {

    var $breadcum = ['icon' => 'mdi mdi-account-multiple', 'breadcum' => ['Manage Coins']];

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = coins::where(function($q)use($filter) {
                            if (!empty($filter['search'])) {
                                $q->where('name', 'LIKE', '%' . $filter['search'] . '%');
                            }
                        })->where('is_delete',0)
                        ->orderBy('id', 'asc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_coins.list', compact('breadcum', 'users', 'filter'));
    }

    public function create() {
        $breadcum = $this->breadcum;
        return view('admin.manage_coins.create', compact('breadcum'));
    }

    public function store(AddAdminCoinsRequest $request) {
        $all = $request->all();
        $all['is_active'] = 1;
        $img = NULL;
            if($request->hasFile('photo')){
                $all['photo'] = UploadImage($request->file('photo'),'','','');
                 DeleteImage(Auth::guard('admin')->user()->photo);
            }
        $role = coins::create($all);
        return redirect()->route('adminCoins')->with('success', 'New admin coins added successfully.');
    }

    public function edit($id) {
        $user = coins::find($id);
        $breadcum = $this->breadcum;
        return view('admin.manage_coins.update', compact('breadcum', 'user'));
    }

    public function update(UpdateAdminCoinsRequest $request) {
        $all = $request->all();
         $img = NULL;
            if($request->hasFile('photo')){
                $all['photo'] = UploadImage($request->file('photo'),'','','');
                DeleteImage(Auth::guard('admin')->user()->photo);
            }
        coins::find($all['uid'])->update($all);
        return redirect()->route('adminCoins')->with('success', 'Coins updated successfully.');
    }

    public function publish(Request $request) {
        try {
            $record = coins::find($request->id);
            if (!empty($record)) {
                if ($record->is_active == 1) {
                    coins::find($record->id)->update(['is_active' => 0]);
                } else {
                    coins::find($record->id)->update(['is_active' => 1]);
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
         coins::find($id)->update(['is_delete' => 1]);
//        coins::destroy($id);
        return redirect()->back()->with('success', 'Admin coins deleted successfully.');
    }

}
