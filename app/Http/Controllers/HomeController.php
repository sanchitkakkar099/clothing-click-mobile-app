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
	public function fetchStores( Request $request)
    {

		//$url = "https://clownfish-app-u8h6e.ondigitalocean.app/storedata";
		$url = "https://vendor.clothingclick.ca/api/storeappInfo/store-profile/list";
         
        $crl = curl_init();
        curl_setopt($crl, CURLOPT_URL, $url);
        curl_setopt($crl, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
         
        $response = curl_exec($crl);
        if(!$response){
           die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        }
         
        curl_close($crl);
		$data = json_decode($response);
		$user_data = [];
		foreach($data->data as $d){
			//echo "<pre>";print_r($d);exit;
			$user_data['role_id'] =8;
			$user_data['email'] =$d->store_email;
			$user_data['username'] =$d->shop_name;
			$user_data['password'] ='';
			$user_data['name'] =str_replace("-", " ",$d->shop_name);
			$user_data['locations'] =$d->city;
			$user_data['website'] =$d->store_domain;
			$user_data['bio'] =$d->store_description;
			$user_data['facebook_id'] =$d->store_facbookId;
			$user_data['instagram_id'] =$d->store_InstaId;
			$store_data = [];
			
			$store_data['shopify_id'] = $d->_id;
			$store_data['store_description'] = $d->store_description;
			$store_data['timings'] = serialize($d->timings);
			$store_data['address1'] = $d->address;
			$store_data['address2'] = '';
			$store_data['city'] = $d->city;
			$store_data['country'] = $d->country;
			$store_data['profile_image'] = $d->brand_logo;
			$store_data['cover_image'] = $d->brand_image_url;
			$store_data['total_products'] = $d->total_products;	
			$store_data['postal_Code'] = $d->postal_code;
			
			$store_data['latitude'] = $d->latitude;	
			$store_data['longitude'] = $d->longitude;	
			$user = User::where('email', '=', $d->store_email)->first();
			if ($user === null) {
				
				$user = User::create($user_data);                
				$store_data['user_id'] = $user->id;
				User::find($user->id)->update($user_data);
				$store = store_details::where('shopify_id', '=', $d->_id)->first();
				if($store){
					store_details::find($store->id)->update($store_data);
				}else{
					store_details::create($store_data);
				}
				$location =[];
				$location['appuser_id'] = $user->id;
				$location['address'] = $d->address;
				$location['city'] = $d->city;
				$location['area'] = $d->country;
				$location['postal_code'] = $d->postal_code;
				$location['longitude'] = $d->longitude;
				$location['latitude'] = $d->latitude;
				$location['price'] = 0;
				$location['is_default'] = 0;
				
				location::firstOrCreate(
				$location
				);
				//store_details::create($store_data);


			}
			else{
				$store_data['user_id'] = $user->id;
				User::find($user->id)->update($user_data);
				$store = store_details::where('shopify_id', '=', $d->_id)->first();
				if($store){
					store_details::find($store->id)->update($store_data);
				}else{
					store_details::create($store_data);
				}
				$location =[];
				$location['appuser_id'] = $user->id;
				$location['address'] = $d->address;
				$location['city'] = $d->city;
				$location['area'] = $d->country;
				$location['postal_code'] = $d->postal_code;
				$location['longitude'] = $d->longitude;
				$location['latitude'] = $d->latitude;
				$location['price'] = 0;
				$location['is_default'] = 0;
				
				location::firstOrCreate(
				$location
				);
			}
		}
		
		
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
		$location = location::where('id',$post->locations)->select('city')->first();
        $res = [];
		$res['title'] = $post->title;
		$res['description'] = $post->description;
		$res['price'] = $post->price;
		$res['condition'] = $post->search_condition;
		$res['size'] = $post->search_size;
		$res['gender'] = $post->search_gender;
		$res['location'] = $location->city;
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
	
	public function getShopifyPost(Request $request){
		//echo "Yes I am here ";exit;
        try {
			$ch = curl_init('https://parenttesting.myshopify.com/admin/api/2022-01/products.json?status=active&limit=250'); // Initialise cURL
			//$post = json_encode($post); // Encode the data array into a JSON string
			$authorization = "X-Shopify-Access-Token: ".env('SHOPIFY_ACCESS_TOKEN'); // Prepare the authorisation token
			curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization )); // Inject the token into the header
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			$result = curl_exec($ch); // Execute the cURL statement
			//return json_decode($result); 
			curl_close($ch); // Close the cURL connection
		//	return json_decode($result); // Return the received data
			$shopify_products = json_decode($result);
			$sposts  = $shopify_products->products;
			$i = 1;
			/////
		//	echo "<pre>";print_r($sposts);exit;
						// Assuming $products is the array shown in the screenshot
			/* 	$active_products = [];

				foreach ($sposts as $product) {
					if (isset($product->status) && $product->status === 'active') {
						$active_products[] = $product;
					}
				} */

			// Output the active products
			  
			  
			  
		//	echo "<pre>";print_r($sposts);exit;
			foreach($sposts as $p){
			
				$vendor = $p->vendor;
				$vendor = $this->RemoveSpecialChar($vendor);
				$vendor  = strtolower(str_replace(' ', '_',$vendor));

				$normalizedVendor = strtolower(str_replace('-', '_', $vendor));

				$user = User::where(function($query) use ($normalizedVendor) {
							$query->where('username', $normalizedVendor)
								  ->orWhereRaw("REPLACE(website, '-', '_') LIKE ?", ["%$normalizedVendor%"])
								  ->orWhereRaw("REPLACE(website, '-', '.') LIKE ?", ["%$normalizedVendor%"]);
						})
						->where('role_id', 8)
						->first();
						//echo "<pre>"; print_r($vendor);echo "</pre>";
						//echo "<pre>";print_r($normalizedVendor); echo "</pre>";
						//echo "<pre>";print_r($user);echo "</pre>";
				if($user){
					$location_id = 	location::where('appuser_id', $user->id)->first();
					$ch = curl_init('https://parenttesting.myshopify.com/admin/api/2023-07/products/'.$p->id.'/metafields.json'); // Initialise cURL
					$authorization = "X-Shopify-Access-Token: ".env('SHOPIFY_ACCESS_TOKEN'); // Prepare the authorisation token
					curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization )); // Inject the token into the header
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
					$result = curl_exec($ch); // Execute the cURL statement
					//return json_decode($result); 
					curl_close($ch); // Close the cURL connection
					$shopify_products = json_decode($result);
					//echo "Product id : ".$p->id."<pre>";print_r($shopify_products->metafields);echo "</pre>";
					$post_exists = posts::where('shopify_product_id', $p->id)->first();
					if($post_exists){
						//echo "exists product id "; print_r($p);echo "<br/>"; 
						
						$gender_id = '';
						$size = '';
						$category = '';
						$tags = $p->tags;

						$tagsArray = collect(explode(',', $tags))->map(fn($tag) => trim($tag));

						// Step 2: Detect gender
							$gender_id = 1;
						if ($tagsArray->contains('Menswear')) {
							$gender_id = 1;
							$tagsArray = $tagsArray->reject(fn($tag) => $tag === 'Menswear');
						} elseif ($tagsArray->contains('Womenswear')) {
							$gender_id = 2;
							$tagsArray = $tagsArray->reject(fn($tag) => $tag === 'Womenswear');
						}

						// Step 3: If gender_id is set, fetch matching categories
						$categoryMatches = [];

						if ($gender_id) {
							$categoryMatches = category::whereIn('name', $tagsArray)
								->where('gender_id', $gender_id)
								->pluck('id')
								->toArray(); // or use ->pluck('id') if you need category IDs
						}
						
						// Output
						//echo "Gender ID: $gender_id\n";
						//echo "<pre>";print_r($p->tags);echo "</pre>";
					//	echo "<pre>";print_r($categoryMatches);echo "</pre>"; 

						
						//$gender_id = '';
						$size = '';
						$category = implode(',',$categoryMatches);
						//echo "<pre>";print_r($category);echo "</pre>"; 

						
						if($p->options){
							 foreach($p->options as $o){
								 if($gender_id == ''){
									 if($o->name == 'Gender'){
										 $gender = $o->values[0];
										 
									 } 
								 }
								 if($category == ''){
									 if($o->name == 'Type'){
										 $type = $o->values[0];
										 
									 }
								 }
								 if($o->name == 'Size'){
									 $size = $o->values[0];
									 
								 }
							 }
						}

						if($shopify_products->metafields){
							 foreach($shopify_products->metafields as $prod){
								  
								 if($prod->key == 'Style' || $prod->key == 'style'){
									 $style = $prod->value;
									 $style_backup = $prod->value;
									 
								 } 
								 if($prod->key == 'Brand' || $prod->key == 'brand'){
									 $brand = $prod->value;
									 
								 } 
								 if($prod->key == 'Color' || $prod->key == 'color'){
									 $color = $prod->value;
									 
								 } 
								 
							 }
						}
						
						if($gender_id == ''){
							if($gender){
								if($gender == 'M' || $gender == 'Male'){
									 $gender_id = 1;
								 }else{
									 $gender_id = 2;
								 }
							}else{
								$gender_id = 1;
							}
						}
						
						if($category == ''){ 
						 if(!empty($type)){
							$categories = category::where([['gender_id',$gender_id],['name','like',$type.'%']])->first();
							if($categories ){
								$category = $categories->id;
							}else{
								$category = 1;
								/* $data = array('gender_id'=>$gender_id, 'name'=>$type, 'parent_id'=>0,'is_active'=>1);
								$category = category::create($data );
								$category = $category->id; */
							}
						 }
						}
							
							
							 if(!empty($size)){

							$sizes = size::where([['gender_id',$gender_id],['category_id',$category],['name',$size]])->first();
							if($sizes ){
								$size_id = $sizes->id;
							}else{
								$size_id = 1;
								/* $data = array('gender_id'=>$gender_id, 'name'=>$size, 'category_id'=>$category,'is_active'=>1);
								$size = size::create($data );
								$size_id = $size->id; */
							}
							 }
							if(!empty($color)){
								$colors = color::whereRaw('LOWER(name) = ?', [strtolower($color)])->first();
								if($colors ){
									$color_id = $colors->id;
								}else{
									$color_id  = 1;
									/* $data = array('name'=>$color,'is_active'=>1);
									$color = color::create($data );
									$color_id = $color->id; */
								}
							}
							
						if(!empty($brand)){
							$brands = Brand::where('name', $brand)->first();
							if($brands ){
								$brand_id = $brands->id;
							}else{
								$brand_id = 1;
								/* $data = array('name'=>$brand,'is_active'=>1);
								$brand = brand::create($data );
								$brand_id = $brand->id; */
							}
						}
						//echo $brand_id;
																	
						if(!empty($style)){
							$styles = style::whereRaw('LOWER(name) = ?', [strtolower($style)])->first();
							//echo "<pre> Styles from Shopify ";print_r($styles);echo "<pre>";
							 if($styles){
								$style_id = $styles->id;
							}else{
								$style_id = 1;
								/* $data = array('name'=>$style,'is_active'=>1);
								$style = style::create($data );
								$style_id = $style->id; */
							} 
						}
							
						
							
							//UploadImageFromUrl($url,'','',''); 
							//echo "<pre>";print_r($brand_id);echo "</pre>";
							$is_sold = 0;
							if($p->variants[0]->inventory_quantity == 0){
								$is_sold = 1;
							}
					$post_data = array(
						'user_id' => $user->id ,
						'brand_id' => $brand_id,
						'shopify_product_id' => $p->id,
						'variant_id'=>$p->variants[0]->id,
						'gender_id' => $gender_id,
						'categories' => $category ?? 0,
						'sizes' => $size_id ?? 0,
						'colors' => $color_id ?? 0,
						'locations' => $location_id->id,
						'style_id' => $style_id ?? 0,
						'condition_id' => 125,    
						'title' => $p->title,
						'description' => $p->description ?? null,
						'price_type' => 1,
						'price' => $p->variants[0]->price,
						'is_sold' => $is_sold,
						//'images' => implode(',',$image),
						'product_url' => null,
							);
				//echo "<pre>";print_r($post_data);echo "</pre>";

					 		
					 $update = posts::find($post_exists->id)->update($post_data);
					  	$image  = [];
					//$image[] = UploadImageFromUrl($p->image->src,'','',''); 
						foreach($p->images as $img){
							$image[(string)$img->id] = $this->UploadImageFromUrlShopify($img->src,'','',''); 
						}
					/* */
					 foreach ($p->variants as $variant) {

						// Default values
						$color_var_id = null;
						$size_var_id  = null;

						// Loop through product options and map dynamically
						foreach ($p->options as $index => $option) {
							$optionName = strtolower($option->name); // e.g. "Color", "Size", "Gender", etc.
							$optionValue = $variant->{'option' . ($index + 1)} ?? null;
							
							
							if ($optionName === 'color') { 
								
								//$color = $optionValue;
								$color_var = color::whereRaw('LOWER(name) = ?', [strtolower($optionValue)])->first();
								if($color_var ){
									$color_var_id = $color_var->id;
								}else{
									$color_var_id  = 1;
								}
							}

							if ($optionName === 'size') {
								$size_var = size::where('name',$optionValue)->first();
								if($size_var ){
									$size_var_id = $size_var->id;
								}else{
									$size_var_id = 1;
									
								}
							}
						}
						 
						// Now insert/update into your variants table
						ProductVariant::updateOrCreate(
							['shopify_variant_id' => $variant->id],
							[
								'post_id'            => $post_exists->id,
								'shopify_variant_id' => $variant->id,
								'title'              => $variant->title ?? null,
								'sku'                => $variant->sku ?? null,
								'price'              => $variant->price ?? null,
								'compare_at_price'   => $variant->compare_at_price ?? null,
								'inventory_quantity' => $variant->inventory_quantity ?? 0,
								'barcode'            => $variant->barcode ?? null,
								'color'              => $color_var_id,
								'size'               => $size_var_id,
								'option3'            => $variant->option3 ?? null,
								'image' => $image[(string)$variant->image_id] ?? null,

							]
						);
					}
										 
					 
							//echo "<pre>";print_r($update);exit;
					// Now associate it with post
					$postLocation = post_locations::firstOrCreate(
						[
							'post_id' => $post_exists->id,
							'location_id' => $location_id->id,
						],
						[
							'created_at' => now(),
							'updated_at' => now(),
						]
					);
					if(!empty($category)){
							$data = [];

							// Check if combination already exists
							$exists = post_categories::where('post_id', $post_exists->id)
								->where('category_id', $category)
								->exists();

							if (!$exists) {
								array_push($data, [
									'post_id' => $post_exists->id,
									'category_id' => $category,
									'created_at' => now(),
									'updated_at' => now(),
								]);
								post_categories::insert($data);
							}
						}

						if (!empty($size)) {
							$data = [];

							$exists = post_sizes::where('post_id', $post_exists->id)
								->where('size_id', $size)
								->exists();

							if (!$exists) {
								array_push($data, [
									'post_id' => $post_exists->id,
									'size_id' => $size,
									'created_at' => now(),
									'updated_at' => now(),
								]);
								post_sizes::insert($data);
							}
						}

						if (!empty($color)) {
							$data = [];

							$exists = post_colors::where('post_id', $post_exists->id)
								->where('color_id', $color)
								->exists();

							if (!$exists) {
								array_push($data, [
									'post_id' => $post_exists->id,
									'color_id' => $color,
									'created_at' => now(),
									'updated_at' => now(),
								]);
								post_colors::insert($data);
							}
						} 

					
					}else{
						echo "New product id "; print_r($p->id);echo "<br/>";
						$gender_id = '';
						 $size = '';
						 $category = '';
						 $tags = $p->tags;

						$tagsArray = collect(explode(',', $tags))->map(fn($tag) => trim($tag));

						// Step 2: Detect gender
						$gender_id = 1;
						if ($tagsArray->contains('Menswear')) {
							$gender_id = 1;
							$tagsArray = $tagsArray->reject(fn($tag) => $tag === 'Menswear');
						} elseif ($tagsArray->contains('Womenswear')) {
							$gender_id = 2;
							$tagsArray = $tagsArray->reject(fn($tag) => $tag === 'Womenswear');
						}

						// Step 3: If gender_id is set, fetch matching categories
						$categoryMatches = [];

						if ($gender_id) {
							$categoryMatches = category::whereIn('name', $tagsArray)
								->where('gender_id', $gender_id)
								->pluck('id')
								->toArray(); // or use ->pluck('id') if you need category IDs
						}
						
						//echo "<pre>";print_r($categoryMatches);echo "</pre>";

						
						//$gender_id = '';
						$size = '';
						$category = implode(',',$categoryMatches);
						 
						if($p->options){
							 foreach($p->options as $o){
								 if($gender_id == ''){
									 if($o->name == 'Gender'){
										 $gender = $o->values[0];
										 
									 } 
								 }
								 if($category == ''){
									 if($o->name == 'Type'){
										 $type = $o->values[0];
										 
									 }
								 }
								 if($o->name == 'Size'){
									 $size = $o->values[0];
									 
								 }
							 }
						}
						//echo "<pre>"; print_r($p->options);echo "</pre>";

						if($shopify_products->metafields){
							 foreach($shopify_products->metafields as $prod){
								  
								 if($prod->key == 'Style' || $prod->key == 'style'){
									 $style = $prod->value;
									 
								 } 
								 if($prod->key == 'Brand'){
									 $brand = $prod->value;
									 
								 } 
								 if($prod->key == 'Color'){
									 $color = $prod->value;
									 
								 } 
								 
							 }
						}
						echo "Brand name ".$brand."<br/>";
						//echo "<pre>"; print_r($shopify_products->metafields);echo "</pre>";
						
						if($category == ''){ 
							if(!empty($type)){
								$categories = category::where([['gender_id',$gender_id],['name','like',$type.'%']])->first();

								if($categories ){
									$category = $categories->id;
								}else{
									$category = 1;
									/* $data = array('gender_id'=>$gender_id, 'name'=>$type, 'parent_id'=>0,'is_active'=>1);
									$category = category::create($data );
									$category = $category->id; */
								}
							}
						}
						//echo "<pre>";print_r($size);echo "</pre>";;
					if(!empty($size)){
						$sizes = size::where([['gender_id',$gender_id],['category_id',$category],['name',$size]])->first();
						if($sizes ){
							$size = $sizes->id;
						}else{
							$size = 1;
							/* $data = array('gender_id'=>$gender_id, 'name'=>$size, 'category_id'=>$category,'is_active'=>1);
							$size = size::create($data );
							$size = $size->id; */
						}
					}
					//echo "<pre>";print_r($size);echo "</pre>";;
					if(!empty($color)){
						$colors = color::where([['name',$color]])->first();
						
						if($colors ){
							$color = $colors->id;
						}else{ $color = 1;
							/* $data = array('name'=>$color,'is_active'=>1);
							$color = color::create($data );
							$color = $color->id; */
						}
					}
							//echo "<pre>";print_r($color);echo "</pre>";;			

					if(!empty($brand)){
						$brands = brand::where('name',$brand)->first();
						if($brands ){
							$brand = $brands->id;
						}else{$brand =1;
							/* $data = array('name'=>$brand,'is_active'=>1);
							$brand = brand::create($data );
							$brand = $brand->id; */
						}
					}
					//echo "<pre>";print_r($brand);echo "</pre>";;		
					if(!empty($style)){
						$styles = style::where('name',ucfirst($style))->first();
						if($styles ){
							$style = $styles->id;
						}else{
							$style = 1;
							/* $data = array('name'=>$style,'is_active'=>1);
							$style = style::create($data );
							$style = $style->id; */
						}
					}
					//echo "<pre>";print_r($style);echo "</pre>";;
					$image  = [];
					//$image[] = UploadImageFromUrl($p->image->src,'','',''); 
					foreach($p->images as $img){
						$image[] = $this->UploadImageFromUrlShopify($img->src,'','',''); 
					}
					//echo "<pre>";print_r($image);echo "</pre>";;

					//echo "<pre>";print_r($user->id);exit;

					//UploadImageFromUrl($url,'','',''); 
					$is_sold = 0;
							if($p->variants[0]->inventory_quantity == 0){
								$is_sold = 1;
							}
						$post_data = array(
							'user_id' => $user->id ,
							'brand_id' => $brand ?? 0,
							'shopify_product_id' => $p->id,
							'variant_id' => $p->variants[0]->id,
							'gender_id' => $gender_id,
							'categories' => $category ?? null,
							'sizes' => $size ?? 0,
							'colors' => $color ?? null,
							'locations' => $location_id->id,
							'style_id' => $style ?? null,
							'condition_id' => 125,    
							'title' => $p->title,
							'description' => $p->description ?? null,
							'price_type' => 1,
							'price' => $p->variants[0]->price,
							'images' => implode(',',$image),
							'product_url' => null,
							'is_sold' => $is_sold,
								);
					//echo "<pre>";print_r($location_id->id);exit;
						$post = posts::create($post_data);
						
					foreach($p->images as $img){
						//$image[] = $this->UploadImageFromUrlShopify($img->id,'','',''); 
						$image[$img->id] = $this->UploadImageFromUrlShopify($img->src,'','',''); 
					}
					 foreach ($p->variants as $variant) {

						// Default values
						$color_var_id = null;
						$size_var_id  = null;

						// Loop through product options and map dynamically
						foreach ($p->options as $index => $option) {
							$optionName = strtolower($option->name); // e.g. "Color", "Size", "Gender", etc.
							$optionValue = $variant->{'option' . ($index + 1)} ?? null;
							
							
							if ($optionName === 'color') { 
								
								//$color = $optionValue;
								$color_var = color::whereRaw('LOWER(name) = ?', [strtolower($optionValue)])->first();
								if($color_var ){
									$color_var_id = $color_var->id;
								}else{
									$color_var_id  = 1;
								}
							}

							if ($optionName === 'size') {
								$size_var = size::where('name',$optionValue)->first();
								if($size_var ){
									$size_var_id = $size_var->id;
								}else{
									$size_var_id = 1;
									
								}
							}
						}
						
						// Now insert/update into your variants table
						ProductVariant::updateOrCreate(
							['shopify_variant_id' => $variant->id],
							[
								'post_id'            => $post->id,
								'shopify_variant_id' => $variant->id,
								'title'              => $variant->title ?? null,
								'sku'                => $variant->sku ?? null,
								'price'              => $variant->price ?? null,
								'compare_at_price'   => $variant->compare_at_price ?? null,
								'inventory_quantity' => $variant->inventory_quantity ?? 0,
								'barcode'            => $variant->barcode ?? null,
								'color'              => $color_var_id,
								'size'               => $size_var_id,
								'option3'            => $variant->option3 ?? null,
								'image'            => $image[$variant->image_id] ?? null,
							]
						);
					}
						
						
						if (!empty($category)) {
							$data = [];

							// Check if combination already exists
							$exists = post_categories::where('post_id', $post->id)
								->where('category_id', $category)
								->exists();

							if (!$exists) {
								array_push($data, [
									'post_id' => $post->id,
									'category_id' => $category,
									'created_at' => now(),
									'updated_at' => now(),
								]);
								post_categories::insert($data);
							}
						}

						if (!empty($size)) {
							$data = [];

							$exists = post_sizes::where('post_id', $post->id)
								->where('size_id', $size)
								->exists();

							if (!$exists) {
								array_push($data, [
									'post_id' => $post->id,
									'size_id' => $size,
									'created_at' => now(),
									'updated_at' => now(),
								]);
								post_sizes::insert($data);
							}
						}

						if (!empty($color)) {
							$data = [];

							$exists = post_colors::where('post_id', $post->id)
								->where('color_id', $color)
								->exists();

							if (!$exists) {
								array_push($data, [
									'post_id' => $post->id,
									'color_id' => $color,
									'created_at' => now(),
									'updated_at' => now(),
								]);
								post_colors::insert($data);
							}
						}
						$store_details = store_details::where('user_id',$user->id)->first();
							$locationArr = [
								'appuser_id' => $user->id,
								'address' => $store_details->address1 ?? null,
								'city' => $store_details->city ?? null,
								'area' => $store_details->country ?? null,
								'postal_code' => $store_details->postal_Code ?? null,
								'latitude' => $store_details->latitude ?? null,
								'longitude' => $store_details->longitude ?? null
							];
							//echo "<pre>";print_r($locationArr);exit;
							$last_location = location::create($locationArr);
							$last_location->save();                    
							$data = [
									'post_id' => $post->id,
									'location_id' => $last_location->id,
									'created_at' => date('Y-m-d H:i:s'),
									'updated_at' => date('Y-m-d H:i:s')                    
								];                
							$getlocation = post_locations::create($data);               
							$getlocation->save();
							
							$post->locations = $last_location->id;
							$post->save();						
						}
						//print_r($post_exists);echo "<br/>";
					}
				$i++;
				
			//}
				
			}
			echo "Itrated ".$i;
				echo "Products Uploaded successfully ";exit;
		}catch (\Exception $e) {
			echo $e->getMessage();exit;
          //  return $this->SendResponse(null, $e->getMessage(), 500);
        }    
	}
	
	public function RemoveSpecialChar($str) {

      // Using str_replace() function  
      // to replace the word 
      $res = str_replace( array( '\'', '"',
      ',' , ';', '<', '>','(',')','_' ), ' ', $str);

      // Returning the result 
      return $res;
      }
	  public function UploadImageFromUrlShopify($url,$dir,$module,$guard = 0)
	{
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
		
		$record = images::create(['name'=>$iname,'module'=>$module,'directory'=>$dir,'is_admin'=>$guard]);

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
