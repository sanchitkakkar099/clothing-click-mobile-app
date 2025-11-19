<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\messagedelete;
use Auth;
use App\messages;
use App\posts;
use App\User;
use App\images;
use App\user_notifications;

class ChatController extends APIResponseController {

    public function index(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($request->all(), [
            'type' => 'required|numeric|min:1|max:2',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        // $messages = messages::with('post')->where(function($q){
        //                 $q->where('sender_user_id',Auth::user()->id)
        //                 ->orwhere('receiver_user_id',Auth::user()->id);
        //             })
        //             ->whereHas('post',function($q) use($all){
        //                 if($all['type'] == 1){
        //                     $q->where('user_id','!=',Auth::user()->id);
        //                 }else{
        //                     $q->where('user_id',Auth::user()->id);
        //                 }
        //             })
        //             ->orderBy('created_at','desc')
        //             ->groupBy('post_id')
        //             ->paginate(config('constants.API_PER_PAGE_RECORDS'));
        
        //** start update by mahesh

            
    //    if(isset($all['test'])){
            $newids = [];
            $post_s_ids = [];
            // hide messages from block user, and also blocked user doest not see chat list of who blocked him
            $blockedByUsers1 = Auth::user()->listUserBlocked()->pluck('user_id')->toArray();

              $blockedByUsers2 = Auth::user()->getBlockedUsers()->pluck('blocked_user_id')->toArray();
              $blockedByUsers = array_merge($blockedByUsers1,$blockedByUsers2);
            $postids = messages::whereNotIn('sender_user_id',$blockedByUsers)->whereNotIn('receiver_user_id',$blockedByUsers)->has('sender')->has('receiver')->where(function($q){
                        $q->where('receiver_user_id',Auth::user()->id)
                        ->orwhere('sender_user_id',Auth::user()->id);
                    })
                    ->whereHas('post',function($q) use($all){
                            $q->where('user_id',Auth::user()->id)->where('is_delete',0);
                    })->latest()->get()->pluck('post_id');
                    
                    foreach($postids as $posid){
                        $getdeleted = messagedelete::where('post_id', $posid)->where('type', 'seller')->where('deleted_by', Auth::user()->id)->pluck('deleted_for')->toArray();
                        array_push($getdeleted, Auth::user()->id);
                        if(isset($all['test'])){
                            // dd($getdeleted);
                        }
                    
                    $unreadMessages = messages::has('sender')->has('receiver')->where(function($q){
                        $q->where('receiver_user_id',Auth::user()->id)
                        ->orwhere('sender_user_id',Auth::user()->id);
                    })->where(function($q) use($getdeleted){
                        $q->whereNotIn('receiver_user_id',$getdeleted)
                        ->orWhereNotIn('sender_user_id',$getdeleted);
                    })
                    ->whereHas('post',function($q) use($all,$posid){
                            $q->where('user_id',Auth::user()->id)->where('is_delete',0);
                    })->where('post_id',$posid)->latest()->get()->unique('sender_user_id');

                        foreach($unreadMessages as $_msg){
                            if(isset($post_s_ids[$_msg->post_id]['sender'][$_msg->receiver_user_id][$_msg->sender_user_id]) || isset($post_s_ids[$_msg->post_id]['sender'][$_msg->sender_user_id][$_msg->receiver_user_id])) {
                                
                           }else{
                                $post_s_ids[$_msg->post_id]['sender'][$_msg->sender_user_id][$_msg->receiver_user_id] = $_msg->sender_user_id;
                                $post_s_ids[$_msg->post_id]['sender'][$_msg->receiver_user_id][$_msg->sender_user_id] = $_msg->receiver_user_id;                                
                                //$post_s_ids[$_msg->post_id]['receive'][$_msg->receiver_user_id] = $_msg->receiver_user_id;
                                array_push($newids, $_msg->id);               
                            }
                        }
                    }
                    
                            
        if($all['type'] == 2){

                    $messages = messages::whereIn('id',$newids)->orderBy('created_at','desc')->paginate(config('constants.API_PER_PAGE_RECORDS'));        
                    $sort_messages = collect($messages->items())->sortByDesc(function ($temp, $key) {
                        return \Carbon\Carbon::parse($temp['created_at'])->getTimestamp(); 
                    });         
        }else{

           $blockedUsers = Auth::user()->listUserBlocked()->pluck('user_id')->toArray();
            $blockedByUsers = Auth::user()->getBlockedUsers()->pluck('blocked_user_id')->toArray();            
                if(isset($all['m__test'])){
                 //   dd($blockedByUsers,$blockedUsers);
                }  
                $mergeBlockList = array_merge($blockedByUsers,$blockedUsers);
                
				$messages_ids = messages::whereNotIn('sender_user_id',$mergeBlockList)->whereNotIn('receiver_user_id',$mergeBlockList)->has('sender')->has('receiver')->with('post')->where(function($q){
                        $q->where('sender_user_id',Auth::user()->id)
                        ->orwhere('receiver_user_id',Auth::user()->id);
                    })
                    ->whereHas('post',function($q) use($all){
                        if($all['type'] == 1){
                            $q->where('user_id','!=',Auth::user()->id)->where('is_delete',0);
                        }else{
                            $q->where('user_id',Auth::user()->id)->where('is_delete',0);
                        }
                        $q->whereDoesntHave('messagedeleted');
                    })
                    ->orderBy('created_at','desc')->latest()->get()->unique('post_id')->pluck('id');
                    // if(isset($all['test']) || $all['type'] == 2){
                    // $messages = $messages->groupBy(['post_id','sender_user_id']);                        
                    // }else{                    
                    //     $messages = $messages->groupBy('post_id');
                    // }
                    $messages = messages::whereIn('id',$messages_ids->toArray())->orderBy('created_at','desc')->paginate(config('constants.API_PER_PAGE_RECORDS'));
                    
//                    $messages = $messages->paginate(config('constants.API_PER_PAGE_RECORDS'));        
                $sort_messages =$messages;             
                // $sort_messages = collect($messages->items())->sortByDesc(function ($temp, $key) {
                //         return \Carbon\Carbon::parse($temp['created_at'])->getTimestamp(); 
                //     });
        }
                    
                    // if(isset($all['test'])){
//                        $messages = $messages->sortBy('created_at');
                    // }
        //** end
                    

        $data = [];
        $sellingNotRead = messages::whereIn('id', $newids)->where([['receiver_user_id',Auth::user()->id],['is_read',0]])->whereHas('post',function($q) use($all) {
             $q->where('user_id',Auth::user()->id)->where('is_delete',0);
         })->count();
        
        // $sellingNotRead = messages::with('post')->where([['receiver_user_id',Auth::user()->id],['is_read',0]])->whereHas('post',function($q) use($all) {
        //     $q->where('user_id',Auth::user()->id)->where('is_delete',0);
        // })->groupby('post_id')->count();

        $buyingNotRead = messages::with('post')->where([['receiver_user_id',Auth::user()->id],['is_read',0]])->whereHas('post',function($q) use($all) {
            $q->where('user_id','!=',Auth::user()->id)->where('is_delete',0)->whereDoesntHave('messagedeleted');
        })->groupby('post_id')->count();
        

        foreach($sort_messages as $message){
            
            if($all['type'] == 2){
                $isRead = messages::where([['post_id',$message->post_id],['sender_user_id',$message->sender_user_id],['is_active',1],['is_delete',0],['receiver_user_id',Auth::user()->id]])->orderby('id','desc')->first();
                if(!empty($isRead)){
                    $isRead = $isRead->is_read;
                }else{
                    $isRead = 1;
                }                
            }else{
                $isRead = messages::where([['post_id',$message->post_id],['is_active',1],['is_delete',0],['receiver_user_id',Auth::user()->id]])->orderby('id','desc')->first();
                if(!empty($isRead)){
                    $isRead = $isRead->is_read;
                }else{
                    $isRead = 1;
                }                
            }

            
            if($all['type'] == 2){

                // $lastMessage = messages::where('post_id',$message->post_id)->where(function($q)use($message){
                //         $q->where('sender_user_id',$message->sender_user_id)
                //         ->where('receiver_user_id',Auth::user()->id);
                //     })->orderby('created_at','desc')->first();                
                    $senderId = $message->sender_user_id;
                    $m_postid = $message->post_id;
                    $lastMessage = $message;
                    
                    // $lastMessage = messages::with('post')->where('post_id',$m_postid)->where(function($q)use($senderId, $m_postid){
                    //         $q->where('receiver_user_id',Auth::user()->id)
                    //         ->where('sender_user_id', $senderId)->where('post_id',$m_postid);
                    //     })->Orwhere(function($q)use($senderId,$m_postid){
                    //         $q->where('sender_user_id',Auth::user()->id)
                    //         ->where('receiver_user_id', $senderId)->where('post_id',$m_postid);
                    //     })->orderby('created_at','desc')->first();  

            }else{
            $lastMessage = messages::where('post_id',$message->post_id)->where(function($q){
                        $q->where('sender_user_id',Auth::user()->id)
                        ->orwhere('receiver_user_id',Auth::user()->id);
                    })->orderby('created_at','desc')->first();                
             }

            
            $url = '';
            $type = 'text';
            $thumbnail = '';
            if(!empty($lastMessage->photo)){
                $url = ResizeImage($lastMessage->photo,500,500);
                $thumbnail = ResizeImage($lastMessage->thumbnail,500,500);
				$type="image";
            }elseif(!empty($lastMessage->video)){
				 $vidUrl = getVideoUrl($lastMessage->video);
				$url =$vidUrl;
				$thumbnail = ResizeImage($lastMessage->thumbnail,500,500);
				$type ="video";
				
			}
            
            if($all['type'] == 2){
                if($message->sender_user_id == \Auth::user()->id){
                    $getSPhoto = ($message->receiver->photo !=null)?ResizeImage($message->receiver->photo ?? null,500,500):null;   
                    $getName = $message->receiver->username;
                    $getSenderId = $message->receiver_user_id;
                }else{
                    $getSPhoto = ($message->sender && $message->sender->photo !=null)?ResizeImage($message->sender->photo ?? null,500,500):null;     
                    $getName = ($message->sender)?$message->sender->username:null;
                    $getSenderId = $message->sender_user_id;   
                }

            }else{
                

                if($message->receiver_user_id == \Auth::user()->id){
                    $getName = $message->sender->username;
                    $getSPhoto =($message->sender->photo !=null)?ResizeImage($message->sender->photo ?? null,500,500):null;                    
                }else{
                    $getName = $message->receiver->username;
                    $getSPhoto = ($message->receiver->photo !=null)?ResizeImage($message->receiver->photo ?? null,500,500):null;                    
                }

            $getSenderId = $message->sender_user_id;
            }

            array_push($data,[
                'post_id' => $message->post_id,
                'post_user_id' => $message->post->user_id ?? null,
                'post_brand' => $message->post->brand->name ?? null,
                'post_name' => $message->post->title ?? null,
                'post_image' =>  posts::getImages($message->post->images,true),
                'post_size' => posts::getSize($message->post->sizes,true),
                'message' => $lastMessage->message ?? '',
				'type'=>$type,
                'file' => $url,
                'thumbnail' => $thumbnail,
                'is_read' => $isRead,
                'username' => $getName,
                'user_profile_picture' =>  $getSPhoto,
                'sender_user_id' => $getSenderId, 
                'created_at' => (isset($lastMessage->created_at))?date('Y-m-d H:i:s',strtotime($lastMessage->created_at)):null,
            ]);
        }

        $res['currentPage'] = $messages->currentPage();
        $res['hasMorePages'] = $messages->hasMorePages();
        $res['selling_not_read'] = $sellingNotRead;
        $res['buying_not_read'] = $buyingNotRead;
        $res['messages'] = $data;

        return $this->SendResponse($res, 'Success', 200);
    }

    public function sendMessage(Request $request)
    {

        try{

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'post_id' => 'required|numeric',
                'message' => 'nullable|string',
                'receiver_id' => 'required|numeric',
                'file' => 'nullable|mimetypes:video/mp4, video/x-flv,video/3gpp,video/quicktime,video/x-msvideo,application/x-mpegURL,image/png,image/jpg,image/jpeg|max:512000000',
				'thumbnail' => 'nullable|mimetypes:image/png,image/jpg,image/jpeg|max:5120'
                //'file' => 'nullable|mimes:jpg,jpeg,png,mp4,3gp|max:5120'
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            if(empty($all['file']) && empty($all['message'])){
                return $this->SendResponse(null, 'Please enter message or file', 400);
            }

            $post = posts::find($all['post_id']);

            if(empty($post)){
                return $this->SendResponse(null, 'Invalid post id', 400);
            }
			$videoId = null;
			$photoId = null;
			$thumbnailId =null;
			$type='';
			if(isset($all['file']) && ($all['file']->getMimeType() == 'video/mp4' || $all['file']->getMimeType() == 'video/quicktime'|| $all['file']->getMimeType() == 'video/x-flv'|| $all['file']->getMimeType() == 'video/3gpp'|| $all['file']->getMimeType() == 'x-msvideo'))
			{
				$videoId = UploadVideo($all['file'],'chat','messages');
				$type='video';
					if(isset($all['thumbnail'])){
				$thumbnailId = UploadImage($all['thumbnail'],'chat','messages');
					}
			}elseif(isset($all['file']) && !empty($all['file'])){ 
                $photoId = UploadImage($all['file'],'chat','messages');
				if(isset($all['thumbnail'])){
                $thumbnailId = UploadImage($all['thumbnail'],'chat','messages');
				}
				$type='image';
            }else{}

            $message = messages::create([
                'sender_user_id' => Auth::user()->id,
                'receiver_user_id' => $all['receiver_id'],
                'post_id' => $post->id,
                'message' => $all['message'] ?? null,
                'photo' => $photoId,
                'video' => $videoId, 
				'type'=> $type,
                'thumbnail' => $thumbnailId, 
                'is_active' => 1,
                'is_delete' => 0,
                'is_read' => 0,
                'created_at' => \Carbon\Carbon::now(),                
            ]);

            $delete_msg_post = messagedelete::where('post_id', $post->id)->where('deleted_by', $all['receiver_id'])->where('deleted_for', Auth::user()->id)->first();
            if($delete_msg_post){
            $delete_msg_post->delete();                
            }

            //     firstOrCreate([
            //     'deleted_by' => \Auth::user()->id,
            //     'post_id' => $post->id,
            // ]);
            
            $url = null;
			$res = [];
			$type="text";
			$thumbnailUrl ='';
            if(!empty($photoId)){
                $imgUrl = ResizeImage($photoId,500,500);
                $thumbnailUrl = ResizeImage($thumbnailId,500,500);
				$url =$imgUrl;
				$type ="image";
				
            }if(!empty($videoId)){
                $vidUrl = getVideoUrl($videoId);
				$thumbnailUrl = ResizeImage($thumbnailId,500,500);
				$url =$vidUrl;
				$type ="video";
            }

            $res = [
                'id' => $message->id,
                'sender_user_id' => $message->sender_user_id,
                'receiver_user_id' => $message->receiver_user_id,
                'message' => $message->message ?? '',
				'type'=>$type,
                'file' => $url,
                'thumbnail' => $thumbnailUrl,
                'is_read' => $message->is_read,
                'created_at' => date('Y-m-d H:i:s',strtotime($message->created_at))
            ];
            
                user_notifications::create([
                    'type' => 'MESSAGE_SEND',
                    'user_id' => $message->receiver_user_id,
                    'resource_id' => Auth::user()->id,
                    'resource_id_two' => $message->id,
                    'title' => 'Chat Message',
                    'message' => $message->message ?? '',
                ]);
                $_getUser = User::with('notificationSetting')->find($message->receiver_user_id);
                if($_getUser && $_getUser->notificationSetting && $_getUser->notificationSetting->is_chat == 1){
                    
                    SendFirebaseNotification('Message','New message received from '.$message->sender->name,getUsersFCMTokens($message->receiver_user_id),['user_id' => Auth::user()->id, 'sender_id' => $message->sender_user_id, 'post_id' =>$post->id, 'type' => 'MESSAGE_SEND']);            
            return $this->SendResponse($res, 'Message sent successfully.', 200);                    
                }
            $res = ['test'=>$_getUser->notificationSetting];
            //array_push($res, $firebaseRes);
            return $this->SendResponse($res, 'Message sent successfully.', 200);
            
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function details(Request $request)
    {
        try{
            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'post_id' => 'required|numeric',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $post = posts::find($all['post_id']);

            if(empty($post)){
                return $this->SendResponse(null, 'Invalid post id', 400);
            }

            $user = User::find($post->user_id);

            if(empty($user)){
                return $this->SendResponse(null, 'Invalid post id', 400);
            }

            $receiverId = $post->user_id;
            // messages::where([['post_id',$all['post_id']],['receiver_user_id',Auth::user()->id]])->update(['is_read' => 1]);

            // $messages = messages::with('post')->where(function($q){
            //                 $q->where('sender_user_id',Auth::user()->id)
            //                 ->orwhere('receiver_user_id',Auth::user()->id);
            //             })
            //             ->where('post_id',$all['post_id'])
            //             ->orderby('id','desc')
            //             ->paginate(50);
            
            //** start update by mahesh read message for particular user for post  when user is selling
            $senderId = (isset($all['sender_user_id']) && $all['sender_user_id'] !=null)?$all['sender_user_id']:null;
			

            if($senderId !=null){
            messages::where([['post_id',$all['post_id']],['receiver_user_id',Auth::user()->id],['sender_user_id',$senderId]])->update(['is_read' => 1]);                
            }else{                
            messages::where([['post_id',$all['post_id']],['receiver_user_id',Auth::user()->id]])->update(['is_read' => 1]);
            }
            //** end
            //** this is for selling post

            // $messages = messages::with('post')->where(function($q){
            //                 $q->where('sender_user_id',Auth::user()->id)
            //                 ->orwhere('receiver_user_id',Auth::user()->id);
            //             })
            //             ->where('post_id',$all['post_id']);
            //** start update by mahesh                        
            if($senderId !=null){
            $messages = messages::has('sender')->has('receiver')->with('post')->where('post_id',$all['post_id'])->where(function($q)use($senderId, $all){
                            $q->where('receiver_user_id',Auth::user()->id)
                            ->where('sender_user_id', $senderId)->where('post_id',$all['post_id']);
                        })->Orwhere(function($q)use($senderId,$all){
                            $q->where('sender_user_id',Auth::user()->id)
                            ->where('receiver_user_id', $senderId)->where('post_id',$all['post_id']);
                        }); 
                        if(isset($all['m_test'])){
                        //    dd(Auth::user()->id, $messages->get());
                        }
            }else{
            $messages = messages::has('sender')->has('receiver')->with('post')->where(function($q){
                            $q->where('sender_user_id',Auth::user()->id)
                            ->orwhere('receiver_user_id',Auth::user()->id);
                        })
                        ->where('post_id',$all['post_id']);                
            }
            //** end
            $messages  = $messages->orderby('id','desc')->paginate(50); 
            $res = [
                'currentPage' => $messages->currentPage(),
                'hasMorePages' => $messages->hasMorePages(),
                'post_id' => $post->id,
                'post_user_id' => $post->user_id,
                'post_brand' => $post->brand->name ?? null,
                'post_name' => $post->title ?? null,
                'post_image' =>  posts::getImages($post->images,true),
                'post_size' => posts::getSize($post->sizes,true),
                'post_price' => $post->price,
                'price_type' => $post->price_type,
                'price_type_name' => ($post->priceType)?$post->priceType->name:null,                
            ];

            $messageData = [];
            foreach($messages as $message){
				//print_r($message)	;exit;		


                if($message->sender_user_id != Auth::user()->id){
                    $receiverId = $message->sender_user_id;
                }

                 
            $url = '';
            $type = 'text';
            $thumbnail = '';
            if(!empty($message->photo)){
               //$url = ResizeImage($message->photo,500,500);
			       $img = images::find($message->photo);
				  $url =  url('uploads/caches/'.$img->directory.'/500_500/'.$img->name);

				//echo $url;exit;
				if($message->thumbnail){
					$thumbnail = ResizeImage($message->thumbnail,500,500);
				}
				$type="image";
            }elseif(!empty($message->video)){
				$vidUrl = getVideoUrl($message->video);
				$url =$vidUrl;
				if($message->thumbnail){
					$thumbnail = ResizeImage($message->thumbnail,500,500);
				}
				$type ="video";
			}else{}
			

                 array_push($messageData,[
                    'id' => $message->id,
                    'sender_user_id' => $message->sender_user_id,
                    'receiver_user_id' => $message->receiver_user_id,
                    'message' => $message->message ?? '',
					'type'=> $type,
					'thumbnail'=> $thumbnail,
                    'file' => $url,
                    'is_read' => $message->is_read,
                    'created_at' => date('Y-m-d H:i:s',strtotime($message->created_at))
                ]); 
				
            }
            $seller = [
                'username' => $user->username ?? null,
                'user_profile_picture' => ($user->photo !=null)?ResizeImage($user->photo,500,500):null,
                
            ];

            $buyer = User::find($receiverId);
            $buyer = [
                'username' => $buyer->username ?? null,
//                'user_profile_picture' => $buyer,
               'user_profile_picture' => ($buyer && $buyer->photo !=null)?ResizeImage($buyer->photo,500,500):null,                
            ];

            $res['seller'] = $seller;
            $res['buyer'] = $buyer;
            $res['receiver_id'] = $receiverId;
            $res['messages'] = $messageData;

            return $this->SendResponse($res, 'Success', 200);
            
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
    
    public function deleteMessage(Request $request){
        try{
            $all = $request->all();
            // message_id is now post id
            $validator = Validator::make($request->all(), [
                'message_id' => 'required|numeric',
                'type' => 'required',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $post = posts::find($all['message_id']);

            if(empty($post)){
                return $this->SendResponse(null, 'Invalid post', 400);
            }
            $delete_msg_post = messagedelete::firstOrCreate([
                'deleted_by' => \Auth::user()->id,
                'post_id' => $post->id,
                'deleted_for' => $request->second_user_id,
                'type' => $request->type
            ]);
            // $delete_msg_post->save();

            // $message->is_delete = 1;
            // $message->save();

            $res['messages'] = 'Message deleted successfully!';
        return $this->SendResponse($res, 'Success', 200);
            
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
    

}
