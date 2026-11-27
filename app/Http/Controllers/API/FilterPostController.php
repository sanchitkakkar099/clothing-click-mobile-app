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

        /** -----------------------------
         * VALIDATION
         * ----------------------------- */
        $validator = Validator::make($all, [
            'is_mysize' => 'required|numeric|max:1',
            'gender_id' => 'nullable|string',
            'sizes' => 'nullable|string',
            'colors' => 'nullable|string',
            'condition_id' => 'nullable|numeric',
            'distance' => 'nullable|numeric',
            'style' => 'nullable|string',
            'seller' => 'nullable|numeric',
            'brand_id' => 'nullable|string',
            'categories' => 'nullable|string',
            'slectedCategories' => 'nullable|string',
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

        // Convert empty strings to null
        $all = array_map(function ($value) {
            return ($value === "" || $value === "null") ? null : $value;
        }, $all);

        $all['price_type'] = 1; // force price_type

        $user = Auth::user();
        $coordinates = ['latitude' => $all['latitude'], 'longitude' => $all['longitude']];
        $distance = $all['distance'] ?? 50;

        /** -----------------------------
         * SIZES
         * ----------------------------- */
        $sizes = [];
        if ($all['is_mysize'] == 1) {
            $sizes = $user->userSizes()->where('is_active', 1)->pluck('size_id')->toArray();
        } elseif (!empty($all['sizes'])) {
            $sizes = explode(',', $all['sizes']);
        }

        /** -----------------------------
         * CATEGORIES
         * ----------------------------- */
       $categoryIds = [];

		$categoriesToProcess = $all['selectedCategories'] ?? null;

		if ($categoriesToProcess) {

			$selected = array_map('trim', explode(',', $categoriesToProcess));
			$remove   = !empty($all['categories']) 
				? array_map('trim', explode(',', $all['categories'])) 
				: [];

			$categoriesToProcess = implode(',', array_diff($selected, $remove)); 
		}
if ($categoriesToProcess) {
 
    $categoryItems = explode(',', (string) $categoriesToProcess); 

    foreach ($categoryItems as $cat) {
        $cat = trim($cat);

        if (is_numeric($cat)) {
            $categoryIds[] = (int)$cat;

            $catName = category::where('id', $cat) 
                ->where('is_active', 1) 
                ->where('is_delete', 0)
                ->value('name');

            if ($catName) {
                $relatedIds = category::where('name', $catName)
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->pluck('id')
                    ->toArray();

                $categoryIds = array_merge($categoryIds, $relatedIds);
            }
        } else {
            $relatedIds = category::where('name', $cat)
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->pluck('id')
                ->toArray();

            $categoryIds = array_merge($categoryIds, $relatedIds);
        }
    }

    $categoryIds = array_unique($categoryIds); 
}
		
        /** -----------------------------
         * MAIN QUERY
         * ----------------------------- */
        $posts = posts::PrivateAccountPost($user) 
            ->RemoveBlockUserPosts()
            ->RemoveSoldPost()
            ->GetNearestPostDashboard($coordinates, true)
            ->where([['posts.is_active', 1], ['posts.is_delete', 0]])
            ->where('posts.user_id', '!=', $user->id)
            ->where(function ($q) use ($all, $sizes, $categoryIds) {

                /** GENDER FILTER */
                if (!empty($all['gender_id'])) {
                    $genders = array_map('trim', explode(',', $all['gender_id']));
                    $q->where(function ($query) use ($genders) {
                        foreach ($genders as $gender) {
                            $query->orWhereRaw("FIND_IN_SET(?, REPLACE(posts.gender_id, ' ', ''))", [(int)$gender]);
                        }
                    });
                }

                /** BRAND FILTER */
                if (!empty($all['brand_id'])) {
                    $brands = explode(',', $all['brand_id']);
                    $q->whereIn('posts.brand_id', $brands);
                }

                /** CONDITION FILTER */
                if (!empty($all['condition_id'])) {
                    $q->where('posts.condition_id', $all['condition_id']);
                }

                /** STYLE FILTER */
                if (!empty($all['style'])) {
                    $q->whereIn('posts.style_id', explode(',', $all['style']));
                }

                /** CATEGORY FILTER */
               if (!empty($categoryIds)) {
					$categoryRegex = implode('|', $categoryIds);
					$q->whereRaw('CONCAT(",", posts.categories, ",") REGEXP ",(' . $categoryRegex . '),"');
				}

                /** COLORS FILTER */
                if (!empty($all['colors'])) {
                    $colors = implode('|', explode(',', $all['colors']));
                    $q->whereRaw('CONCAT(",", posts.colors, ",") REGEXP ",(' . $colors . '),"');
                }

                /** PRICE FILTER */
                $q->where('posts.price_type', $all['price_type']);
                if (!empty($all['price_from']) && $all['price_type'] == 1) {
                    $q->where('posts.price', '>=', (int)$all['price_from']);
                }
                if (!empty($all['price_to']) && $all['price_type'] == 1) {
                    $q->where('posts.price', '<=', (int)$all['price_to']);
                }

                /** SIZE FILTER */
                if (count($sizes) > 0) {
                    $sizeRegex = implode('|', $sizes);
                    $q->whereRaw('CONCAT(",", posts.sizes, ",") REGEXP ",(' . $sizeRegex . '),"');
                }

                /** SELLER FILTER */
                if (!empty($all['seller'])) {
                    $q->with('user')->whereHas('user', function ($s) use ($all) {
                        if ((int)$all['seller'] !== 1) {
                            $s->where('role_id', 8);
                        } else {
                            $s->where('role_id', (int)$all['seller']);
                        }
                    });
                }
            });

        /** -----------------------------
         * SORTING
         * ----------------------------- */
        if (!empty($all['sort_by'])) {
            $sortValue = $all['sort_value'] ?? 'desc';
            switch ($all['sort_by']) {
                case 'date':
                    $posts->orderBy('posts.created_at', $sortValue);
                    break;
                case 'price':
                    $posts->orderBy('posts.price', $sortValue);
                    break;
                case 'distance':
                    $posts->orderBy('distance', $sortValue);
                    break;
                case 'trending':
                    $posts->orderBy('posts.post_views', 'desc');
                    break;
                default:
                    $posts->orderBy('id', 'desc');
                    break;
            }
        } else {
            $posts->orderBy('id', 'desc');
        }

        /** -----------------------------
         * PAGINATION
         * ----------------------------- */
        $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));

        if ($all['is_only_count'] == 1) {
            $res['total_posts'] = $posts->total();
            $res['user_detail'] = $user->id . ' - ' . $user->name . ' - ' . $user->username;
        } else {
            $res['currentPage'] = $posts->currentPage();
            $res['hasMorePages'] = $posts->hasMorePages();
            $res['posts'] = posts::getAPIPostListData($posts->items());
            $res['user_detail'] = $user->id . ' - ' . $user->name;
        }

        /** -----------------------------
         * USER SEARCH LOGS
         * ----------------------------- */
        $logs = [];

        if (!empty($all['categories'])) {
            foreach (explode(',', $all['categories']) as $cat) {
                $logs[] = [
                    'user_id' => $user->id,
                    'log_type' => 'CATEGORY',
                    'resource_id' => $cat,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($all['brand_id'])) {
            foreach (explode(',', $all['brand_id']) as $brand) {
                $logs[] = [
                    'user_id' => $user->id,
                    'log_type' => 'BRAND',
                    'resource_id' => $brand,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($logs)) {
            user_search_logs::insert($logs);
        }

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
			'variant_id' => 'required|numeric',
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
				// Billing = card billing details; shipping = Stripe shipping address for delivery orders
				// (type 1), otherwise the billing address. Everything must be inside "order", otherwise
				// Shopify ignores it and the order has no customer or address.
				$billingDetails = $paymentIntent['charges']['data'][0]['billing_details'] ?? null;
				$phone = $billingDetails['phone'] ?? ($all['phone'] ?? '');
				$billingName = explode(' ', trim((string) ($billingDetails['name'] ?? '')), 2);
				$billingAddr = $address;
				$billingAddr['first_name'] = $billingName[0] !== '' ? $billingName[0] : $address['first_name'];
				$billingAddr['last_name'] = $billingName[1] ?? $address['last_name'];
				$billingAddr['phone'] = $phone;

				$shippingAddr = $billingAddr;
				$stripeShipping = $paymentIntent['shipping'] ?? null;
				if ($all['type_of_order'] == 1 && !empty($stripeShipping['address']['line1'])) {
					$shipName = explode(' ', trim((string) ($stripeShipping['name'] ?? '')), 2);
					$shippingAddr = [
						'first_name' => $shipName[0],
						'last_name'  => $shipName[1] ?? '',
						'address1'   => $stripeShipping['address']['line1'],
						'address2'   => $stripeShipping['address']['line2'] ?? ($all['address2'] ?? ''),
						'city'       => $stripeShipping['address']['city'] ?? '',
						'province'   => $stripeShipping['address']['state'] ?? '',
						'zip'        => $stripeShipping['address']['postal_code'] ?? '',
						'country'    => $stripeShipping['address']['country'] ?? '',
						'phone'      => $stripeShipping['phone'] ?? ($all['phone'] ?? $phone),
					];
				}

				// Orders from the app are tagged (the vendor platform copies the tags to the vendor store order).
				// A pickup order needs no shipping, so it is created already fulfilled.
				$isPickup = $all['type_of_order'] == 2;
				$orderTags = $isPickup ? "Mobile App, Pickup" : "Mobile App, Delivery";
				$pickupFulfilled = $isPickup ? ["fulfillment_status" => "fulfilled"] : [];
				$lineItems = [["variant_id"=>$all['variant_id'], "quantity"=>1]];
				$transactions = [["kind"=> "sale", "status"=> "success", "amount"=> $paymentIntent['amount']/100]];
				$data_to_be_passed = [
					"order" => [
						"line_items"       => $lineItems,
						"email"            => Auth::user()->email,
						"customer"         => [
							"first_name" => $billingAddr['first_name'],
							"last_name"  => $billingAddr['last_name'],
							"email"      => Auth::user()->email,
						],
						"billing_address"  => $billingAddr,
						"shipping_address" => $shippingAddr,
						"financial_status" => "paid",
						"transactions"     => $transactions,
						"note"             => "Thank you for your order!",
						"tags"             => $orderTags,
						...$pickupFulfilled,
					],
				];

				$postShopifyOrder = function ($payload) {
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
					  CURLOPT_POSTFIELDS =>json_encode($payload),
					  CURLOPT_HTTPHEADER => array(
						'X-Shopify-Access-Token: '. config('services.shopify.access_token'),
						'Content-Type: application/json'
					  ),
					));
					$response = curl_exec($curl);
					curl_close($curl);
					return [$response, json_decode($response)];
				};

				[$response, $rese] = $postShopifyOrder($data_to_be_passed);

				// If Shopify rejects the full order (e.g. an address it cannot validate), log why and
				// fall back to the old minimal order so the paid order still reaches the parent store.
				if (empty($rese->order->id)) {
					\Log::warning('Shopify order with customer/address rejected, retrying minimal order', ['transaction_id' => $all['transaction_id'], 'response' => substr((string) $response, 0, 500)]);
					[$response, $rese] = $postShopifyOrder(["order" => [
						"line_items"       => $lineItems,
						"financial_status" => "paid",
						"transactions"     => $transactions,
						"note"             => "Thank you for your order!",
						"tags"             => $orderTags,
						...$pickupFulfilled,
					]]);
				}
					// The payment is already captured at this point, so never lose the order record
					// if Shopify rejects the request: log it and carry on with an empty Shopify order id.
					$s_order_id = $rese->order->id ?? '';
					if ($s_order_id === '') {
						\Log::error('Shopify order creation failed', ['transaction_id' => $all['transaction_id'], 'product_id' => $all['product_id'], 'response' => substr((string) $response, 0, 500)]);
					}
				
			
			$order = orders::create([
				'buyer_id' => Auth::user()->id,
				'seller_id' => $all['seller_id'],
				'product_id' => $all['product_id'],
				'variant_id' => $all['variant_id'],
				'shopify_order_id' => $s_order_id,
				'shopify_order_number' => $rese->order->name ?? null,
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
        
			// Also create the order in the vendor's own Shopify store (only for connected vendors;
			// parent-store-only products are skipped). Never lets a vendor-store problem fail the order.
			try {
				$vendorResult = app(\App\Services\VendorShopifyOrderService::class)->createForPost(
					$product, $all['variant_id'], $address, ['email' => Auth::user()->email],
					$paymentIntent['amount']/100,
					['app_order_id' => $order->id, 'parent_order' => $rese->order->name ?? '']
				);
			} catch (\Throwable $e) {
				\Log::error('Vendor order step crashed: ' . $e->getMessage());
			}

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
				$post = posts::getOrderPostDetails($order->product_id, $order->variant_id);
				//print_r($post);exit;
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
			$post = posts::getOrderPostDetails($order->product_id,$order->variant_id);
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
