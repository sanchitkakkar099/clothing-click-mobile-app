<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\User;
use App\location;
use App\user_location_payment_logs;
use App\user_transactions;
use App\user_cards;

class LocationController extends APIResponseController {

    public function selectLocation(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($request->all(), [
            'location_id' => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{

            $location = location::where([['appuser_id',Auth::user()->id],['id',$all['location_id']],['is_active',1],['is_delete',0]])->first();

            if(!empty($location)){
                location::where('appuser_id',Auth::user()->id)->update(['is_default' => 0]);
                location::where([['appuser_id',Auth::user()->id],['id',$all['location_id']]])->update(['is_default' => 1]);
                
                $res = User::getAPIUserDetails(Auth::user());
                return $this->SendResponse($res, 'Success', 200);
            }

            return $this->SendResponse(null, 'Invalid location id', 404);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function payLocation(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($request->all(), [
            'location_id' => 'required|numeric',
            'card_id' => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{

            $checkLocation = location::where([['appuser_id',Auth::id()],['id',$all['location_id']],['is_delete',0]])->first();

            if(empty($checkLocation)){
                return $this->SendResponse(null, 'Invalid location id', 400);
            }

            if($checkLocation->is_paid == 1 && is_null($checkLocation->expire_at)){
                return $this->SendResponse(null, 'This is your free location, you dont need to pay for this.', 400);
            }

            $card = user_cards::where([['id',$all['card_id']],['user_id',Auth::user()->id],['is_active',1],['is_delete',0]])->first();

            if(empty($card)){
                return $this->SendResponse(null, 'Invalid card id', 400);
            }

            $price = config('constants.Store_Monthly_Rate');
            if(Auth::user()->role_id == 3){
                $price = config('constants.Brand_Monthly_Rate');
            }

            $isSuccess = 0;
            $transactionId = null;
            $errorMessage = null;
            $logId = null;

            $stripe = getStripeObject();

            try{
                $charge = $stripe->charges->create([
                    'amount' => $price * 100,
                    'currency' => config('constants.STRIPE_CURRENCY_CODE'),
                    'source' => $card->stripe_card_id,
                    'customer' => $card->stripe_customer_id,
                    'description' => 'Location Payment',
                ]);

                $isSuccess = 1;
                $transactionId = $charge->id;

            }catch(\Exception $e){
                $charge = $e;
                $errorMessage = $e->getMessage();
            }

            if($isSuccess == 1){

                if($checkLocation->expire_at >= date('Y-m-d H:i:s')){
                    $startAt = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($checkLocation->expire_at)));
                    $endAt = date('Y-m-d H:i:s', strtotime("+1 month", strtotime($startAt)));
                }else{
                    $startAt = date('Y-m-d H:i:s');
                    $endAt = date('Y-m-d H:i:s',strtotime('+1 month'));
                }

                location::find($checkLocation->id)->update(['is_paid' => 1, 'expire_at' => $endAt, 'is_active' => 1, 'price' => $price]);

                $log = user_location_payment_logs::create([
                    'user_id' => Auth::id(),
                    'location_id' => $checkLocation->id,
                    'amount' => $price,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                ]);

                $logId = $log->id;
            }


            user_transactions::create([
                'type' => 'PAY_LOCATION',
                'user_id' => Auth::user()->id,
                'amount' => $price,
                'is_success' => $isSuccess,
                'transaction_id' => $transactionId,
                'response' => json_encode($charge),
                'card_brand' => $card->brand,
                'card_last_four' => $card->last_four,
                'card_name' => $card->name,
                'message' => $errorMessage,
                'log_id' => $logId,
                'resource_id' => $checkLocation->id
            ]);

            if($isSuccess == 1){
                return $this->SendResponse(null, 'Your location payment was successfull.', 200);
            }

            return $this->SendResponse(null, $errorMessage, 400);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function addLocation(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($request->all(), [
            'address' => 'required|string',
            'city' => 'nullable|string',
            'area' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{
            
            $all['appuser_id'] = Auth::id();

            if(Auth::user()->role_id == 1){
                $all['is_paid'] = 1;
                $all['expire_at'] = null;
            }elseif(Auth::user()->role_id == 2){
                $all['price'] = config('constants.Store_Monthly_Rate');
            }elseif(Auth::user()->role_id == 3){
                $all['price'] = config('constants.Brand_Monthly_Rate');
            }

            location::create($all);

            $checkDefaultLocation = Auth::user()->locations()->where([['is_active',1],['is_delete',0],['is_default',1]])->count();
            if($checkDefaultLocation == 0){
                $firstLocation = location::where([['appuser_id',Auth::id()],['is_active',1],['is_delete',0]])->orderby('id','asc')->first();
                if(!empty($firstLocation)){
                    location::find($firstLocation->id)->update(['is_default' => 1, 'is_paid' => 1, 'expire_at' => null]);
                }
            }

            $res = User::getAPIUserDetails(Auth::user());

            return $this->SendResponse($res, 'Location added successfully', 200);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function deleteLocation(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($request->all(), [
            'location_id' => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{

            $check = location::where([['id',$all['location_id']],['appuser_id',Auth::id()],['is_delete',0]])->first();

            if(!empty($check)){

                $check->is_delete = 1;
                $check->update();

                $checkDefaultLocation = Auth::user()->locations()->where([['is_active',1],['is_delete',0],['is_default',1]])->count();
                if($checkDefaultLocation == 0){
                    $firstLocation = location::where([['appuser_id',Auth::id()],['is_active',1],['is_delete',0]])->orderby('id','asc')->first();
                    if(!empty($firstLocation)){
                        location::find($firstLocation->id)->update(['is_default' => 1, 'is_paid' => 1, 'expire_at' => null]);
                    }
                }

                $res = User::getAPIUserDetails(Auth::user());

                return $this->SendResponse($res, 'Location added successfully', 200);

            }

            return $this->SendResponse(null, 'Invalid location id', 400);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
}
