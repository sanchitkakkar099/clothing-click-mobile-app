<?php
use App\permissions;
use App\modules;
use App\images;
use App\videos;
use App\user_logindevice_info;
use Twilio\Rest\Client;
use App\User;
use App\user_notification_settings;

function getStripeObject()
{
   return new \Stripe\StripeClient(
    env('STRIPE_SECRET_KEY')
);
}

function SendFirebaseNotification($title, $msg, $tokens, $data)
{
    $result = null;
    
    $url = 'https://fcm.googleapis.com/fcm/send';
    $headers = array(
        'Content-Type:application/json',
        'Authorization:key=' . config('constants.FIREBASE_SERVER_KEY')
    );

    $data['title'] = $title;
    $data['body'] = $msg;

    $tokens = array_chunk($tokens, 1000);

    foreach ($tokens as $token) {
//        $token = 'cINpUVbFJU48sEt7l8H31k:APA91bEtRfOAvb-DQxyw9shROOaYKd-WMCehHT_TqROM6sMq1zkkE9Xwebwne_o0Lp8Ud7tEPz4qyI40MVoXhhdLuTZVAsNjvINXWVn-6oWBWaY0-TSoNOrhS4HdKwabVresZGEHi1bB';
//        dd($token[0]);
        $getDevice = user_logindevice_info::whereIn('devices_token', $token)->distinct('devices_token')->get();
        $deviceTypes = $getDevice->groupBy('devices_type');

        foreach($deviceTypes as $deviceType => $tokenValue){
            $tokenIds = $tokenValue->unique('devices_token')->pluck('devices_token')->toArray();
//            dd($tokenIds);
            if ($deviceType == 'A') {
                $payload = [
                    'registration_ids' => $tokenIds,
                    'data'             => $data,
                    //"android"          => [
                      //  "priority" => "high",
                    //],
                    "sound"=> "default"
                ];
            } else {
                $payload = [
                    "notification"     => [
                        "title" => $title,
                        "body"  => $msg,
                        //"sound"=> "default"
                        "sound"=> "Revisedv2.wav"
                    ],
                    'registration_ids' => $tokenIds,
                    'data'             => $data,
                    "android"          => [
                        "priority" => "high",
                    ],
                    
                ];
    
            }  
        //    print_r($payload);exit;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $result = curl_exec($ch);
        
        echo curl_error($ch);
        
            // echo "<br>sending request data <br><br>";
            // print_r($payload);
            // echo "<br><br>final success data sending request data <br><br>";            
            // print_r($result);        
            // exit;
            
        if ($result === FALSE) {
            // print_r($result);
            // echo "this is false";
            // echo "<Br>";
            die('Curl failed: ' . curl_error($ch));
        }else{
//            echo "this is sucess";
//            dd($result);
        }        
        curl_close($ch);
        
        }


        

            
        // $payload = [
        //     "notification" => [
        //         "title" => $title,
        //         "body" => $msg
        //     ],
        //     'registration_ids' => $token,
        //     'data' => $data,
        //     "android" => [
        //         "priority" => "high"
        //     ]
        // ];


    }

    return $result;
}

function getUsersFCMTokens($userId)
{
    $tokens = [];
    
    if(!empty($userId)){
        $tokens = user_logindevice_info::where('appuser_id',$userId)->whereNotNULL('devices_token')->distinct('devices_token')->pluck('devices_token')->toArray();
    }

    return $tokens;
}

function sendTwillioSMS($number,$message)
{
    $account_sid = env('TWILIO_SID');
	$auth_token = env('TWILIO_AUTH_TOKEN');
	$twillio_number = env('TWILIO_NUMBER');
    $client = new Client($account_sid, $auth_token);

    return $client->messages->create($number, ['from' => $twillio_number, 'body' => $message] );
}

function removeSpecialCharacter($aliasName)
{
    $specialChar = ['@','#','-','`','"',"'",'.',',','?','%','$','!','^','&','*','(',')','_','+','{','}','[',']',';','/','~','=','<','>'];
    $aliasName = strtolower(str_replace($specialChar,'',$aliasName));
    return $aliasName;
}

function GenerateOTP($n)
{
    $generator = "0512436897";
    $result = '';
    for ($i = 1; $i <= $n; $i++) { 
        $result .= substr($generator, (rand()%(strlen($generator))), 1); 
    }
    return $result;
}

function EmailSender($view,$data)
{
    Mail::send($view, $data, function ($m) use ($data) {

        $m->from(config('constants.MAIL_FROM_EMAIL'), config('constants.MAIL_FROM'));
        $m->sender(config('constants.MAIL_FROM_EMAIL'), config('constants.MAIL_FROM'));
        $m->to($data['mailTo'], $data['mailToName'])->subject($data['mailSubject']);

        if(isset($data['mailReplyTo']) && !empty($data['mailReplyTo'])){
            $m->replyTo($data['mailReplyTo'],config('constants.MAIL_FROM'));
        }else{
            $m->replyTo(config('constants.MAIL_REPLY_TO_EMAIL'),config('constants.MAIL_FROM'));
        }

        if(isset($data['mailCC']) && !empty($data['mailCC'])){
            $m->cc($data['mailCC'],$name = NULL);
        }

        if(isset($data['mailBCC']) && !empty($data['mailBCC'])){
            $m->bcc($data['mailBCC'],$name = NULL);
        }

        if(isset($data['mailFile']) && !empty($data['mailFile'])){
            if(is_array($data['mailFile'])){
                foreach($data['mailFile'] as $attachment){
                    $m->attach($attachment);
                }
            }else{
                $m->attach($data['mailFile']);
            }
        }
    });

    if (Mail::failures()) {
       return false;
    }
    return true;
}

function getVideoUrl($videoId)
{
    $video = videos::find($videoId);
    $url = null;
    if(!empty($video)){
        $url = url('uploads/videos/'.$video->directory.'/'.$video->name);
    }
    return $url;
}

function ResizeImage($imgId,$height,$width, $type = null)
{
    $img = images::find($imgId);

    if(!file_exists(public_path('uploads'))){
        mkdir(public_path('uploads',0755));
    }

    if(!file_exists(public_path('uploads/caches'))){
        mkdir(public_path('uploads/caches',0755));
    }

    if(!empty($img)){

        if(!file_exists(public_path('uploads/caches/'.$img->directory))){
            mkdir(public_path('uploads/caches/'.$img->directory),0755);
        }

        if(!file_exists(public_path('uploads/caches/'.$img->directory.'/'.$height.'_'.$width))){
            mkdir(public_path('uploads/caches/'.$img->directory.'/'.$height.'_'.$width),0755);
        }

        $filePath = public_path('uploads/caches/'.$img->directory.'/'.$height.'_'.$width.'/'.$img->name);

        if(!file_exists($filePath)){
            $originalImage = public_path('uploads/files/'.$img->directory.'/'.$img->name);
            if(file_exists($originalImage)){
                $original = Image::make($originalImage);
                $original->resize($height, $width, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($filePath);
            }
        }
        return url('uploads/caches/'.$img->directory.'/'.$height.'_'.$width.'/'.$img->name);
    }

    return ReturnDefaultImage($height,$width, $type);
}

function ReturnDefaultImage($height,$width, $type=null)
{
    if(!file_exists(public_path('uploads/caches/default'))){
        mkdir(public_path('uploads/caches/default'),0755);
    }
    if(!file_exists(public_path('uploads/caches/default/'.$height.'_'.$width))){
        mkdir(public_path('uploads/caches/default/'.$height.'_'.$width),0755);
    }
    if($type == 'user_image'){
        $filePath = public_path('uploads/caches/default/'.$height.'_'.$width.'/user_image.png');        
    }else{
        $filePath = public_path('uploads/caches/default/'.$height.'_'.$width.'/no_image.png');        
    }


    if(!file_exists($filePath)){
        if($type == 'user_image'){        
        $originalImage = public_path('uploads/default/user_image.png');
        }else{
        $originalImage = public_path('uploads/default/no_image.png');            
        }
        $original = Image::make($originalImage);
        $original->resize($height, $width, function ($constraint) {
            $constraint->aspectRatio();
        })->save($filePath);
    }
    if($type == 'user_image'){
    return url('uploads/caches/default/'.$height.'_'.$width.'/user_image.png');        
    }else{
    return url('uploads/caches/default/'.$height.'_'.$width.'/no_image.png');        
    }

}

function UploadImage($file,$dir,$module,$guard = 0)
{
    $imgId = 1;
    $lastRecord = images::latest()->first();
    if(!empty($lastRecord)){
        $imgId = $lastRecord->id;
    }

    $iname = explode('.',$file->getClientOriginalName())[0].'_'.time().'_'.date('Ymd').'_'.$imgId.'.'.$file->getClientOriginalExtension();

    $destinationPath = public_path("uploads/files/".$dir);
    
    if(!file_exists($destinationPath)){
        mkdir($destinationPath,0755);
    }
  
    $file->move($destinationPath, $iname);
    
    $record = images::create(['name'=>$iname,'module'=>$module,'directory'=>$dir,'is_admin'=>$guard]);

    return $record->id;
}
function UploadImageFromUrl($url,$dir,$module,$guard = 0)
{
    $imgId = 1;
    $lastRecord = images::latest()->first();
    if(!empty($lastRecord)){
        $imgId = $lastRecord->id;
    }
	
	  $url_array = explode("/", $url);
	  $image_name = end($url_array);
	  $image_array = explode(".", $image_name);
	  $extension = end($image_array);
	  $image_data = file_get_contents($url);
   
	
	
	
	
    $iname = explode('.',$image_name )[0].'_'.time().'_'.date('Ymd').'_'.$imgId.'.'.$extension;

    $destinationPath = public_path("uploads/files/post_images/");
	
    
    if(!file_exists($destinationPath)){
        mkdir($destinationPath,0755);
    }
	touch( $destinationPath . '/'.$iname );
	file_put_contents($destinationPath.'/'.$iname, $image_data);
   // $file->move($destinationPath, $iname);
    
    $record = images::create(['name'=>$iname,'module'=>$module,'directory'=>$dir,'is_admin'=>$guard]);

    return $record->id;
}

function UploadVideo($file,$dir,$module,$guard = 0)
{
    $videoId = 1;
    $lastRecord = videos::latest()->first();
    if(!empty($lastRecord)){
        $videoId = $lastRecord->id;
    }

    $iname = explode('.',$file->getClientOriginalName())[0].'_'.time().'_'.date('Ymd').'_'.$videoId.'.'.$file->getClientOriginalExtension();

    $destinationPath = public_path("uploads/videos/".$dir);
    
    if(!file_exists($destinationPath)){
        mkdir($destinationPath,0755);
    }
  
    $file->move($destinationPath, $iname);
    
    $record = videos::create(['name'=>$iname,'module'=>$module,'directory'=>$dir,'is_admin'=>$guard]);

    return $record->id;
}

function DeleteImage($imgId)
{
    if(!empty($imgId)){
        $img = images::find($imgId);
        if(!empty($img)){
           $path = public_path('uploads/files/'.$img->directory.'/'.$img->name);
           if(file_exists($path)){
               unlink($path);
               images::destroy($imgId);
               return true;
           }
        }
    }
    return false;
}

function DeleteVideo($videoId)
{
    if(!empty($videoId)){
        $video = videos::find($videoId);
        if(!empty($video)){
           $path = public_path('uploads/videos/'.$video->directory.'/'.$video->name);
           if(file_exists($path)){
               unlink($path);
               videos::destroy($videoId);
               return true;
           }
        }
    }
    return false;
}

function CheckModuleIsActiveOrNot($expression)
{
    $modules = Cache::get('modules');
    if(empty($modules)){
        $modules = modules::where('is_active',1)->get();
        Cache::forever('modules',$modules);
    }
    
    foreach($modules as $module){
        if($module->module_slug == $expression){
            return true;
        }
    }
    return false;
}

function ManagePermission($expression)
{
    if(Auth::guard('admin')->user()->role_id == 1){
        return true;
    }

    $forModule = explode('-',$expression);
    if(CheckModuleIsActiveOrNot($forModule[0])){
        
        $permissions = getPermissionArray();
    
        foreach($permissions as $permission){
            if($permission->role_id == Auth::guard('admin')->user()->role_id && $permission->per_slug == $expression){
                return true;
            }
        }
    }
    return false;
}

function getPermissionArray()
{
    $permissions = Cache::get('permissionArray');
    if(empty($permissions)){
        $permissions = permissions::join('role_permissions','role_permissions.permission_id','=','permissions.id')
                    ->select('role_permissions.role_id','permissions.per_slug')
                    ->get();
        Cache::forever('permissionArray', $permissions);
    }
    return $permissions;
}
// Compress image
function compressImage($source, $destination, $quality) {
  $info = getimagesize($source);
  if ($info['mime'] == 'image/jpeg') 
    $image = imagecreatefromjpeg($source);

  elseif ($info['mime'] == 'image/gif') 
    $image = imagecreatefromgif($source);

  elseif ($info['mime'] == 'image/png') 
    $image = imagecreatefrompng($source);

  imagejpeg($image, $destination, $quality);

}

?>