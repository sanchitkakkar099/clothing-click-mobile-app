<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\coins;
use App\User;
use App\user_cards;
use App\user_transactions;
use App\user_coin_transactions;

class CoinController extends APIResponseController {

    public function index(Request $request)
    {
        ini_set( 'serialize_precision', -1 );

        $getCoins = coins::where([['is_active',1],['is_delete',0]])->orderby('id','asc')->get();

        $data = [];
        foreach($getCoins as $coin){
            array_push($data,[
                'id' => $coin->id,
                'name' => $coin->name,
                'price' => (double)$coin->price,
                'photo' => ResizeImage($coin->photo,500,500)
            ]);
        }

        $coinInfo = [
            ['title' => 'Post three items', 'price' => (double)config('constants.COIN_POST_THREE_ITEMS') ],
            ['title' =>'Get your first follower', 'price' => (double)config('constants.COIN_GET_YOUR_FIRST_FOLLOWER')],
            ['title' =>'Sell three items', 'price' => (double)config('constants.COIN_SELL_THREE_ITEMS')],
            ['title' =>'Share this app with friends', 'price' => (double)config('constants.COIN_SHARE_APP_WITH_FRIENDS')],
            ['title' =>'Post fifteen items', 'price' => (double)config('constants.COIN_POST_FIFTEEN_ITEMS')],
            ['title' =>'Get 5 followers', 'price' => (double)config('constants.COIN_GET_FIVE_FOLLOWERS')],
            ['title' =>'Rate a user', 'price' => (double)config('constants.COIN_RATE_USER')],
            ['title' =>'Rate us on app store', 'price' => (double)config('constants.COIN_RATE_US_ON_APP_STORE')],
        ];

        $res['earn_coin_info'] = $coinInfo;
        $res['PREMIUM_PRICE'] = round(floatval(config('constants.PREMIUM_SUBSCRIPTION_PRICE')),2);
        $res['PREMIUM_FREE_TRAIL_DAYS'] = (int)config('constants.PREMIUM_SUBSCRIPTION_FREE_TRIAL_DAYS');
        $res['coins'] = $data;

        return $this->SendResponse($res, 'Success', 200);
    }

    public function buy(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($request->all(), [
            'card_id' => 'required|numeric|min:1',
            'coin_id' => 'required|numeric|min:1',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{

            $coin  = coins::where([['id',$all['coin_id']],['is_active',1],['is_delete',0]])->first();

            if(empty($coin)){
                return $this->SendResponse(null, 'Invalid coin id', 400);
            }

            $card = user_cards::where([['id',$all['card_id']],['user_id',Auth::user()->id],['is_active',1],['is_delete',0]])->first();

            if(empty($card)){
                return $this->SendResponse(null, 'Invalid card id', 400);
            }

            $isSuccess = 0;
            $transactionId = null;
            $errorMessage = null;
            $logId = null;

            $stripe = getStripeObject();

            try{
                $charge = $stripe->charges->create([
                    'amount' => $coin->price * 100,
                    'currency' => config('constants.STRIPE_CURRENCY_CODE'),
                    'source' => $card->stripe_card_id,
                    'customer' => $card->stripe_customer_id,
                    'description' => 'Buy Click Coins',
                ]);

                $isSuccess = 1;
            }catch(\Exception $e){
                $charge = $e;
                $errorMessage = $e->getMessage();
            }
            
            if($isSuccess == 1){
                $openingBalance = Auth::user()->coins;
                $closingBalance = $openingBalance + $coin->coins;

                Auth::user()->update(['coins' => $closingBalance]);

                $log = user_coin_transactions::create([
                    'type' => 'BUY_CLICK_COINS',
                    'user_id' => Auth::user()->id,
                    'is_credit' => 1,
                    'amount' => $coin->coins,
                    'resource_id' => $coin->id,
                    'opening_balance' => $openingBalance,
                    'closing_balance' => $closingBalance,
                    'description' => 'Buy click coins'
                ]);

                $transactionId = $charge->id;
                $logId = $log->id;
            }


            user_transactions::create([
                'type' => 'BUY_CLICK_COINS',
                'user_id' => Auth::user()->id,
                'amount' => $coin->price,
                'is_success' => $isSuccess,
                'transaction_id' => $transactionId,
                'response' => json_encode($charge),
                'card_brand' => $card->brand,
                'card_last_four' => $card->last_four,
                'card_name' => $card->name,
                'message' => $errorMessage,
                'log_id' => $logId
            ]);

            if($isSuccess == 1){
                return $this->SendResponse(null, 'Payment successfull, click coins added in your account.', 200);
            }

            return $this->SendResponse(null, $errorMessage, 400);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    

}
