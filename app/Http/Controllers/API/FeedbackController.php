<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\reviews;
use App\posts;
use App\feedback;
use App\User;
use App\user_notifications;

class FeedbackController extends APIResponseController {

    public function index(Request $request)
    {
        try{
            $res = [];
			$feedbacks = feedback::where('receiver_user_id',Auth::user()->id)
                        ->orwhere('sender_user_id',Auth::user()->id)->get();
			//echo "<pre>";
			$data =[];
            foreach($feedbacks as $f){
				$thumbnail ='';
				$url ='';
				if(!empty($f->photo)){
					$url = ResizeImage($f->photo,500,500);
					$thumbnail = ResizeImage($f->thumbnail,500,500);
				}
				/* if($f->receiver_user_id == \Auth::user()->id){
                    $getName = $f->sender->username;
                    $getSPhoto =($f->sender->photo !=null)?ResizeImage($f->sender->photo ?? null,500,500):null;                    
                }else{
                    $getName = $f->receiver->username;
                    $getSPhoto = ($f->receiver->photo !=null)?ResizeImage($f->receiver->photo ?? null,500,500):null;                    
                } */
				array_push($data,[
                'message' => $f->message ?? '',
                'file' => $url,
                'thumbnail' => $thumbnail,
                'is_read' => $f->is_read,
               /*  'username' => $getName,
                'user_profile_picture' =>  $getSPhoto, */
                'sender_user_id' => $f->sender_user_id, 
                'created_at' => (isset($lastMessage->created_at))?date('Y-m-d H:i:s',strtotime($lastMessage->created_at)):null,
            ]);
			}
			$res['feedbacks'] = $data;
            return $this->SendResponse($res, 'Success', 200);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function add(Request $request)
    {
        try{
            $all = $request->all();

            $validator = Validator::make($all, [
                'message' => 'nullable|string',
                'file' => 'nullable|mimetypes:image/png,image/jpg,image/jpeg|max:512000000',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
			if(empty($all['file']) && empty($all['message'])){
                return $this->SendResponse(null, 'Please enter message or file', 400);
            }
			$photoId ='';
            if(isset($all['file']) && !empty($all['file'])){ 
                $photoId = UploadImage($all['file'],'chat','messages');
			}
    
            feedback::create([
                'sender_user_id' => Auth::user()->id,
                'message' => $all['message'],
                'photo' => $photoId,
                'is_read' => 1,
            ]);
    
            return $this->SendResponse(null, 'feedback added successfully.', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    
}
