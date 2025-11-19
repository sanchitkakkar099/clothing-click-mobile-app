<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\User;
use App\location;
use App\style;
use App\posts;
use App\post_categories;
use App\post_colors;
use App\post_locations;
use App\post_sizes;
use App\user_coin_transactions;
use App\messages;
use App\reviews;
use App\user_saved_searches;
use App\user_notifications;
use App\user_favourite_posts;
use App\user_post_promotion_logs;
use App\category;
use App\size;
use App\color;
use App\orders;
use App\shipping_details;
use App\store_details;
use App\user_addresses;
use DB;

class OrderController extends APIResponseController {

    public function saveOrder(Request $request)
    {
        $all = $request->all();
		$validator = Validator::make($all, [
			'seller_id' => 'required|numeric',
			'product_id' => 'required|numeric',
			'payment_mode' => 'required|string',
			'transaction_id' => 'required|string',
			'type_of_order' => 'required|numeric',
		]);            

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{
			$stripe =getStripeObject(); 

			header('Content-Type: application/json');
			$paymentIntent = $stripe->paymentIntents->retrieve($all['transaction_id'],[]);
			if($all['type_of_order'] == 1){
				$name = explode(" ",$paymentIntent['shipping']['name']);
				$shipping_address = user_addresses::create([
					'user_id' => Auth::user()->id,
					'address_type' => 'shipping',
					'first_name' => $name[0],
					'last_name' =>$name[1],
					'country' => $paymentIntent['shipping']['address']['country'],
					'company' => $all['company'] ?? '',
					'address' => $paymentIntent['shipping']['address']['line1'],
					'address2' => $all['address2'] ?? '',
					'city' => $paymentIntent['shipping']['address']['city'],
					'province' => $paymentIntent['shipping']['address']['state'],
					'post_code' => $paymentIntent['shipping']['address']['city'],
					'phone' => $all['phone'] ?? '', 
				]);
			}
			$name = $paymentIntent['charges']['data'][0]['billing_details']['name'];
			$name = explode(' ',$name);
			$billing_address = user_addresses::create([
					'user_id' => Auth::user()->id,
					'address_type' => 'billing',
					'first_name' => $name[0],
					'last_name' => $name[1] ?? '',
					'country' => $paymentIntent['charges']['data'][0]['billing_details']['address']['country'],
					'company' => $all['company'] ?? '',
					'address' => $paymentIntent['charges']['data'][0]['billing_details']['address']['line1'],
					'address2' => $all['address2'] ?? '',
					'city' => $paymentIntent['charges']['data'][0]['billing_details']['address']['city'],
					'province' => $paymentIntent['charges']['data'][0]['billing_details']['address']['state'],
					'post_code' => $paymentIntent['charges']['data'][0]['billing_details']['address']['postal_code'],
					'phone' => $paymentIntent['charges']['data'][0]['billing_details']['phone'], 
				]);
			$order = orders::create([
				'buyer_id' => Auth::user()->id,
				'seller_id' => $all['seller_id'],
				'product_id' => $all['product_id'],
				'tax' => 0,
				'total' => $paymentIntent['amount']/100,
				'payment_mode' => $all['payment_mode'],
				'transaction_id' => $all['transaction_id'],
				'status' => $all['status'],
				'currency' => $paymentIntent['currency'],
				'shipping_address_id' => $shipping_address->id ?? 0,
				'billing_address_id' => $billing_address->id ?? 0,
				'type_of_order' => $all['type_of_order'],
			]);
			//echo "<pre>";print_r($order->id);exit;
			$response = [];
			$response = $order;
        

            return $this->SendResponse($order, 'Order created successfully', 200);

        }catch(\Exception $e){
            return $this->SendResponse($e, $e->getMessage(), 500);
        }
    }

    public function getAllOrder(Request $request)
    {
			$all = $request->all();
		try{
			$res = [];
			$response=[];
			$order = [];
			$all_orders = orders::where('buyer_id',Auth::user()->id)->orderby('id','desc')->get();
			foreach($all_orders as $order){
				$post = posts::getOrderPostDetails($order->product_id);
				$seller_details = User::getOrderAPIsellerData($order->seller_id);
				$res['product_details'] = $post;
				$res['order_details'] = $order;
				$res['seller_details'] = $seller_details;
				$response[]= $res;
			}
            return $this->SendResponse($response, 'Success', 200);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function getSingleOrder(Request $request)
    {
        $all = $request->all();
        $validator = Validator::make($all, [
            'order_id' => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{
            $order = orders::find($all['order_id']);
			$post = posts::getOrderPostDetails($order->product_id);
			$seller_details = User::getOrderAPIsellerData($order->seller_id);
			$address = user_addresses::find($order->shipping_address_id);
			$res['product_details'] = $post;
			$res['order_details'] = $order;
			$res['seller_details'] = $seller_details;
			$res['shipping_address'] ='';
			$res['pickup_address'] ='';
			if($address){
				$res['shipping_address'] = $address;
			}
			if($order->type_of_order == 2){
				
				if($seller_details['type'] == 'store'){
					$pickup = store_details::where([['user_id',$order->seller_id]])->first();
				
				}else{
					$pickup = location::where([['appuser_id',$order->seller_id]])->first();
				}
				$res['pickup_address'] =$pickup;
			}
			$response[]= $res;
		
            //$res = $order;
            return $this->SendResponse($response, 'Success', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    
}
