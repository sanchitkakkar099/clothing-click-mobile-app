<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\user_notifications;
use App\User;
use App\posts;
use App\messages;
use App\user_saved_searches;
use App\category;
use App\custom_notifications;
use App\user_logindevice_info;

class NotificationController extends APIResponseController {

    public function index(Request $request)
    {
        try{
            $blockedUsers1 = Auth::user()->listUserBlocked()->pluck('user_id')->toArray();
            $blockedUsers2 = Auth::user()->getBlockedUsers()->pluck('blocked_user_id')->toArray();
            $blockedByUsers = array_merge($blockedUsers1,$blockedUsers2);
            
            user_notifications::where('user_id',Auth::id())->update(['is_read' => 1]);

            $notifications = user_notifications::has('user')->where([['user_id',Auth::user()->id],['is_active',1],['is_delete',0],['type', '!=','EARN_CLICK_COIN']])
                            ->orderby('created_at','desc')
                            ->paginate(config('constants.API_PER_PAGE_RECORDS'));

            $data = [];

            foreach($notifications as $notification){

                $postImage = null;
                $postTitle = null;
                $postSize = null;
                $postBrand = null;
                $postPrice = null;
                $postDesc = null;
                $userPhoto = null;
                $username = null;
                $name = null;
                $userId = null;
                $postId = null;
                $sellerId = null;
                $earnedCoin = null;
                $followRequestId = null;
                $saveSearchId = null;
                $roleId = null;
                $saveSearchName = null;
                $category = null;
                
                if($notification->type == 'SAVED_SEARCH_ALERT' || $notification->type == 'WATCHLIST_PRICE_DROP'){

                    $post = posts::whereHas('user', function($q) use($blockedByUsers){
                        $q->whereNotIn('id', $blockedByUsers);
                    })->find($notification->resource_id);
                    if(!$post){
                        continue;
                    }
                    $postId = $post->id ?? null;
                    $postImage = posts::getImages($post->images ?? null,true);
                    $postTitle = $post->title ?? null;
                    $postSize = posts::getSize($post->sizes ?? null);
                    $postBrand = $post->brand->name ?? null;

                    
                    if($notification->type == 'WATCHLIST_PRICE_DROP'){
                        $postPrice =  $post->sale_price ?? $post->price;
                    }else{
                        $postPrice = $post->price ?? 0;
                    }

                    if($notification->type == 'SAVED_SEARCH_ALERT'){
                    $categories = explode(',', $post->categories);                        
                        $category = category::whereDoesntHave('childCategories')->wherein('id',$categories)->where([['is_active',1],['is_delete',0]])->first();    
                        
                        $postTitle = $category->name;
                        $saveSearchId = $notification->resource_id_two;
                        $check = user_saved_searches::where('is_delete',0)->find($saveSearchId);
                        if(!$check){
                            continue;
                        }                        
                        if($check){
                            $saveSearchName = $check->name;
                        }                        
                    }
                    

                    $postDesc = $post->description ?? null;


                }elseif($notification->type == 'RATE_SELLER' || $notification->type == 'FOLLOW_REQUEST' || $notification->type == 'NEW_FOLLOWER' || $notification->type == 'ACCEPT_FOLLOW_REQUEST'){
                    /// hiding accetp follow request in notification are
                // || $notification->type == 'ACCEPT_FOLLOW_REQUEST'
                    $user = User::whereNotIn('id',$blockedByUsers)->find($notification->resource_id);
                    if(!$user){
                        continue;
                    }


                    $userPhoto = ($user->photo !=null)?ResizeImage($user->photo ?? null,500,500):'';
                    $username = $user->username ?? null;
                    $name = $user->name ?? null;

                    if($notification->type == 'RATE_SELLER'){
                        $postId = $notification->resource_id_two ?? null;
                        $sellerId = $notification->resource_id ?? null;

                        $seller = User::find($sellerId);

                        $roleId = $user->role_id ?? null;

                    }else{
                        $userId = $notification->resource_id;

                        $user = User::find($notification->resource_id);

                        $roleId = $user->role_id ?? null;
                    }

                    if($notification->type == 'FOLLOW_REQUEST'){
                        $followRequestId = $notification->resource_id_two;
                    }

                }elseif($notification->type == 'EARN_CLICK_COIN'){
                    $earnedCoin = $notification->resource_id ?? null;
                }elseif($notification->type == 'MESSAGE_SEND'){
                    $user = User::whereNotIn('id',$blockedByUsers)->find($notification->resource_id);         
                    if(!$user){
                        continue;
                    }
                    $message = messages::with('post')->find($notification->resource_id_two);
                    
                    $userPhoto = ($user->photo !=null)?ResizeImage($user->photo ?? null,500,500):'';
                    $username = $user->username ?? null;
                    $name = $user->name ?? null;                    
                    $postId = ($message)?$message->post_id:null;
                    $userId = $notification->resource_id;
                    
                }elseif($notification->type == 'CUSTOM_NOTIFICATION'){
                    $customNotification = custom_notifications::find($notification->resource_id);         
                    if(!$customNotification){
                        continue;
                    }                    
                    $userPhoto = ($customNotification->photo !=null)?ResizeImage($customNotification->photo ?? null,500,500):'';
                }

                array_push($data,[
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'is_read' => $notification->is_read,
                    'created_at' => date('Y-m-d H:i:s',strtotime($notification->created_at)),
                    'post_image' => $postImage,
                    'post_title' => $postTitle,
                    'post_size' => $postSize,
                    'post_brand' => $postBrand,
                    'post_price' => $postPrice,
                    'post_desc' => $postDesc,
                    'name' => $name,
                    'username' => $username,
                    'user_photo' => $userPhoto,
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'post_id' => $postId,
                    'seller_id' => $sellerId,
                    'follow_request_id' => $followRequestId,
                    'earned_coin' => $earnedCoin,
                    'save_search_id' => $saveSearchId,
                    'save_search_name' => $saveSearchName,
                    'category' => $category,
                ]);

            }

            
            $res['currentPage'] = $notifications->currentPage();
            $res['hasMorePages'] = $notifications->hasMorePages();
            $res['notifications'] = $data;

            return $this->SendResponse($res, 'Success', 200);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function sendNotification(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'send_notification_to' => 'required',
                'custom_user_ids' => 'required_if:send_notification_to,other',
                'header' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
            $all              = $request->all();
            $all['is_active'] = 1;
            $all['user_id'] = Auth::user()->id;
            $img              = null;
            if ($request->hasFile('photo')) {
                $all['photo'] = UploadImage($request->file('photo'), '', '', '');
            }

            $notification = custom_notifications::create($all);

            if($notification->send_notification_to == 'women'){
                $getUserIds = User::RemoveBlockUser()->where([['user_selected_gender',2],['is_active',1],['is_delete',0],['id','!=',Auth::user()->id]])->pluck('id')->toArray();
            }elseif($notification->send_notification_to == 'men'){
                $getUserIds = User::RemoveBlockUser()->where([['user_selected_gender',1],['is_active',1],['is_delete',0],['id','!=',Auth::user()->id]])->pluck('id')->toArray();
            }elseif($notification->send_notification_to == 'all'){
                $getUserIds = User::RemoveBlockUser()->where([['is_active',1],['is_delete',0],['id','!=',Auth::user()->id]])->pluck('id')->toArray();
            }elseif($notification->send_notification_to == 'other'){
                $getUserIds = explode(",", $request->custom_user_ids);
            }

            $tokens = user_logindevice_info::whereIn('appuser_id',$getUserIds)->whereNotNULL('devices_token')->pluck('devices_token')->toArray();

            if($notification->show_notificatoin_log == 1){            
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

            if($notification->display_on_screen == 1){
                //SendFirebaseNotification($notification->header, $notification->subtext,$tokens,['post_id' => $all['user_id'],'type' => 'CUSTOM_NOTIFICATION']);

            }

            return $this->SendResponse($notification, 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse([], $e->getMessage(), 500);
        }
    }
    
}
