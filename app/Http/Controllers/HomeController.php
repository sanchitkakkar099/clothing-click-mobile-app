<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use App\user_sizes;

use App\user_favourite_brands;
use Auth;
use App\location;

use App\posts;

use App\user_followers;
use App\images;
use App\post_locations;
use App\user_locations;
use App\store_details;
use App\post_categories;
use App\post_colors;
use App\post_sizes;
use App\ProductVariant;
use App\category;
use App\size; 
use App\color;
use App\brand;
use App\style;

use DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       // $this->middleware('auth');
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    
    public function contact()
    {
        return view('contact');
    }
	public function profile( Request $request)
    {
		//$user = User::findorfail($request->user_id);
		$user = User::where([['id',$request->user_id],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();
		if(empty($user)){
			//echo $request->user_id; exit;
			$user_name = preg_replace('/^@/', '', $request->user_id);
			$user = User::where([['username',$user_name],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();
		}
		$selling=$user->posts()->where([['is_active',1],['is_delete',0],['is_sold',0]])->get();
		$sold=$user->posts()->where([['is_active',1],['is_delete',0],['is_sold',1]])->get();
		$user = $this->UserDetails($user);
		$selling_posts = [];
		$sold_posts = [];
		foreach($selling as $sell){
			 array_push($selling_posts,[
				'id' => $sell->id,
                'title' => $sell->title,
                'price' => $sell->price,
                'price_type' => $sell->price_type,
                'price_type_name' => ($sell->priceType)?$sell->priceType->name:null, 
				'images'=> $this->getImages($sell->images,true),
                'brand_id' => $sell->brand_id,
                'user_id' => $sell->user_id,
			 ]);
			
		}
		foreach($sold as $sell){
			 array_push($sold_posts,[
				'id' => $sell->id,
                'title' => $sell->title,
                'price' => $sell->price,
                'price_type' => $sell->price_type,
                'price_type_name' => ($sell->priceType)?$sell->priceType->name:null, 
				'images'=> $this->getImages($sell->images,true),
                'brand_id' => $sell->brand_id,
                'user_id' => $sell->user_id,
			 ]);
			
		}  

		//echo "<pre>";print_r($user);exit;
        return view('profile',compact('user','selling_posts','sold_posts'));
    }
	public function fetchStores(Request $request)
{
    $url = "https://vendor.clothingclick.ca/api/storeappInfo/store-profile/list";

    $crl = curl_init();
    curl_setopt_array($crl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FRESH_CONNECT => true,
    ]);

    $response = curl_exec($crl);

    if (!$response) {
        die('Error: "' . curl_error($crl) . '" - Code: ' . curl_errno($crl));
    }

    curl_close($crl);

    $data = json_decode($response);
	//echo "<pre>"; print_r($data);exit;

    foreach ($data->data as $d) {

        /** ---------------- USER DATA ---------------- */
        $user_data = [
            'role_id' => 8,
            'email' => $d->store_email,
            'username' => $d->username,
            'name' => str_replace("-", " ", $d->shop_name),
            'website' => $d->store_domain,
            'bio' => $d->store_description,
            'facebook_id' => $d->store_facbookId,
            'instagram_id' => $d->store_InstaId,
            'show_email_on_app' =>  filter_var(
										$d->show_email_on_app,
										FILTER_VALIDATE_BOOLEAN
									) ? 1 : 0,   
			'address_visible' =>  filter_var(
										$d->address_visible,
										FILTER_VALIDATE_BOOLEAN
									) ? 1 : 0,
           
        ];

        $user = User::updateOrCreate(
            ['website' => $d->store_domain ],
            $user_data
        );
		$raw = trim((string) $d->show_email_on_app);

		$user_data['show_email_on_app'] = $raw === '1' ? 1 : 0;
				$raw1 = trim((string) $d->address_visible);

		$user_data['address_visible'] = $raw1 === '1' ? 1 : 0;


		$user->update($user_data);

        /** ---------------- STORE DATA ---------------- */
        $store_data = [
            'user_id' => $user->id,
            'shopify_id' => $d->_id,
            'store_description' => $d->store_description,
			'timings' => !empty($d->timings) ? serialize($d->timings) : null,            
			'address1' => $d->address,
            'city' => $d->city,
            'country' => $d->country,
            'postal_Code' => $d->postal_code,
            'latitude' => $d->latitude,
            'longitude' => $d->longitude,
            'profile_image' => $d->brand_logo,
            'cover_image' => $d->brand_image_url,
            'total_products' => $d->total_products,
        ];

        store_details::updateOrCreate(
            ['shopify_id' => $d->_id],
            $store_data
        );

        /** ---------------- LOCATION DATA ---------------- */
        $location = [
            'appuser_id' => $user->id,
            'address' => $d->address,
            'city' => $d->city,
            'area' => $d->country,
            'postal_code' => $d->postal_code,
            'latitude' => $d->latitude,
            'longitude' => $d->longitude,
            'price' => 0,
            'is_default' => 1,
        ];

        location::updateOrCreate(
            [
                'appuser_id' => $user->id,
                'postal_code' => $d->postal_code
            ],
            $location
        );
    }

    return response()->json([
        'status' => true,
        'message' => 'Stores synced successfully'
    ]);
}

	 public function getImages($images,$isSingle = false)
    {
        if(empty($images)){
            return [];
        }

        $images = explode(',',$images);

        $res = [];
        foreach($images as $key => $img){
            $img_get = images::find($img);            
            array_push($res,[
                'id' => (int)$img,
                'image' => asset('uploads/files/post_images/'.$img_get->name),
                //'image' => ResizeImage($img,500,500),                
            ]);

            if($isSingle){
                break;
            }
        }

        return $res;
    }
	public function UserDetails($user){
		//echo "<pre>";print_r($user);exit;
		 if(is_object($user)){

            $user = User::find($user->id);

        }else{

            $user = User::find($user);

        }
		$blockedUsers1 = $user->listUserBlocked()->pluck('user_id')->toArray();

        $blockedUsers2 = $user->getBlockedUsers()->pluck('blocked_user_id')->toArray();

        $blockedUsers = array_merge($blockedUsers1,$blockedUsers2);        

        //dd($user);

        

        $user['gender_name'] = $user->gender->name ?? null;



        if(!empty($user['photo'])){

            $user['photo'] = ResizeImage($user['photo'],1000,1000, 'user_image');

        }

        

        $user['totalFollowings'] = $user->followings()->whereNotIn('following_user_id', $blockedUsers)->has('user')->where([['is_active',1],['is_delete',0]])->count();

        $user['totalFollowers'] = $user->followers()->whereNotIn('follower_user_id', $blockedUsers)->has('user')->where([['is_accepted',1],['is_active',1],['is_delete',0]])->count();

        $user['totalPosts'] = $user->posts()->where([['is_active',1],['is_delete',0],['is_sold',0]])->count();

        $user['totalSoldPosts'] = $user->posts()->where([['is_active',1],['is_delete',0],['is_sold',1]])->count();

        $user['created_at'] = date('Y-m-d H:i:s',strtotime($user['created_at']));

        $user['joined_at'] = Carbon::parse($user->created_at)->format('Y-m-d H:i:s');        

        $user['avg_rating'] = round($user->reviews()->has('user')->has('reviewBy')->avg('rating'),2);

        $user['total_reviews'] = $user->reviews()->has('user')->has('reviewBy')->count();

        $user['total_unread_notifications'] = $user->notifications()->where([['is_read',0],['is_active',1],['is_delete',0]])->count();

        

        $user['is_requested'] = 0;

        

        $user['is_following'] = 0;

        $user['follow_request_id'] = null;

        if($user->role_id == 2){

        $user['is_order'] = 0;            

        }

        if($user->role_id == 1){

        $user['locations'] = $user->locations()->where('is_delete',0)->where('is_default',1)->select('id','address','postal_code','latitude','longitude','price','city','area','is_default','is_paid','is_active','expire_at')->orderBy('id','desc')->take(1)->get();            

        }else{

        $user['locations'] = $user->locations()->where('is_delete',0)->select('id','address','postal_code','latitude','longitude','price','city','area','is_default','is_paid','is_active','expire_at')->get();            

        }

            $checkFollowRequest = user_followers::where([['is_active',1],['is_delete',0],['is_accepted',0],['user_id',$user->id]])->orderby('id','desc')->first();

//        dd($checkFollowRequest, Auth::user()->id);

            if(!empty($checkFollowRequest)){

                $user['is_requested'] = 1;

                $user['follow_request_id'] = $checkFollowRequest->id;

            }



            $checkFollowingOrNot = user_followers::where([['is_active',1],['is_delete',0],['is_accepted',1],['user_id',$user->id]])->orderby('id','desc')->count();



            if($checkFollowingOrNot > 0){

                $user['is_following'] = 1;

            }else{

                if($user->is_private ==1){

                     $user['totalSoldPosts'] = 0;                       

                }



            }



        

        //dd($user['totalSoldPosts']);


            $selling_count = ($user['totalSoldPosts'] > 0)?'('.$user['totalSoldPosts'].')':null;

            $user['tabs'] = [

                    [

                        'type' => 1,

                        'name' => 'Selling'

                    ],

                    [

                        'type' => 2,

                        'name' => 'Sold'. $selling_count,

                    ],

            ];

       

        

        unset($user['is_delete'],$user['updated_at'],$user['gender']);

	return $user;
	}
    
    
	public function post( Request $request, $id)
    {
		
		$post = posts::find($id);
		$location = location::where('id',$post->locations)->first();
		//echo "<pre>";print_r($location); exit;
        $res = [];
		$res['title'] = $post->title;
		$res['description'] = $post->description;
		$res['price'] = $post->price;
		$res['condition'] = $post->search_condition;
		$res['size'] = $post->search_size;
		$res['gender'] = $post->search_gender;
		$res['location'] = $location->city;
		$res['lat'] = $location->latitude;
		$res['long'] = $location->longitude;
		$res['city'] = $location->city;
		$res['state'] = $location->state;
		$res['country'] = $location->country;
		$res['post_views'] = $post->post_views;
		$res['images'] = $this->getImages($post->images,false);
		$OldDate = strtotime($post->created_at);
		$NewDate = date('M j, Y', $OldDate);
		$diff = date_diff(date_create($NewDate),date_create(date("M j, Y")));
		if($diff->format('%R%a') > 30 ){
			$res['posted_on'] = $NewDate;
		}else{
          $res['posted_on'] = $diff->format('%a days Ago');
		}
            //$res = posts::getAPIPostDetails($id);        
			//echo "<pre>";print_r($res);exit;
            
        return view('product', compact('res'));
    }
	
	public function getShopifyPost(Request $request)
{
    try {

        /** --------------------------------
         * FETCH PRODUCTS FROM SHOPIFY
         * -------------------------------- */

		$ch = curl_init();

		curl_setopt_array($ch, [
			CURLOPT_URL => 'https://parenttesting.myshopify.com/admin/api/2022-01/products.json?limit=250',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => [
				'X-Shopify-Access-Token: ' . config('services.shopify.access_token'),
			],
		]);

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			echo 'Curl Error: ' . curl_error($ch);
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);
		$products = json_decode($response)->products ?? [];
	//	echo "<pre>";print_r($products);exit;
	
        foreach ($products as $p) {
            $this->syncShopifyProduct($p);
        }

        /** --------------------------------
         * SUCCESS RESPONSE
         * -------------------------------- */
        return response()->json([
            'status'  => true,
            'message' => 'Products synced successfully with variants'
        ]);

    } catch (\Exception $e) {

        /** --------------------------------
         * ERROR LOG
         * -------------------------------- */
        \Log::error('Shopify Sync Error', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile()
        ]);

        return response()->json([
            'status' => false,
            'error'  => $e->getMessage(),
            'line'   => $e->getLine()
        ], 500);
    }
}
	

	/**
	 * Create/update one post (with variants, images, categories, location)
	 * from a Shopify product payload (REST product object / products webhook).
	 * Returns the post, or false when no app user matches the product vendor.
	 */
	public function syncShopifyProduct($p)
	{
		$post = null;

            /** --------------------------------
             * MATCH USER FROM VENDOR
             * -------------------------------- */
            $vendor = strtolower(
                str_replace(' ', '_', $this->RemoveSpecialChar($p->vendor))
            );

            $user = User::where(function ($q) use ($vendor) {

                $q->where('username', $vendor)
                    ->orWhereRaw(
                        "REPLACE(website, '-', '_') LIKE ?",
                        ["%$vendor%"]
                    )
                    ->orWhereRaw(
                        "REPLACE(website, '-', '.') LIKE ?",
                        ["%$vendor%"]
                    );

            })->where('role_id', 8)->first();

            // Skip if no user found
            if (!$user) {
                return false;
            }

            /** --------------------------------
             * USER LOCATION
             * -------------------------------- */
            $location = location::where('appuser_id', $user->id)->first();

            /** --------------------------------
             * TAG BASED CATEGORY + GENDER
             * -------------------------------- */
            $tagsArray = collect(explode(',', $p->tags))
                ->map(fn($t) => trim($t));

            // Default Menswear = 1
            $gender_id = $tagsArray->contains('Womenswear') ? 2 : 1;

            // Remove gender tags
            $tagsArray = $tagsArray->reject(function ($t) {
                return in_array($t, ['Menswear', 'Womenswear']);
            });

            /** --------------------------------
             * CATEGORY IDS
             * -------------------------------- */
            $categoryIds = category::whereIn('name', $tagsArray)
                ->where('gender_id', $gender_id)
                ->pluck('id')
                ->toArray();

            /** --------------------------------
             * BASIC POST DATA
             * -------------------------------- */
            $postData = [

                'user_id'            => $user->id,
                'shopify_product_id' => $p->id,
                'variant_id'         => $p->variants[0]->id ?? null,

                'gender_id'          => $gender_id,

                // Store categories as comma separated
                'categories'         => implode(',', $categoryIds),

                'locations'          => $location->id ?? null,

                'condition_id'       => 125,

                'title'              => $p->title,

                'description'        => $p->body_html ?? null,

                'price_type'         => 1,

                'price'              => $p->variants[0]->price ?? 0,

                // only active products are visible; draft/archived are hidden
                'is_active'          => ($p->status ?? 'active') == 'active' ? 1 : 0,

                'is_sold'            => 0,
            ];

            /** --------------------------------
             * CREATE OR UPDATE POST
             * -------------------------------- */
            $post = posts::updateOrCreate(
                [
                    'shopify_product_id' => $p->id
                ],
                $postData
            );
			$images1 = [];
			$variantImageMap = [];
			$imageIdMap = [];

			if (!empty($p->images)) {

				foreach ($p->images as $img) {

					if (empty($img->src)) {
						continue;
					}

					$uploadedImage = $this->UploadImageFromUrlShopify(
						$img->src,
						'posts',
						'variant',
						'0',
						$img->alt ?? ''
					);

					if (!$uploadedImage) {
						continue;
					}

					// Product gallery images
					$images1[] = $uploadedImage;

					// Shopify Image ID => Uploaded Image
					$imageIdMap[$img->id] = $uploadedImage;

					// Variant ID => Uploaded Image
					if (!empty($img->variant_ids)) {

						foreach ($img->variant_ids as $variantId) {
							$variantImageMap[$variantId] = $uploadedImage;
						}
					}
				}
			}

			$post->update([
				'images' => implode(',', array_unique($images1))
			]);
		
            /** --------------------------------
             * HANDLE VARIANTS
             * -------------------------------- */
            $images = [];
            $allQty = [];

            foreach ($p->variants as $variant) {

				/** --------------------------------
				 * VARIANT IMAGE
				 * -------------------------------- */

				$variantImage = null;

				/*
				|--------------------------------------------------------------------------
				| Priority 1 : Variant mapped using variant_ids
				|--------------------------------------------------------------------------
				*/
				if (isset($variantImageMap[$variant->id])) {

					$variantImage = $variantImageMap[$variant->id];

				}
				/*
				|--------------------------------------------------------------------------
				| Priority 2 : Variant has image_id
				|--------------------------------------------------------------------------
				elseif (!empty($variant->image_id) && isset($imageIdMap[$variant->image_id])) {

					$variantImage = $imageIdMap[$variant->image_id];

				}
				/*
				|--------------------------------------------------------------------------
				| Priority 3 : First Product Image
				|--------------------------------------------------------------------------
				*/
				elseif (!empty($images1)) {

					$variantImage = $images1[0];

				}
                /** --------------------------------
                 * EXTRACT SIZE / COLOR
                 * -------------------------------- */
                $color_id = null;
                $size_id  = null;

                foreach ($p->options as $index => $option) {

                    $optionName  = strtolower($option->name);

                    $optionValue = $variant->{'option' . ($index + 1)} ?? null;

                    if (!$optionValue) {
                        continue;
                    }

                    /** --------------------------------
                     * COLOR
                     * -------------------------------- */
                    if (in_array($optionName, ['color', 'colour'])) {

                        $colorModel = color::whereRaw(
                            'LOWER(name) = ?',
                            [strtolower($optionValue)]
                        )->first();

                        $color_id = $colorModel->id ?? 1;
                    }

                    /** --------------------------------
                     * SIZE
                     * -------------------------------- */
                    if ($optionName == 'size') {

                        $sizeModel = size::where(
                            'name',
                            $optionValue
                        )->first();

                        $size_id = $sizeModel->id ?? 1;
                    }
                }
				  
			
                /** --------------------------------
                 * CREATE OR UPDATE VARIANT
                 * -------------------------------- */
                ProductVariant::updateOrCreate(
                    [
                        'shopify_variant_id' => $variant->id
                    ],
                    [
                        'post_id'            => $post->id,

                        'shopify_variant_id' => $variant->id,

                        'title'              => $variant->title,

                        'sku'                => $variant->sku,

                        'price'              => $variant->price,

                        'compare_at_price'   => $variant->compare_at_price,

                        'inventory_quantity' => $variant->inventory_quantity ?? 0,

                        'barcode'            => $variant->barcode,

                        'image' => $variantImage,

                        'color'              => $color_id,

                        'size'               => $size_id,
                    ]
                );

                /** --------------------------------
                 * INVENTORY
                 * -------------------------------- */
                $allQty[] = $variant->inventory_quantity ?? 0;
            }

            /** --------------------------------
             * UPDATE POST IMAGES
             * -------------------------------- */
         

            /** --------------------------------
             * MARK SOLD IF ALL QTY = 0
             * -------------------------------- */
            if (
                !empty($allQty) &&
                collect($allQty)->every(fn($q) => intval($q) === 0)
            ) {
                $post->update([
                    'is_sold' => 1
                ]);
            }

            /** --------------------------------
             * POST CATEGORY RELATION
             * -------------------------------- */
            if (!empty($categoryIds)) {

                foreach ($categoryIds as $catId) {

                    post_categories::firstOrCreate([
                        'post_id'     => $post->id,
                        'category_id' => $catId
                    ]);
                }
            }

            /** --------------------------------
             * POST LOCATION RELATION
             * -------------------------------- */
            if ($location) {

                post_locations::firstOrCreate([
                    'post_id'     => $post->id,
                    'location_id' => $location->id
                ]);
            }

		return $post;
	}

	public function RemoveSpecialChar($str) {

      // Using str_replace() function  
      // to replace the word 
      $res = str_replace( array( '\'', '"',
      ',' , ';', '<', '>','(',')','_' ), ' ', $str);

      // Returning the result 
      return $res;
      }

		public function UploadImageFromUrlShopify($url, $path = 'posts', $type = 'variant',$guard = 0,$alt='')  
{
    $module = $path; // ✅ ensures it's always defined 
		$imgId = 1;
		$lastRecord = images::latest()->first();
		if(!empty($lastRecord)){
			$imgId = $lastRecord->id;
		}
		
		  $url_array = explode("/", $url);
		  //$image_name = end($url_array);
		   $parsedUrl = parse_url($url);
    $pathParts = pathinfo($parsedUrl['path']);
    $image_name = $pathParts['basename']; // Extracts: WhatsAppImage2025-01-02at5_1742370608_20250319_7551.jpg

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
		
		$record = images::create(['name'=>$iname,'module'=>$module,'directory'=>$path ,'is_admin'=>$guard,'alt'=>$alt]);

		return $record->id;
	}
	public function destroyByShopifyId($shopifyId)
    {
        $post = posts::where('shopify_product_id', $shopifyId)->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => "No post found with shopify_product_id: {$shopifyId}"
            ], 404);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
            'data' => $post
        ], 200);
    }
}
