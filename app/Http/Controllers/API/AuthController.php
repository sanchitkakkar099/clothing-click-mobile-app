<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\User;
use App\user_logindevice_info;
use App\location;
use App\general_settings;
use App\api_otp_requests;
use App\email_otp_requests;
use Hash;
use App\user_sizes;
use App\user_favourite_brands;
use App\user_notification_settings;
use App\code;
use App\Store;
use DB;

class AuthController extends APIResponseController {

    public function login(Request $request) {

        $all = $request->all();

        $validator = Validator::make($request->all(), [
            'login_type' => 'required|numeric',
            'username' => 'required|required_if:login_type,1',
            'password' => 'nullable|required_if:login_type,1',
            'devices_type' => 'required',
            'devices_token' => 'nullable',
            'devices_name' => 'required',
            'os' => 'required',	
            'devices_id' => 'required',
            'app_version' => 'required',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try {

            //$loginField = 'email';
            if($all['login_type'] == 2){
                $loginField = 'facebook_id';
            }elseif($all['login_type'] == 3){
                $loginField = 'twitter_id';
            }elseif($all['login_type'] == 4){
                $loginField = 'google_id';
            }elseif($all['login_type'] == 5){
                $loginField = 'apple_id';
            }else{
                if (filter_var($request->username, FILTER_VALIDATE_EMAIL)) {
                    $loginField = 'email';
					$user = User::where([['email', $all['username']],['is_delete',0]])->orderby('id','desc')->first();
                }elseif(preg_match('/^[0-9]{10}+$/', request('username'))){
                    $loginField = 'phone';
                }else{
                    $loginField = 'username';
					$user = User::where([['username', $all['username']],['is_delete',0]])->orderby('id','desc')->first();
                }
				
            }
			$user = User::where([[$loginField, $request->username],['is_delete',0]])->orderby('id','desc')->first();
            
			
			//print_r($user);exit;
			
            
            if(!empty($user) && $all['login_type'] == 1){
				//echo "In if";exit;
                if($user->is_active == 0){
                    return $this->SendResponse(null, 'Your account is block, please contact admin to active.', 404);                    
                }         
                if($all['login_type'] == 1){
                    if (!Hash::check($all['password'], $user->password)) {
                        return $this->SendResponse(null, 'Please enter valid credentials', 406);
                    }
                }

                $token = $user->createToken(config('app.name'));
				//echo config('app.name');
				//echo "<pre>";print_r( $token); exit;
                $user['token'] = $token->accessToken;

                unset($user['created_at'],$user['updated_at'],$user['facebook_id'],$user['twitter_id'],$user['google_id'],$user['apple_id']);

                $all['access_token_id'] = $token->token->id;
                $all['appuser_id'] = $user->id;
                user_logindevice_info::create($all);

                $res = User::getAPIUserDetails($user);
                $res['token'] = $user['token'];
                $res['total_positive_review'] = $user->reviews()->where('rating', '>',2)->has('reviewBy')->count();
                
                return $this->SendResponse($res, 'Success', 200);

            }elseif($user && $all['login_type'] !== 1){
				$user = User::where([['email', $request->email],['is_delete',0]])->orderby('id','desc')->first();
				$all[$loginField] =$request->username;
				$all['role_id'] =1;
				if($user){
					$update_user = User::find($user->id)->update([$loginField => $all['username']]);
				}else{
					$all['is_first_login'] = 1;
				    $user = User::create($all);
				}
				$token = $user->createToken('Clothes');  
                $user['token'] = $token->accessToken;

                unset($user['created_at'],$user['updated_at'],$user['facebook_id'],$user['twitter_id'],$user['google_id'],$user['apple_id']);

                $all['access_token_id'] = $token->token->id;
                $all['appuser_id'] = $user->id;
                user_logindevice_info::create($all);

                $res = User::getAPIUserDetails($user);
                $res['token'] = $user['token'];
                $res['total_positive_review'] = $user->reviews()->where('rating', '>',2)->has('reviewBy')->count();
                
                return $this->SendResponse($res, 'Success', 200);
			}elseif(empty($user) && $all['login_type'] !== 1){
				$user = User::where([['email', $request->email],['is_delete',0]])->orderby('id','desc')->first();
				$all[$loginField] =$request->username;
				$all['role_id'] =1;
				if($user){
					$update_user = User::find($user->id)->update([$loginField => $all['username']]);
				}else{ 
				$all['is_first_login'] = 1;
				    $user = User::create($all);
				}
				$token = $user->createToken('Clothes');  
                $user['token'] = $token->accessToken;

                unset($user['created_at'],$user['updated_at'],$user['facebook_id'],$user['twitter_id'],$user['google_id'],$user['apple_id']);

                $all['access_token_id'] = $token->token->id;
                $all['appuser_id'] = $user->id;
                user_logindevice_info::create($all);

                $res = User::getAPIUserDetails($user);
                $res['token'] = $user['token'];
                $res['total_positive_review'] = $user->reviews()->where('rating', '>',2)->has('reviewBy')->count();
                
                return $this->SendResponse($res, 'Success', 200);
			}else{
				//echo "In else";exit;

            return $this->SendResponse(null, 'Look like you are not registred with us. please complete signup process.', 404);
			}

        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function signup(Request $request) {

        $all = $request->all();

        $validator = Validator::make($all, [
            'signup_type' => 'required|numeric',
            'role_id' => 'required|numeric',
            'email' => 'required|email|string|max:155|unique:users',
            'username' => 'required|unique:users',
            'password' => 'nullable|required_if:signup_type,1|string|min:8|max:55|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'name' => 'required|max:155',
            'facebook_id' => 'nullable|required_if:signup_type,2',
            'google_id' => 'nullable|required_if:signup_type,4',
            'twitter_id' => 'nullable|required_if:signup_type,3',
            'apple_id' => 'nullable|required_if:signup_type,5',
            'devices_type' => 'required',
            'devices_token' => 'nullable',
            'devices_name' => 'required',
            'os' => 'required',
            'devices_id' => 'required',
            'app_version' => 'required',
            'locations' => 'required|json'
        ]);

        if($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        if($request->has('access_code') && $request->access_code !=null){
            $code = Code::with('users')->where('name',$request->access_code)->first();
        
            if(!$code){
                return $this->SendResponse(null, 'Invalid Access Code', 403);                
            }
            if($code->limit <= $code->users()->count()){
                return $this->SendResponse(null, 'Access Code Limit is reach', 403);            
            }
        }
        
        try{

            if(!isset($all['signup_type']) && empty($all['signup_type'])){
                $all['signup_type'] = 1;
            }

            if($all['signup_type'] == 1){
                $all['password'] = bcrypt($all['password']);
            }
            if(isset($all['role_id']) && $all['role_id'] == 3){
                $all['is_order'] = 1;
            }
            //$all['coins'] = 100000;
             $all['password'] = Hash::make($request->password);
            $user = User::create($all);

            if($request->has('access_code') && $request->access_code !=null){
                    $user->access_code = $request->access_code;
                    $user->save();
            }
                
            $all['appuser_id'] = $user->id;
            $all['user_id'] = $user->id;

            $token = $user->createToken(config('app.name'));

            $all['access_token_id'] = $token->token->id;
            user_logindevice_info::create($all);
            
            $user['token'] = $token->accessToken;

            //Create location
            $locations = json_decode($all['locations']);
            if(!empty($locations)){
                foreach($locations as $key => $location){
                    
                    $isDefault = 0;
                    if($key == 0){
                        $isDefault = 1;
                    }

                    $isPaid = 1;
                    $locationPrice = 0;

                    if($all['role_id'] == 2 && $isDefault == 0){
                        $isPaid = 0;
                        $locationPrice = config('constants.Store_Monthly_Rate');
                    }elseif($all['role_id'] == 3 && $isDefault == 0){
                        $isPaid = 0;
                        $locationPrice = config('constants.Brand_Monthly_Rate');
                    }

                    location::create([
                        'appuser_id' => $user->id,
                        'address' => $location->address ?? null,
                        'city' => $location->city ?? null,
                        'area' => $location->area ?? null,
                        'postal_code' => $location->postal_code ?? null,
                        'latitude' => $location->latitude ?? null,
                        'longitude' => $location->longitude ?? null,
                        'is_default' => $isDefault,
                        'is_paid' => $isPaid,
                        'price' => $locationPrice
                    ]);
                }
            }

            user_notification_settings::create($all);

            $otp = GenerateOTP(6);
            $emailOtp = email_otp_requests::create([
                'user_id' => $user->id,
                'otp' => $otp
            ]);

            $res = User::getAPIUserDetails($user);
            $res['token'] = $user['token'];

            return $this->SendResponse($res, 'Success', 200);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function signupSteptwo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:10|unique:users,phone,'.Auth::user()->id,
            'country_code' => 'required|string|max:10',
            'country_prefix' => 'required|string|max:10',
            'is_test' => 'nullable|numeric|max:1'
        ]);

        if($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{

            $all = $request->all();
            $all['phone'] = $all['phone'];

            //User::find(Auth::user()->id)->update(['phone' => $all['phone'],'country_code' => $all['country_code'], 'country_prefix' => $all['country_prefix']]);

            $otp = GenerateOTP(6);
            //Update OTP in DB
          
			api_otp_requests::updateOrCreate(
				['user_id' =>  Auth::user()->id],
				['otp' => $otp, 'phone' => $request->phone, 'country_code' => $request->country_code, 'country_prefix' => $request->country_prefix]
			);


            $message = 'Your one time password is '.$otp;
            
            $all['phone'] = $all['country_prefix'].$all['phone'];
            
            $res = User::getAPIUserDetails(Auth::user());

            $res['otp'] = $otp;
			sendTwillioSMS($all['phone'],$message);
            /* if(isset($all['is_test']) && !empty($all['is_test'])){
                //$res['otp'] = null;
                //try{
                    sendTwillioSMS($all['phone'],$message);
                //}catch(\Exception $e){
                    
                //}
            } */
            
            return $this->SendResponse($res, 'One time password successfully sent on '.$all['phone'], 200);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function verify_user_phone(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|string|min:6|max:6',
        ]);

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try {
            $otp = api_otp_requests::where([['user_id', Auth::user()->id], ['otp', $request->otp]])->orderBy('id', 'desc')->first();
            if (!empty($otp)) {
                
                User::find(Auth::user()->id)->update(['phone_verified_at' => date('Y-m-d H:i:S'),         'phone' => $otp->phone,
							'country_code' => $otp->country_code,
							'country_prefix' => $otp->country_prefix]);
                api_otp_requests::destroy($otp->id);
                

                $res = User::getAPIUserDetails(Auth::user());
    
                return $this->SendResponse($res, 'Phone number successfully verified', 200);
            } else {
                return $this->SendResponse(null, 'Please enter valid one time password.', 422);
            }
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function userDetail(Request $request)
    {
        try{

            $all = $request->all();

            $user = User::getAPIUserDetails(Auth::user(),false);
            return $this->SendResponse($user, 'Success', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email|string|max:155|unique:users,email,'.Auth::user()->id,
            'phone' => 'nullable|string|max:10|unique:users,phone,'.Auth::user()->id,
            'country_code' => 'nullable|string|max:10',
            'country_prefix' => 'nullable|string|max:10',
            'username' => 'required|string|unique:users,username,'.Auth::user()->id,
            'name' => 'required|string|max:155',
            'bio' => 'nullable|string|max:5000',
            'website' => 'nullable|string|max:2000',
            'facebook_id' => 'nullable|string|max:2000',
            'instagram_id' => 'nullable|string|max:2000',
            'locations' => 'required|json',
            'deleted_location_ids' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{
            
            $all = $request->all();

            if(isset($all['photo']) && !empty($all['photo'])){
                $all['photo'] = UploadImage($all['photo'],'app_user_profile','registration');
                if(!empty(Auth::user()->photo)){
                    DeleteImage(Auth::user()->photo);
                }
            }

            if(isset($all['phone']) && !empty($all['phone'])){
                if($all['phone'] != Auth::user()->phone){
                    $all['phone_verified_at'] = null;
                }
            }

            if(isset($all['email']) && !empty($all['email'])){
                if($all['email'] != Auth::user()->email){
                    $all['email_verified_at'] = null;
                }
            }

            User::find(Auth::user()->id)->update($all);

            if(isset($all['deleted_location_ids']) && !empty($all['deleted_location_ids'])){
                $deletedLocationIds = explode(',',$all['deleted_location_ids']);
                location::whereIn('id',$deletedLocationIds)->update(['is_delete' => 1]);
            }

            if(isset($all['locations']) && !empty($all['locations'])){
                $all['locations'] = json_decode($all['locations']);
                foreach($all['locations'] as $key => $location){
                    $locationArr = [
                        'appuser_id' => Auth::user()->id,
                        'address' => $location->address ?? null,
                        'city' => $location->city ?? null,
                        'area' => $location->area ?? null,
                        'postal_code' => $location->postal_code ?? null,
                        'latitude' => $location->latitude ?? null,
                        'longitude' => $location->longitude ?? null
                    ];

                    if(isset($location->id) && !empty($location->id)){
                        if(Auth::user()->role_id == 1){
                            
                            location::where([['appuser_id',Auth::user()->id]])->update(['is_default'=>0]);
                                $last_location = location::create($locationArr);
                                $last_location->is_default = 1;
                                $last_location->save();
                                
                        }else{
                            location::find($location->id)->update($locationArr);
                        }
                    }else{
                        if(Auth::user()->role_id == 1){
                            $lastLocation = location::where([['appuser_id',Auth::user()->id],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();
                            if(!empty($lastLocation)){
                                //location::find($lastLocation->id)->update($locationArr);
                            }
                            // update users location to default 0;
                            location::where([['appuser_id',Auth::user()->id]])->update(['is_default'=>0]);
                                $last_location = location::create($locationArr);
                                $last_location->is_default = 1;
                                $last_location->save();
                        }else{
                            
                            if(Auth::user()->role_id == 2){
                                $locationArr['price'] = config('constants.Store_Monthly_Rate');
                            }else{
                                $locationArr['price'] = config('constants.Brand_Monthly_Rate');
                            }
                            $locationArr['is_paid'] = 0; 

                            location::create($locationArr);
                        }
                    }
                }

                $checkDefaultLocation = Auth::user()->locations()->where([['is_active',1],['is_delete',0],['is_default',1]])->count();
                if($checkDefaultLocation == 0){
                    $firstLocation = location::where([['appuser_id',Auth::id()],['is_active',1],['is_delete',0]])->orderby('id','asc')->first();
                    if(!empty($firstLocation)){
                        location::find($firstLocation->id)->update(['is_default' => 1, 'is_paid' => 1, 'expire_at' => null]);
                    }
                }

            }

            $res = User::getAPIUserDetails(Auth::user());

            return $this->SendResponse($res, 'Profile detail updated successfully.', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function forgot_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try {

            $all = $request->all();
            $all['phone'] = $all['phone'];

            $user = User::where([['phone',$all['phone']],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();

            if(!empty($user)){
                
                $link = url('trouble_resetpassword/' . base64_encode($user->id));
               $message = 'Here is your reset password link '.$link.', Please copy and paste the link into an internet browser.';                
                // $message = 'Here is your reset password link '.$link;

                $phone = $user->country_prefix.$user->phone;

                sendTwillioSMS($phone,$message);

                return $this->SendResponse(null, 'Reset password link has been sent on registred phone number.', 200);

            }

            return $this->SendResponse(null, 'Look like you are not registred with us.', 404);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function logout(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'devices_id' => 'required|string',
                'is_all' => 'required|numeric|max:1'
            ]);
    
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            if(request('is_all') == 0){

                if(isset($request->devices_id) && !empty($request->devices_id)){
                    user_logindevice_info::where([['appuser_id',Auth::user()->id],['devices_id',$request->devices_id]])->delete();
                }
                $request->user()->token()->revoke();
                $request->user()->token()->delete();

            }else{

                user_logindevice_info::where('appuser_id',Auth::user()->id)->delete();

                foreach($request->user()->tokens as $token){
                    $token->revoke();
                    $token->delete();
                }

            }

            return $this->SendResponse(null, 'Successfully logout', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function logoutDeviceUsers(Request $request){
        try {

            $validator = Validator::make($request->all(), [
                'devices_id' => 'required|string',
            ]);
    
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $devices = user_logindevice_info::where('devices_id',$request->devices_id)->get();

            foreach($devices as $device){
                DB::table('oauth_access_tokens')->where('id',$device->access_token_id)->delete();
                $device->delete();
            }

            return $this->SendResponse(null, 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function change_password(Request $request) {
        $validator = Validator::make($request->all(), [
           // 'old_password' => 'required|string|min:8|max:55',
            'new_password' => 'required|string|min:8|max:55|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        ]);
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }
        try {
			// $user = User::where([['phone',$all['phone']],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();			 
           // if (Hash::check($request->old_password, Auth::user()->password)) {
            User::find(Auth::user()->id)->update(["password" => Hash::make($request->new_password)]);
            return $this->SendResponse(null, 'Password successfully changed', 200);
            //} else {
              //  return $this->SendResponse(null, 'Please enter correct old password.', 422);
            //}
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function changeProfileStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|numeric|max:1',
        ]);

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try {

            $all = $request->all();

            Auth::user()->update(['is_private' => $all['status']]);

            $res = User::getAPIUserDetails(Auth::user());
            
            return $this->SendResponse($res, 'Profile status changed successfully', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function changeLocationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|numeric|max:1',
        ]);

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try {

            $all = $request->all();

            Auth::user()->update(['is_findlocation_on' => $all['status']]);

            $res = User::getAPIUserDetails(Auth::user());
            
            return $this->SendResponse($res, 'Profile location status changed successfully', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function changeEmailStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|numeric|max:1',
        ]);

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try {

            $all = $request->all();

            Auth::user()->update(['is_email_show' => $all['status']]);

            $res = User::getAPIUserDetails(Auth::user());
            
            return $this->SendResponse($res, 'Email status changed successfully', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function changePhoneStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|numeric|max:1',
        ]);

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try {

            $all = $request->all();

            Auth::user()->update(['is_phone_show' => $all['status']]);

            $res = User::getAPIUserDetails(Auth::user());
            
            return $this->SendResponse($res, 'Phone status changed successfully', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function updateSocialAccountId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|numeric|max:4|min:1',
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try {

            $all = $request->all();

            switch($all['type']){
                case 1:
                    $field = 'facebook_id';
                    break;
                case 2:
                    $field = 'google_id';
                    break;
                case 3:
                    $field = 'twitter_id';
                    break;
                case 4:
                    $field = 'apple_id';
                    break;
            }

            Auth::user()->update([$field => $all['id']]);

            $res = User::getAPIUserDetails(Auth::user());
            
            return $this->SendResponse($res, 'Social account id updated successfully.', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function resendEmailVerificationLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_email_change' => 'required|numeric|max:1|min:0',
            'email' => 'nullable|required_if:is_email_change,1|email|unique:users,email,'.Auth::user()->id,
        ]);

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{

            $all = $request->all();

            if(isset($all['is_email_change']) && !empty($all['is_email_change'])){
                Auth::user()->update(['email' => $all['email']]);
            }

            email_otp_requests::where('user_id',Auth::id())->delete();

            $otp = GenerateOTP(6);
            $emailOtp = email_otp_requests::create([
                'user_id' => Auth::id(),
                'otp' => $otp
            ]);

            $link = route('verifyEmailLink',['user_id' => encrypt(Auth::id()), 'otp' => encrypt($otp)]);

            return $this->SendResponse(null, 'Email varification link has been sent your email address.', 200);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
    
    public function checkAccessCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'access_code' => 'required|exists:codes,name',
        ]);

        if($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        $code = Code::with('users')->where('name',$request->access_code)->first();

        if($code->limit <= $code->users()->count()){
            return $this->SendResponse(null, 'Access Code Limit is reach', 400);            
        }
        $res = $code;
        return $this->SendResponse($res, 'Success', 200);        

    }    

    public function ConvertToStore(Request $request)
    {
		 $all = $request->all();
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'email' => 'required',
        ]);

        if($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }
		Auth::user()->update(['role_id' => 8]);
		$all['user_id'] = auth()->user()->id;
		$all['is_active'] = 0;
		$store = Store::create($all);
		return $this->SendResponse(null, 'Your profile has been converted to store', 200);            
        
    }    

}
