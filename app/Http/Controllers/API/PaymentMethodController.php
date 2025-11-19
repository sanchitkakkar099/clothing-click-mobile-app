<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\User;
use App\user_cards;

class PaymentMethodController extends APIResponseController {

    public function index(Request $request)
    {
        try{

            $cards = user_cards::where([['user_id',Auth::id()],['is_active',1],['is_delete',0]])->orderby('created_at','desc')->get();

            $res = [];
            if($cards->count() > 0){
                foreach($cards as $card){
                    array_push($res,user_cards::getCardDetail($card));
                }
            }
            return $this->SendResponse($res, 'Success', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function create(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'card_name' => 'required|string|max:100',
                'card_number' => 'required|numeric',
                'exp_month' => 'required|numeric',
                'exp_year' => 'required|numeric',
                'cvv' => 'required|numeric',
                'address' => 'required|string|max:5000',
                'postal_code' => 'required|string|max:50',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'country' => 'required|string|max:10',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $stripe = getStripeObject();
            
            if(empty(Auth::user()->stripe_customer_id)){
                try{
                    $customer = $stripe->customers->create([
                        'email' => Auth::user()->email,
                        'name' => Auth::user()->name,
                        'phone' => Auth::user()->phone,
                        'address' => [
                            'line1' => $all['address'],
                            'postal_code' => $all['postal_code'],
                            'city' => $all['city'],
                            'state' => $all['state'],
                            'country' => $all['country'],
                        ],
                    ]);
                    Auth::user()->update(['stripe_customer_id' => $customer->id]);
                    $customerId = $customer->id;
                }catch(\Exception $e){
                    return $this->SendResponse(null, $e->getMessage(), 400);
                }
            }else{
                $customerId = Auth::user()->stripe_customer_id;
            }
            
            try{
                $token = $stripe->tokens->create([
                    'card' => [
                    'number' => $all['card_number'],
                    'exp_month' => $all['exp_month'],
                    'exp_year' => $all['exp_year'],
                    'cvc' => $all['cvv'],
                    ],
                ]);
            }catch(\Exception $e){
                return $this->SendResponse(null, $e->getMessage(), 400);
            }

            try{
                $card = $stripe->customers->createSource(
                    $customerId,
                    ['source' => $token->id]
                );
            }catch(\Exception $e){
                return $this->SendResponse(null, $e->getMessage(), 400);
            }

            $savedCard = user_cards::create([
                'user_id' => Auth::user()->id,
                'stripe_customer_id' => $customerId,
                'stripe_card_id' => $card->id,
                'name' => $all['card_name'],
                'brand' => $card->brand,
                'last_four' => substr($all['card_number'],-4),
                'exp_month' => $all['exp_month'],
                'exp_year' => $all['exp_year']
            ]);

            $res = user_cards::getCardDetail($savedCard);

            return $this->SendResponse($res, 'Card added successfully.', 200);

        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function delete(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'card_id' => 'required|numeric',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $check = user_cards::where([['user_id',Auth::user()->id],['id',$all['card_id']]])->count();

            if($check == 0){
                return $this->SendResponse(null, 'Invalid card id.', 400);
            }

            user_cards::find($all['card_id'])->update(['is_delete' => 1]);

            return $this->SendResponse(null, 'Card deleted successfully.', 200);

        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
}
