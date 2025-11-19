<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\User;
use App\user_followers;
use App\posts;
use App\user_followings;
use App\user_coin_transactions;
use App\user_notifications;

class UserFollowerController extends APIResponseController 
{

    public function following(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($all, [
                'search' => 'nullable|string|max:100',
            ]);
            
            if($request->has('user_id') && $request->user_id !=null){
                $user = User::find($request->user_id);
            }else{
                $user = Auth::user();
            }            
            if(isset($all['test'])){
//                dd($user);
            }
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
            
            $blockedUsers1 = Auth::user()->listUserBlocked()->pluck('user_id')->toArray();
            $blockedUsers2 = Auth::user()->getBlockedUsers()->pluck('blocked_user_id')->toArray();
            $blockedUsers = array_merge($blockedUsers1,$blockedUsers2);
            //dd($blockedUsers);
            $followings = User::RemoveBlockUser()->leftjoin('user_followings',function($q)use($all,$user){
                            $q->on('user_followings.following_user_id','users.id')
                                ->where(function($iq) use($user){
                                    $iq->where([['user_followings.user_id',$user->id],['user_followings.is_active',1],['user_followings.is_delete',0]])
                                    ->orwherenull('user_followings.user_id');
                                });
                        })->whereNotIn('user_followings.following_user_id',$blockedUsers)
                        ->where([['users.is_active',1],['users.is_delete',0],['users.id','!=',$user->id]])
                        ->where([['user_followings.is_active',1],['user_followings.is_delete',0]])
                        ->where(function($q)use($all){
                            $q->where([['user_followings.is_active',1],['user_followings.is_delete',0]])
                            ->orwhereNull('user_followings.is_active');
                        })
                        ->where(function($q)use($all){
                            if(isset($all['search']) && !empty($all['search'])){
                                $q->where('users.username','LIKE','%'.$all['search'].'%')
                                ->orwhere('users.name','LIKE','%'.$all['search'].'%');
                            }
                        })
                        ->orderby('user_followings.id','desc')
                        ->orderby('users.name','asc')
                        ->select('users.name','users.username','users.id AS uid','users.photo','users.role_id','user_followings.*')
                        ->paginate(100000);

            $userFollowings = [];
            if(isset($all['test'])){
                //dd($followings);
            }
            foreach($followings as $following){

                $is_following = 0;
                if($request->has('user_id') && $request->user_id !=null){   
                    $getFollowing = user_followings::where('user_id', \Auth::user()->id)->where('following_user_id', $following->uid)->where('is_active',1)->where('is_delete',0)->first();
                    
                    if($getFollowing){
                        $is_following = 1;
                    }                    
                }else{
                    if(!empty($following->following_user_id)){
                        $is_following = 1;
                    }                    
                }


                $image = null;
//                if(!empty($following->photo)){
                    $image = ResizeImage($following->photo,500,500,'user_image');
  //              }

                $isRequested = user_followers::where([['user_id',$following->uid],['follower_user_id',Auth::user()->id],['is_accepted',0],['is_active',1],['is_delete',0]])->count(); 
				$followers = user_followers::where([['user_id',$following->uid],['is_accepted',1],['is_active',1],['is_delete',0]])->count();
                if($isRequested > 0){
                    $isRequested = 1;
                }if($followers > 0){
                    $followers = 1;
                }
                $total_posts =  posts::where([['posts.is_active', 1], ['posts.is_delete', 0], ['posts.user_id', $following->uid]])->count();

                array_push($userFollowings,[
                    'user_id' => intval($following->uid),
                    'role_id' => $following->role_id,
                    'name' => $following->name,
                    'username' => $following->username,
                    'is_following' => $is_following,
                    'followers' => $followers,
                    'is_requested' => $isRequested,
                    'photo' => $image,
                    'total_posts' => $total_posts
                ]);
            }

            $res['totalFollowings'] = $user->followings()->whereNotIn('following_user_id', $blockedUsers)->has('user')->where([['is_active',1],['is_delete',0]])->count();
            $res['totalFollowers'] = $user->followers()->whereNotIn('follower_user_id', $blockedUsers)->has('user')->where([['is_accepted',1],['is_active',1],['is_delete',0]])->count();
            $res['totalFollowRequests'] = $user->followers()->has('user')->where([['is_accepted',0],['is_active',1],['is_delete',0]])->count();
			 $res['totalpost'] =  posts::where([['posts.is_active', 1], ['posts.is_delete', 0], ['posts.user_id', $user->id]])->count();
            $res['currentPage'] = $followings->currentPage();
            $res['hasMorePages'] = $followings->hasMorePages();
			
            $res['followings'] = $userFollowings;
           

    
            return $this->SendResponse($res, 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function followers(Request $request)
    {
        try{
            $all = $request->all();

            $validator = Validator::make($all, [
                'search' => 'nullable|string|max:100',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
            
            if($request->has('user_id') && $request->user_id !=null){
                $user = User::find($request->user_id);
            $blockedUsers1 = $user->listUserBlocked()->pluck('user_id')->toArray();
            $blockedUsers2 = $user->getBlockedUsers()->pluck('blocked_user_id')->toArray();
            $blockedUsers = array_merge($blockedUsers1,$blockedUsers2);
            }else{
                $user = Auth::user();
            $blockedUsers1 = Auth::user()->listUserBlocked()->pluck('user_id')->toArray();
            $blockedUsers2 = Auth::user()->getBlockedUsers()->pluck('blocked_user_id')->toArray();
            $blockedUsers = array_merge($blockedUsers1,$blockedUsers2);                
            }
            //dd($blockedUsers);
            

            
            $followers = user_followers::whereNotIn('follower_user_id',$blockedUsers)->with('user')
                        ->where('user_id',$user->id)
                        ->where([['is_active',1],['is_delete',0],['is_accepted',1]])
                        ->whereHas('user',function($q)use($all){
                            $q->where([['is_active',1],['is_delete',0]]);
                        })
                        ->whereHas('user',function($q)use($all){
                            if(isset($all['search']) && !empty($all['search'])){
                                $q->where('username','LIKE','%'.$all['search'].'%')
                                ->orwhere('name','LIKE','%'.$all['search'].'%');
                            }
                        })
                        ->paginate(100000);
    
            $userFollowers = [];
            foreach($followers as $follower){

                //$image = null;
                //if(!empty($follower->user->photo)){
                    $image = ResizeImage($follower->user->photo,500,500,'user_image');
                //}
                
                //if($request->has('user_id') && $request->user_id !=null){
                    $is_following = 0;
                    $getFollowing = user_followings::where('user_id', \Auth::user()->id)->where('following_user_id', $follower->follower_user_id)->where('is_active',1)->where('is_delete',0)->first();
                    
                    if($getFollowing){
                        $is_following = 1;
                    }
    
                    $isRequested = user_followers::where([['user_id',Auth::user()->id],['follower_user_id',$follower->uid],['is_accepted',0],['is_active',1],['is_delete',0]])->count(); 
					
					$sumOfFollowers = user_followers::where([['user_id',$follower->follower_user_id],['is_accepted',1],['is_active',1],['is_delete',0]])->count();
					$total_posts =  posts::where([['posts.is_active', 1], ['posts.is_delete', 0], ['posts.user_id', $follower->follower_user_id]])->count();
                    if($isRequested > 0){
                        $isRequested = 1;
                    }  
					if($sumOfFollowers > 0){
                        $$sumOfFollowers = 1;
                    }                    
                //}
                array_push($userFollowers,[
                    'user_id' => $follower->follower_user_id,
                    'role_id' => $follower->user->role_id ?? null,
                    'name' => $follower->user->name ?? null,
                    'username' => $follower->user->username ?? null,
                    'photo' => $image,
                    'is_following' => $is_following,
                    'is_requested' => $isRequested,                    
                    'followers' => $sumOfFollowers,                    
                    'total_posts' => $total_posts,                    
                ]);
            }
    
            $res['totalFollowings'] = $user->followings()->whereNotIn('following_user_id',$blockedUsers)->has('user')->where([['is_active',1],['is_delete',0]])->count();
            $res['totalFollowers'] = $user->followers()->whereNotIn('follower_user_id',$blockedUsers)->has('user')->where([['is_accepted',1],['is_active',1],['is_delete',0]])->count();
            $res['totalFollowRequests'] = $user->followers()->has('user')->where([['is_accepted',0],['is_active',1],['is_delete',0]])->count();
			 
            $res['currentPage'] = $followers->currentPage();
            $res['hasMorePages'] = $followers->hasMorePages();
            $res['followers'] = $userFollowers;
    
    
            return $this->SendResponse($res, 'Success', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function followRequests(Request $request)
    {
        try{
            $all = $request->all();

            $validator = Validator::make($all, [
                'search' => 'nullable|string|max:100',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $followRequests = user_followers::with('user')
                        ->where('user_id',Auth::user()->id)
                        ->where([['is_active',1],['is_delete',0],['is_accepted',0]])
                        ->whereHas('user',function($q)use($all){
                            $q->where([['is_active',1],['is_delete',0]]);
                        })
                        ->whereHas('user',function($q)use($all){
                            if(isset($all['search']) && !empty($all['search'])){
                                $q->where('username','LIKE','%'.$all['search'].'%')
                                ->orwhere('name','LIKE','%'.$all['search'].'%');
                            }
                        })
                        ->paginate(config('constants.API_PER_PAGE_RECORDS'));
    
            $userfollowRequests = [];
            foreach($followRequests as $followRequest){

                //$image = null;
                //if(!empty($followRequest->user->photo)){
                    $image = ResizeImage($followRequest->user->photo,500,500,'user_image');
                //}

                array_push($userfollowRequests,[
                    'id' => $followRequest->id,
                    'user_id' => $followRequest->follower_user_id,
                    'role_id' => $followRequest->user->role_id ?? null,
                    'name' => $followRequest->user->name ?? null,
                    'username' => $followRequest->user->username ?? null,
                    'photo' => $image
                ]);
            }
    
            $res['totalFollowRequests'] = $followRequests->total();
            $res['currentPage'] = $followRequests->currentPage();
            $res['hasMorePages'] = $followRequests->hasMorePages();
            $res['FollowRequests'] = $userfollowRequests;
    
            return $this->SendResponse($res, 'Success', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function followUser(Request $request)
    {
        try {
            
            $all = $request->all();

            $validator = Validator::make($all, [
                'user_id' => 'required|numeric',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $checkUser = User::where([['id',$all['user_id']],['is_delete',0],['is_active',1]])->first();

            if(empty($checkUser)){
                return $this->SendResponse(null, 'Invalid user id', 400);
            }

            $check = user_followers::where([['user_id',$all['user_id']],['follower_user_id',Auth::user()->id],['is_delete',0],['is_active',1]])->orderby('id','desc')->first();

            if(!empty($check)){
                $user = User::getAPIUserDetails($checkUser,true);
                return $this->SendResponse($user, 'You already sent follow request to this user', 200);
            }

            $is_accepted = 1;
            if($checkUser->is_private == 1){
                $is_accepted = 0;
            }

            $createFollower = user_followers::create([
                'user_id' => $all['user_id'],
                'follower_user_id' => Auth::user()->id,
                'is_accepted' => $is_accepted,
                'is_active' => 1,
                'is_delete' => 0
            ]);

            if($checkUser->is_private == 1){
                user_notifications::create([
                    'type' => 'FOLLOW_REQUEST',
                    'user_id' => $all['user_id'],
                    'resource_id' => Auth::user()->id,
                    'resource_id_two' => $createFollower->id,
                    'title' => 'Follow Request',
                    'message' => 'Wants to follow you',
                ]);
                $_getUser = User::with('notificationSetting')->find($all['user_id']);
                if($_getUser && $_getUser->notificationSetting && $_getUser->notificationSetting->is_follower == 1){
                    
                SendFirebaseNotification('Follow Request','You have new follow request.',getUsersFCMTokens($all['user_id']),['user_id' => Auth::user()->id, 'follow_request_id' => $createFollower->id,'type' => 'FOLLOW_REQUEST','u_id' =>$all['user_id']]);
                }
            }

            if($is_accepted == 1){

                user_followings::create([
                    'user_id' => Auth::user()->id,
                    'following_user_id' => $all['user_id'],
                    'is_active' => 1,
                    'is_delete' => 0
                ]);

                user_notifications::create([
                    'type' => 'NEW_FOLLOWER',
                    'user_id' => $all['user_id'],
                    'resource_id' => Auth::user()->id,
                    'title' => 'Started following you'
                ]);
                $_getUser = User::with('notificationSetting')->find($all['user_id']);
                if($_getUser && $_getUser->notificationSetting && $_getUser->notificationSetting->is_follower == 1){
                SendFirebaseNotification('New Follower','You have new follower.',getUsersFCMTokens($all['user_id']),['user_id' => Auth::user()->id,'type' => 'NEW_FOLLOWER','u_id' =>$all['user_id']]);
                }

            }

            $getTotalFollowers = user_followers::where('user_id',$all['user_id'])->count();
            /*
            if($getTotalFollowers == 1){
                $checkTransaction = user_coin_transactions::where([['user_id',$all['user_id']],['type','EARN_COIN_AFTER_GET_FIRST_FOLLOWER']])->count();
                
                if($checkTransaction == 0){

                    $getUser = User::find($all['user_id']);

                    user_coin_transactions::create([
                        'type' => 'EARN_COIN_AFTER_GET_FIRST_FOLLOWER',
                        'user_id' => $getUser->id,
                        'is_credit' => 1,
                        'amount' => config('constants.COIN_GET_YOUR_FIRST_FOLLOWER'),
                        'opening_balance' => $getUser->coins,
                        'closing_balance' => $getUser->coins + config('constants.COIN_GET_YOUR_FIRST_FOLLOWER'),
                        'resource_id' => null,
                        'description' => 'Earn coin after get first follower'
                    ]);
					
					user_notifications::create([
						'type' => 'EARN_CLICK_COIN',
						'user_id' => $getUser->id,
						'resource_id' => config('constants.COIN_GET_YOUR_FIRST_FOLLOWER'),
						'title' => 'Click Coins Earned',
						'message' => 'First Follower',
					]);

                    User::find($getUser->id)->update(['coins' => $getUser->coins + config('constants.COIN_GET_YOUR_FIRST_FOLLOWER')]);

                    $_getUser = User::with('notificationSetting')->find($getUser->id);
                    if($_getUser && $_getUser->notificationSetting && $_getUser->notificationSetting->is_coin == 1){
                        SendFirebaseNotification('Click Coin','Congratulations, you have earned '.config('constants.COIN_GET_YOUR_FIRST_FOLLOWER').' Click Coins.',getUsersFCMTokens($getUser->id),['type' => 'EARN_CLICK_COIN','u_id' =>$getUser->id]);
                    }
                }
               
            }

            if($getTotalFollowers == 5){
                $checkTransaction = user_coin_transactions::where([['user_id',$all['user_id']],['type','EARN_COIN_AFTER_GET_FIVE_FOLLOWER']])->count();
                if($checkTransaction == 0){

                    $getUser = User::find($all['user_id']);

                    user_coin_transactions::create([
                        'type' => 'EARN_COIN_AFTER_GET_FIVE_FOLLOWER',
                        'user_id' => $getUser->id,
                        'is_credit' => 1,
                        'amount' => config('constants.COIN_GET_FIVE_FOLLOWERS'),
                        'opening_balance' => $getUser->coins,
                        'closing_balance' => $getUser->coins + config('constants.COIN_GET_FIVE_FOLLOWERS'),
                        'resource_id' => null,
                        'description' => 'Earn coin after get five follower'
                    ]);
					
					user_notifications::create([
						'type' => 'EARN_CLICK_COIN',
						'user_id' => $getUser->id,
						'resource_id' => config('constants.COIN_GET_FIVE_FOLLOWERS'),
						'title' => 'Click Coins Earned',
						'message' => 'Five Follower',
					]);

                    User::find($getUser->id)->update(['coins' => $getUser->coins + config('constants.COIN_GET_FIVE_FOLLOWERS')]);
                    $_getUser = User::with('notificationSetting')->find($getUser->id);
                    if($_getUser && $_getUser->notificationSetting && $_getUser->notificationSetting->is_coin == 1){
                        SendFirebaseNotification('Click Coin','Congratulations, you have earned '.config('constants.COIN_GET_FIVE_FOLLOWERS').' Click Coins.',getUsersFCMTokens($getUser->id),['type' => 'EARN_CLICK_COIN','u_id' =>$getUser->id]);
                    }
                }
            }
             */

            // array_push($userFollowings,[
            //     'user_id' => $following->uid,
            //     'role_id' => $following->role_id,
            //     'name' => $following->name,
            //     'username' => $following->username,
            //     'is_following' => $is_following,
            //     'is_requested' => $isRequested,
            //     'photo' => $image
            // ]);

            $user = User::getAPIUserDetails($checkUser,true);

            return $this->SendResponse($user, 'Success', 200);
            
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function unfollowUser(Request $request)
    {
        try {
            
            $all = $request->all();

            $validator = Validator::make($all, [
                'user_id' => 'required|numeric',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
			//print_r(Auth::user()->id);exit;
            $check = user_followings::where([['user_id',Auth::user()->id],['following_user_id',$all['user_id']],['is_delete',0]])->orderby('id','desc')->first();
            
            // $check = user_followings::where([['user_id',Auth::user()->id],['following_user_id',$all['user_id']],['is_delete',0],['is_active',1]])->orderby('id','desc')->first();
            
            // if(!empty($check_follow_request)){
                
            // }
            $checkUser = User::find($all['user_id']);
            
            $check_follow_request = user_followers::where([['is_delete',0],['is_active',1],['user_id',$all['user_id']],['follower_user_id',Auth::user()->id]])->first();
            
            if($check_follow_request){
                $check_follow_request = user_followers::where([['user_id',$all['user_id']],['follower_user_id',Auth::user()->id]])->update(['is_delete' => 1, 'is_active' => 0]);
                
				user_notifications::where([['type', 'FOLLOW_REQUEST'],['user_id', $all['user_id']], ['resource_id',Auth::user()->id]])->update(['is_delete' => 1]);
            
                    if(!empty($check)){
                        $check->is_active = 0;
                        $check->is_delete = 1;
                        $check->save();                
                    }                
                $user = User::getAPIUserDetails($checkUser,true);
                return $this->SendResponse($user, 'Success', 200);
            }
            
            if(empty($check)){
                return $this->SendResponse(null, 'You are not following this user.', 400);
            }

            $check->is_active = 0;
            $check->is_delete = 1;
            $check->save();

            user_followers::where([['user_id',$all['user_id']],['follower_user_id',Auth::user()->id]])->update(['is_delete' => 1, 'is_active' => 0]);
    

            
            $user = User::getAPIUserDetails($checkUser,true);
            return $this->SendResponse($user, 'Success', 200);
            
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function followRequestStatus(Request $request)
    {
        try {
            
            $all = $request->all();

            $validator = Validator::make($all, [
                'follow_request_id' => 'required|numeric',
                'status' => 'required|numeric|max:1'
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $check = user_followers::where([['id',$all['follow_request_id']],['is_accepted',0],['is_active',1],['is_delete',0]])->first();
            
            if(empty($check)){
                return $this->SendResponse(null, 'Invalid follow request id', 400);
            }

            if($all['status'] == 1){

                $check->is_accepted = 1;

                user_followings::create([
                    'user_id' => $check->follower_user_id,
                    'following_user_id' => Auth::user()->id,
                    'is_active' => 1,
                    'is_delete' => 0
                ]);
				$check_user = User::find($check->follower_user_id);
                user_notifications::create([
                    'type' => 'ACCEPT_FOLLOW_REQUEST',
                    'user_id' => $check->follower_user_id,
                    'resource_id' => Auth::user()->id,
                    'title' => $check_user->username.' Accepted your follow request'
                ]);

                user_notifications::where([['type','FOLLOW_REQUEST'],['user_id',Auth::id()],['resource_id_two',$all['follow_request_id']]])->update(['is_delete' => 1]);
                    $_getUser = User::with('notificationSetting')->find($check->follower_user_id);
                    if($_getUser && $_getUser->notificationSetting && $_getUser->notificationSetting->is_follower == 1){
                    SendFirebaseNotification('Accept Follow Request','Congratulations, your follow request has been accepted.',getUsersFCMTokens($check->follower_user_id),['type' => 'ACCEPT_FOLLOW_REQUEST','u_id' => $check->follower_user_id]);
                    }
            }else{
                $check->is_accepted = 0;
                $check->is_active = 0;
                $check->is_delete = 1;
                if(isset($all['notification_id'])){
                    $findNotification = user_notifications::find($all['notification_id']);
                    if($findNotification){
                        $findNotification->is_read = 0;
                        $findNotification->is_active = 0;
                        $findNotification->is_delete = 1;
                        $findNotification->save();
                        
                    }                    
                }

            }

            $check->update();

            return $this->SendResponse(null, 'Success', 200);
            
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
}
