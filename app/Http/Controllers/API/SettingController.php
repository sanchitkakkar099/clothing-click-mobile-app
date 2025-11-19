<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\user_notification_settings;
use App\User;

class SettingController extends APIResponseController {

    public function updateNotification(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'notification_type' => 'required|numeric|max:7',
                'value' => 'required|numeric|max:1'
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $update = [];
            switch($all['notification_type']){
                case 1:
                    $update['is_all'] = $all['value'];
                    $update['is_save_search'] = $all['value'];
                    $update['is_chat'] = $all['value'];
                    $update['is_follower'] = $all['value'];
                    $update['is_rating'] = $all['value'];
                    $update['is_watchlist'] = $all['value'];
                    $update['is_coin'] = $all['value'];
                    break;
                case 2:
                    $update['is_save_search'] = $all['value'];
                    break;
                case 3:
                    $update['is_chat'] = $all['value'];
                    break;
                case 4:
                    $update['is_follower'] = $all['value'];
                    break;
                case 5:
                    $update['is_rating'] = $all['value'];
                    break;
                case 6:
                    $update['is_watchlist'] = $all['value'];
                    break;
                case 7:
                    $update['is_coin'] = $all['value'];
                    break;
            }
            
            if(!empty($update)){
                if($all['value'] == 0){
                    $update['is_all'] = $all['value'];
                }
                user_notification_settings::where('user_id',Auth::user()->id)->update($update);

                if($all['value'] == 1){
                    $n = user_notification_settings::where('user_id',Auth::user()->id)->first();
                    if($n->is_save_search == 1 && $n->is_chat == 1 && $n->is_follower == 1 && $n->is_rating == 1 && $n->is_watchlist == 1 && $n->is_coin == 1){
                        user_notification_settings::where('user_id',Auth::user()->id)->update(['is_all' => 1]);
                    }
                }

                $res = User::getAPIUserDetails(Auth::user());

                return $this->SendResponse($res, 'Success', 200);
            }

            return $this->SendResponse(null, 'Invalid input', 400);

        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function chatOrderSetting(Request $request)
    {
        try{
            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'setting_type' => 'required|numeric|max:2',
                'value' => 'required|numeric|max:1'
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            if($all['setting_type'] == 1){
                $update = ['is_order' => $all['value']];
            }else{
                $update = ['is_chat' => $all['value']];
            }

            Auth::user()->update($update);

            $res = User::getAPIUserDetails(Auth::user());

            return $this->SendResponse($res, 'Setting changed successfully', 200);
            
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

}
