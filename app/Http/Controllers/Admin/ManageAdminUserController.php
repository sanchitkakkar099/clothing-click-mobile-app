<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\roles;
use App\admins;
use App\Http\Requests\Admin\AddAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use Config;
use App\user_reported_users;
use Illuminate\Support\Facades\DB;
use App\User;

class ManageAdminUserController extends SystemAdminController
{
    var $breadcum = ['icon'=>'mdi mdi-account-multiple','breadcum'=>['Manage Admin Users']];
    public function __construct()
    {
        parent::__construct();
    }
    public function index(Request $request)
    {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = admins::where(function($q)use($filter){
                    if(!empty($filter['search'])){
                        $q->where('name','LIKE','%'.$filter['search'].'%')
                        ->orwhere('email','LIKE','%'.$filter['search'].'%');
                    }
                })
                ->orderBy('created_at','desc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_users.list',compact('breadcum','users','filter'));
    }
    public function create()
    {
        $breadcum = $this->breadcum;
        $roles = roles::getList();
        return view('admin.manage_users.create',compact('breadcum','roles'));
    }
    public function store(AddAdminUserRequest $request)
    {
        $all = $request->all();
        $all['role_id'] = base64_decode($all['role_id']);
        $all['password'] = bcrypt($all['password']);
        $all['is_active'] = 1;
        $role = admins::create($all);
        return redirect()->route('adminUserManager')->with('success','New admin user added successfully.');
    }
    public function edit($id)
    {
        $user = admins::find($id);
        $roles = roles::getList();
        $breadcum = $this->breadcum;
        return view('admin.manage_users.update',compact('breadcum','roles','user'));
    }
    public function update(UpdateAdminUserRequest $request)
    {
        $all = $request->all();
        $all['role_id'] = base64_decode($all['role_id']);
        if(!empty($all['password'])){
            $all['password'] = bcrypt($all['password']);
        }else{
            unset($all['password']);
        }
        admins::find($all['uid'])->update($all);
        return redirect()->route('adminUserManager')->with('success','User updated successfully.');
    }
    public function publish(Request $request)
    {
        try{
            $record = admins::find($request->id);
            if(!empty($record)){
                if($record->is_active == 1){
                    admins::find($record->id)->update(['is_active'=>0]);
                }else{
                    admins::find($record->id)->update(['is_active'=>1]);
                }
                return 'success';
            }
            return 'Record not found';
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }
    public function delete($id)
    {
        if($id == 1 || $id == 2){
            return redirect()->back()->with('error','You can not delete admin user');
        }
        admins::destroy($id);
        return redirect()->back()->with('success','Admin user deleted successfully.');
    }
	public function reported_users(Request $request)
    {
        $filter = $request->all();
        $breadcum = 'Reported Users';
        $users = user_reported_users::select('reported_user_id','message', DB::raw('count(*) as total'))
		->groupBy('reported_user_id')->get();
		$list = [];
		foreach($users as $user){
			$details = [];
			$details['user_details']=User::find($user->reported_user_id);
			$details['reporting_details'] = $user;
			$list[] = $details;
		}
		$users = $list;
		
        return view('admin.manage_users.reported_list',compact('breadcum','users','filter'));
    }
     public function user_reporting(Request $request, $id)
    {
        $filter = $request->all();
        $breadcum = 'User reporting';
        $users = user_reported_users::select('reported_user_id','message','user_id')->where('reported_user_id',$id)
		->get();
		$list = [];
		foreach($users as $user){
			$details = [];
			$details['user_details']=User::find($user->user_id);
			$details['reporting_details'] = $user;
			$list[] = $details;
		}
		$users = $list;
		
        return view('admin.manage_users.reporting_list',compact('breadcum','users','filter'));
    }
     
}
