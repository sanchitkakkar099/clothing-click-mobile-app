<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddNotificationsRequest;
use App\Http\Requests\Admin\UpdateNotificationsRequest;
use App\Notifications;
use App\User;
use App\custom_notifications;
use App\user_logindevice_info;
use App\user_notifications;
use Auth;
use Config;
use Illuminate\Http\Request;

class ManageNotificationsController extends SystemAdminController
{
    var $breadcum = ['icon'=>'mdi mdi-account-multiple','breadcum'=>['Manage Notifications']];

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        
        $notifications = custom_notifications::where(function($q)use($filter){
                    if(!empty($filter['search'])){
                        $q->where('header','LIKE','%'.$filter['search'].'%');
                    }
                })
                ->orderBy('created_at','desc')->paginate(Config::get('constants.pageRecords'));
              
        return view('admin.notifications.list',compact('breadcum','notifications','filter'));
    }

    public function create()
    {
        $breadcum = $this->breadcum;
         $usersdata = User::get();
        return view('admin.notifications.create',compact('breadcum','usersdata'));
    }
    
    
    public function store(AddNotificationsRequest $request)
    {
         $all = $request->all();
         // dd($all);
            $token_array = [];

            $all['is_active'] = 1;
            // $all['user_id'] = Auth::user()->id;
            $img              = null;
            if ($request->hasFile('photo')) {
                $all['photo'] = UploadImage($request->file('photo'), '', '', '');
            }
            if($request->send_notification_to == 'custom'){
                $all['custom_user_ids'] = implode(",",$request->custom_user_ids);
            }

            $notification = custom_notifications::create($all);

            if($notification->send_notification_to == 'women'){
                $getUserIds = User::RemoveBlockUser()->where([['user_selected_gender',2],['is_active',1],['is_delete',0]])->pluck('id')->toArray();
            }elseif($notification->send_notification_to == 'men'){
                $getUserIds = User::RemoveBlockUser()->where([['user_selected_gender',1],['is_active',1],['is_delete',0]])->pluck('id')->toArray();
            }elseif($notification->send_notification_to == 'all'){
                $getUserIds = User::RemoveBlockUser()->where([['is_active',1]])->pluck('id')->toArray();
            }elseif($notification->send_notification_to == 'custom'){
                $getUserIds = explode(",",$notification->custom_user_ids);
            }
            // dd($getUserIds);
            $tokens = user_logindevice_info::whereIn('appuser_id',$getUserIds)->whereNotNULL('devices_token')->distinct('devices_token')->pluck('devices_token')->toArray();

            SendFirebaseNotification($notification->header, $notification->subtext,$tokens,['type' => 'CUSTOM_NOTIFICATION']);

            if($notification->show_notificatoin_log == '1'){            
                foreach ($getUserIds as $key => $value) {
                    user_notifications::create([
                        'type' => 'CUSTOM_NOTIFICATION',
                        'user_id' => $value,
                        'resource_id' => $notification->id,
                        'title' => $notification->header,
                        'message' => $notification->subtext,
                    ]);
                }
            }


       
        return redirect()->route('Notifications')->with('success','New notification added successfully.');
    }




    public function delete($id)
    {
        if($id == 1 || $id == 2){
            return redirect()->back()->with('error','You can not delete user');
        }
        custom_notifications::destroy($id);
        return redirect()->back()->with('success','Notification deleted successfully.');
    }
}
