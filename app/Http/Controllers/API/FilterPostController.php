<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use DB;

use App\User;
use App\colors;
use App\user_search_logs;
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

class FilterPostController extends APIResponseController {

    public function index(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'is_mysize' => 'required|numeric|max:1',
                'gender_id' => 'nullable|string',
                'sizes' => 'nullable|string',
                'colors' => 'nullable|string',
                'condition_id' => 'nullable|numeric',
                'distance' => 'nullable|numeric',
                'style' => 'nullable|string',
                'seller' => 'nullable|numeric',
                'brand_id' => 'nullable|string',
                'latitude' => 'nullable|string',
                'longitude' => 'nullable|string',
                'price_type' => 'nullable|numeric',
                'price_from' => 'nullable|required_if:price_type,1|numeric',
                'price_to' => 'nullable|required_if:price_type,1|numeric',
                'sort_by' => 'nullable|string|max:15',
                'sort_value' => 'nullable|required_with:sort_by|string|max:5',
                'is_only_count' => 'required|numeric|min:0|max:1',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
			$all['price_type'] = 1;
            $isDistanceCodition = false;
			$cordinates = array('latitude'=> $all['latitude'],'longitude'=> $all['longitude']);

            $distance = 50;
            //if(isset($all['distance']) && $all['distance'] > 0){
            if(isset($all['distance'])){
                $isDistanceCodition = true;
                $distance = $all['distance'];
            }

            $sizes = [];
            if($all['is_mysize'] == 1){
                $sizes = Auth::user()->userSizes()->where('is_active',1)->pluck('size_id')->toArray();
            }else{
                if(isset($all['sizes']) && !empty($all['sizes'])){
                    $sizes = explode(',',$all['sizes']);
                }
            }

            $user = Auth::user();
            
            $finalArray = [];
            
            if(!empty($all['categories'])){
                $get_categories = explode(',',$all['categories']);
                $finalArray = category::whereDoesntHave('childCategories')->wherein('id',$get_categories)->where([['is_active',1],['is_delete',0]])->pluck('id')->toArray();                            
            }
            
            if(!empty($all['slectedCategories'])){
                $get_categories = explode(',',$all['slectedCategories']);
                $finalArray = category::wherein('id',$get_categories)->where([['is_active',1],['is_delete',0]])->pluck('id')->toArray();                            
            }
			
			//DB::enableQueryLog();


       $posts = posts::PrivateAccountPost($user)
    ->RemoveBlockUserPosts()
    ->RemoveSoldPost()
    ->GetNearestPostDashboard($cordinates, true)     // <-- Added
    ->where([['posts.is_active',1],['posts.is_delete',0]])
    ->where('posts.user_id','!=',Auth::user()->id)
    ->where(function($q) use($all, $sizes,$finalArray) {
        
        if (!empty($all['gender_id'])) {
            $gender_array = array_map('trim', explode(',', $all['gender_id'])); 
            $q->where(function($query) use ($gender_array) {
                foreach ($gender_array as $gender) {
                    $gender = (int)$gender;
                    $query->orWhereRaw("FIND_IN_SET(?, REPLACE(posts.gender_id, ' ', ''))", [$gender]);
                }
            });
        }

        if(!empty($all['brand_id'])){
            $brands = explode(',',$all['brand_id']);
            $q->whereIn('posts.brand_id',$brands);
        }

        if(!empty($all['condition_id'])){
            $q->where('posts.condition_id',$all['condition_id']);
        }

        if(!empty($all['style'])){
            $q->whereIn('posts.style_id',explode(',',$all['style']));
        }

        if (!empty($all['categories'])) {
            $category_array = explode(',', $all['categories']);
            $q->where(function($query) use ($category_array) {
                foreach ($category_array as $catId) {
                    $query->orWhereRaw("posts.categories REGEXP ?", ['(^|,)' . $catId . '($|,)']);
                }
            });
        }

        if(!empty($all['colors'])){
            $colors = explode(',',$all['colors']);
            $colors = implode('|',$colors);
            $q->whereRaw('CONCAT(",", posts.colors, ",") REGEXP ",('.$colors.'),"');
        }

        if(!empty($all['price_type'])){
            $q->where('posts.price_type',$all['price_type']);
        }

        if(!empty($all['price_from']) && $all['price_type'] == 1){
            $q->where('posts.price','>=',(int)$all['price_from']);
        }

        if(!empty($all['price_to']) && $all['price_type'] == 1){
            $q->where('posts.price','<=',(int)$all['price_to']);
        }

        if(count($sizes) > 0){
            $sizes = implode('|',$sizes);
            $q->whereRaw('CONCAT(",", posts.sizes, ",") REGEXP ",('.$sizes.'),"');
        }

        if(!empty($all['seller'])){
            if((int)$all['seller'] !== 1){
                $q->with('user')->whereHas('user',function($s){
                    $s->where('role_id',8);
                });
            }else{
                $q->with('user')->whereHas('user',function($s) use($all) {
                    $s->where('role_id',(int)$all['seller']);
                });
            }
        }

    });


            if(isset($all['sort_by']) && !empty($all['sort_by'])){
                switch($all['sort_by']){
                    case 'date':
                        $posts->orderby('posts.created_at',$all['sort_value']);
                        break;
                    case 'price':
                        $query = "CAST(posts.price AS UNSIGNED) ".$all['sort_value'];
                        $posts->orderByRaw($query);
                        break;
                    case 'distance':
                        $posts->orderby('distance',$all['sort_value']);
                        break;
					case 'trending':
                        $posts->orderby('posts.post_views','desc');
                        break;
                    default:
                        $posts->orderby('id','desc');
                        break;
                }
            }

            if($all['is_only_count'] == 1){
                $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
                $res['total_posts'] = $posts->total();
                $res['user_detail'] = $user->id. '- '.$user->name. '- '.$user->username;
            }else{
                $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
                $res['currentPage'] = $posts->currentPage();
                $res['hasMorePages'] = $posts->hasMorePages();
                $res['posts'] = posts::getAPIPostListData($posts->items());
                $res['user_detail'] = $user->id. '- '.$user->name;
            }

            $logs = [];
            if(!empty($all['categories'])){
                $categories = explode(',',$all['categories']);
                if(!empty($categories)){
                    foreach($categories as $cat){
                        array_push($logs,[
                            'user_id' => AUth::id(),
                            'log_type' => 'CATEGORY',
                            'resource_id' => $cat,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }

            if(!empty($all['brand_id'])){
                $brands = explode(',',$all['brand_id']);
                if(!empty($brands)){
                    foreach($brands as $b){
                        array_push($logs,[
                            'user_id' => AUth::id(),
                            'log_type' => 'BRAND',
                            'resource_id' => $b,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }

            if(!empty($logs)){
                user_search_logs::insert($logs);
            }
           // dd(DB::getQueryLog());

            return $this->SendResponse($res, 'Success', 200);

        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
	
	
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
			 		require_once('/var/www/html/vendor/stripe/stripe-php/init.php');
//exit;

        $stripe =getStripeObject(); 

			header('Content-Type: application/json');
			$paymentIntent = $stripe->paymentIntents->retrieve($all['transaction_id'],[]);
			//return json_encode($paymentIntent['currency']);
			$name = explode(" ",$paymentIntent['shipping']['name']);
			$address= [
					'first_name' => $name[0],
					'last_name' =>$name[1] ?? '',
					'country' => $paymentIntent['charges']['data'][0]['billing_details']['address']['country'],
					'address1' => $paymentIntent['charges']['data'][0]['billing_details']['address']['line1'],
					'city' => $paymentIntent['charges']['data'][0]['billing_details']['address']['city'],
					'province' => $paymentIntent['charges']['data'][0]['billing_details']['address']['state'],
					'zip' => $paymentIntent['charges']['data'][0]['billing_details']['address']['postal_code'],
				];
			
			if($all['type_of_order'] == 1){
				
				//print_r($name);exit;
				$shipping_address = user_addresses::create([
					'user_id' => Auth::user()->id,
					'address_type' => 'shipping',
					'first_name' => $name[0],
					'last_name' =>$name[1] ?? '',
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
			$s_order_id = '';
			$product = posts::find($all['product_id']);
			if($product->variant_id != ''){
				$order=[];
				$order['line_items']=[["variant_id"=>$product->variant_id, "quantity"=>1]];
				$customer= ['id'=>Auth::user()->email,'email'=>Auth::user()->email];
				$data_to_be_passed = [
					"order"=>$order,
					"customer"=>$customer,
					"billing_address"=>$address,
					"shipping_address"=>$address,
					"financial_status"=>"paid",
					"transactions"=> [
										[
										"kind"=> "sale",
										"status"=> "success",
										"amount"=> $paymentIntent['amount']/100
										]
									  
									],
					"note"=> "Thank you for your order!"				
				];
				//print_r(json_encode($data_to_be_passed));
				//exit;
				
				$curl = curl_init();

					curl_setopt_array($curl, array(
					  CURLOPT_URL => 'https://parenttesting.myshopify.com/admin/api/2023-10/orders.json',
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => '',
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 0,
					  CURLOPT_FOLLOWLOCATION => true,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => 'POST',
					  CURLOPT_POSTFIELDS =>json_encode($data_to_be_passed),
					  CURLOPT_HTTPHEADER => array(
						'X-Shopify-Access-Token: '. env('SHOPIFY_ACCESS_TOKEN'),
						'Content-Type: application/json'
					  ),
					));

					$response = curl_exec($curl);

					curl_close($curl);
					$rese = json_decode($response);
					//echo "<pre>";print_r($rese->order->id);exit;
					$s_order_id = $rese->order->id;
				
			}
			$order = orders::create([
				'buyer_id' => Auth::user()->id,
				'seller_id' => $all['seller_id'],
				'product_id' => $all['product_id'],
				'shopify_order_id' => $s_order_id,
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
        
			$response = $order;

            return $this->SendResponse($order, 'Order created successfully', 200);

        }catch(\Exception $e){
            return $this->SendResponse($e, $e->getMessage(), 500);
        }
    }

    public function getAllOrder(Request $request)
    {
        
			$res = [];
			$response=[];
			$order = [];
		try{
			$all_orders = orders::where([['buyer_id',Auth::user()->id]])->orderby('id','desc')->get();
			//echo "<pre>";print_r($all_orders);exit;
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
