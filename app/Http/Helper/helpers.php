<?php

use Illuminate\Support\Facades\Log;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
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
function SendFirebaseNotification($title, $msg, $tokens, $data = [])
{
    if (empty($tokens)) {
        Log::info('FCM: No device tokens found');
        return false;
    }

    $projectId = env('FIREBASE_PROJECT_ID');

    // ✅ Decode JSON from ENV
    $json = base64_decode(env('FIREBASE_SERVICE_ACCOUNT_BASE64'));
    $serviceAccount = json_decode($json, true);

    if (!$serviceAccount) {
        Log::error('FCM: Invalid service account JSON from env');
        return false;
    }

    $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

    $credentials = new ServiceAccountCredentials($scopes, $serviceAccount);
    $auth = $credentials->fetchAuthToken();

    if (empty($auth['access_token'])) {
        Log::error('FCM OAuth failed', $auth);
        return false;
    }

    $accessToken = $auth['access_token'];

    foreach ($tokens as $token) {
        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $msg,
                ],
			    'apns' => [
						'payload' => [
							'aps' => [
								'sound' => 'handoff.mp3', // or custom sound file
							],
						],
					],

                'data' => array_map('strval', $data),
            ],
        ];

       $response = Http::withToken($accessToken)
    ->post(
        "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
        $payload
    );

	Log::info('FCM Send Result', [
		'token' => substr($token, 0, 10) . '...',
		'status' => $response->status(),
		'response' => $response->json(),
	]);

    }

    return true;
}




function getUsersFCMTokens($userId)
{
    if (empty($userId)) {
        return [];
    }

    return user_logindevice_info::where('appuser_id', $userId)
        ->whereNotNull('devices_token')
        ->pluck('devices_token')
        ->unique()
        ->values()
        ->toArray();
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
if (!function_exists('sendMandrillOTP')) {
    function sendMandrillOTP($toEmail, $userName, $otp)
    {
		   \Log::info('sendMailjetOTP helper called');

        try {
			$mj = new \Mailjet\Client(
				config('services.mailjet.key'),
				config('services.mailjet.secret'),
				true,
				['version' => 'v3.1'] 
			);

            $html = "
                <h2>Hello {$userName},</h2>
                <p>Your One Time Password (OTP) is:</p>
                <h1 style='letter-spacing:3px;'>{$otp}</h1>
                <p>This OTP is valid for 10 minutes.</p>
                <br>
                <p>Regards,<br>" . config('services.mailjet.from_name') . "</p>
            ";

            $body = [
                'Messages' => [[
                    'From' => [
                        'Email' => config('services.mailjet.from_address'),
                        'Name'  => config('services.mailjet.from_name'),
                    ],
                    'To' => [[
                        'Email' => $toEmail,
                        'Name'  => $userName,
                    ]],
                    'Subject' => 'Your OTP Verification Code',
                    'HTMLPart' => $html,
                ]]
            ];

            $response = $mj->post(\Mailjet\Resources::$Email, ['body' => $body]);

            if (!$response->success()) {
                \Log::error('Mailjet OTP API Error', $response->getData());
                return false;
            }

            return true;

        } catch (\Exception $e) {
            \Log::error('Mailjet OTP Exception: ' . $e->getMessage());
            return false;
        }
    }
} 
if (!function_exists('sendMailjetResetPassword')) {
    function sendMailjetResetPassword($toEmail, $userName, $resetLink)
    {
        \Log::info('sendMailjetResetPassword helper called');

        try {
            $mj = new \Mailjet\Client(
                config('services.mailjet.key'),
                config('services.mailjet.secret'),
                true,
                ['version' => 'v3.1']
            );

            $html = "
                <h2>Hello {$userName},</h2>
                <p>You requested to reset your password.</p>
                <p>Click the link below to reset it:</p>
                <p>
                    <a href='{$resetLink}' style='
                        background:#4F46E5;
                        color:#ffffff;
                        padding:12px 20px;
                        text-decoration:none;
                        border-radius:5px;
                        display:inline-block;
                    '>Reset Password</a>
                </p>
                <p>If the button does not work, copy and paste this link into your browser:</p>
                <p>{$resetLink}</p>
                <br>
                <p>This link will expire soon for security reasons.</p>
                <br>
                <p>Regards,<br>" . config('services.mailjet.from_name') . "</p>
            ";

            $body = [
                'Messages' => [[
                    'From' => [
                        'Email' => config('services.mailjet.from_address'),
                        'Name'  => config('services.mailjet.from_name'),
                    ],
                    'To' => [[
                        'Email' => $toEmail,
                        'Name'  => $userName,
                    ]],
                    'Subject' => 'Reset Your Password',
                    'HTMLPart' => $html,
                ]]
            ];

            $response = $mj->post(\Mailjet\Resources::$Email, ['body' => $body]);

            if (!$response->success()) {
                \Log::error('Mailjet Reset Password API Error', $response->getData());
                return false;
            }

            return true;

        } catch (\Exception $e) {
            \Log::error('Mailjet Reset Password Exception: ' . $e->getMessage());
            return false;
        }
    }
}
?>