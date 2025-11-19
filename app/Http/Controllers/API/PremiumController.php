<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\User;
use App\user_cards;
use App\user_transactions;
use App\user_premium_logs;

class PremiumController extends APIResponseController {

    public function buy(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($request->all(), [
            'is_trial' => 'required|numeric|max:1',
            'card_id' => 'nullable|required_if:is_trial,0|numeric|min:1',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{

            $isPaid = 0;
            $amount = 0;
            $isSuccess = 0;

            if($all['is_trial'] == 1){

                if(Auth::user()->is_premium_activated == 1){
                    return $this->SendResponse(null, 'You have already activated premium subscription before so you are not eligble for trial period.', 400);
                }

                $startAt = date('Y-m-d H:i:s');
                $endAt = date('Y-m-d H:i:s',strtotime('+ '.config('constants.PREMIUM_SUBSCRIPTION_FREE_TRIAL_DAYS').' days'));

                $isSuccess = 1;
                $isActivePackage = 1;
                $message = 'Your free trial for premium is activated for '.config('constants.PREMIUM_SUBSCRIPTION_FREE_TRIAL_DAYS').' days';

            }else{

                $card = user_cards::where([['id',$all['card_id']],['user_id',Auth::user()->id],['is_active',1],['is_delete',0]])->first();

                if(empty($card)){
                    return $this->SendResponse(null, 'Invalid card id', 400);
                }

                $transactionId = null;
                $errorMessage = null;

                $stripe = getStripeObject();

                try{
                    $charge = $stripe->charges->create([
                        'amount' => config('constants.PREMIUM_SUBSCRIPTION_PRICE') * 100,
                        'currency' => config('constants.STRIPE_CURRENCY_CODE'),
                        'source' => $card->stripe_card_id,
                        'customer' => $card->stripe_customer_id,
                        'description' => 'Buy Premium Subscription',
                    ]);
    
                    $isSuccess = 1;
                    $isPaid = 1;
                    $amount = config('constants.PREMIUM_SUBSCRIPTION_PRICE');
                    $transactionId = $charge->id;

                    $message = 'Your free trial for premium is activated';
                    
                    $checkAlreadySubscribed = user_premium_logs::where([['user_id',Auth::user()->id],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();
                    
                    if(empty($checkAlreadySubscribed)){
                        $startAt = date('Y-m-d H:i:s');
                        $endAt = date('Y-m-d H:i:s',strtotime('+1 month'));
                        $isActivePackage = 1;
                    }else{
                        $startAt = date('Y-m-d H:i:s', strtotime("+1 day", strtotime(Auth::user()->premium_end_at)));
                        $endAt = date('Y-m-d H:i:s', strtotime("+1 month", strtotime($startAt)));
                        $isActivePackage = 0;
                    }
                    
                }catch(\Exception $e){
                    $charge = $e;
                    $errorMessage = $e->getMessage();
                }

                $transaction = user_transactions::create([
                    'type' => 'BUY_PREMIUM_SUBSCRIPTION',
                    'user_id' => Auth::user()->id,
                    'amount' => config('constants.PREMIUM_SUBSCRIPTION_PRICE'),
                    'is_success' => $isSuccess,
                    'transaction_id' => $transactionId,
                    'response' => json_encode($charge),
                    'card_brand' => $card->brand,
                    'card_last_four' => $card->last_four,
                    'card_name' => $card->name,
                    'message' => $errorMessage
                ]);

                if($isSuccess == 0){
                    return $this->SendResponse(null, $errorMessage, 400);
                }

            }

            if($isSuccess == 1){

                Auth::user()->update(['is_premium_user' => 1, 'is_premium_activated' => 1, 'premium_end_at' => $endAt]);

                $log = user_premium_logs::create([
                    'user_id' => Auth::user()->id,
                    'is_paid' => $isPaid,
                    'amount' => $amount,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'is_active' => $isActivePackage,
                    'is_delete' => 0
                ]);

                if($isPaid == 1 && isset($transaction) && !empty($transaction)){
                    user_transactions::find($transaction->id)->update(['log_id' => $log->id]);
                }
            }
            
            return $this->SendResponse(null, $message, 200);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    

}
