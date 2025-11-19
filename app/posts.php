<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\size;
use App\store_details;
use App\user_favourite_posts;
use App\location;
use App\images;
use App\recent_postview;
use App\user_addresses;
use App\messagedelete;
use App\color;
use App\User;
use App\ProductVariant;
use App\category;use App\style;
use DB;
use Auth;

class posts extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'brand_id', 'gender_id', 'categories', 'sizes', 'condition_id', 'colors', 'title', 'description', 'locations', 'price_type', 'price', 'images', 'videos', 'is_active', 'is_delete', 'is_sold', 'post_views','is_toppick','is_bump','is_hightlight','is_sale','is_profile_promote','sold_user_id','sale_price','top_pick_expire_at','bump_expire_at','hightlight_expire_at','sale_expire_at','profile_promote_expire_at','sold_at','product_url','search_gender','search_brand','search_category','search_size','search_condition','search_color','style_id','shopify_product_id','variant_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_active' => 'integer',
        'is_delete' => 'integer',
        'gender_id' => 'integer',
        'condition_id' => 'integer',
        'is_sold' => 'integer',
        'post_views' => 'integer',
        'is_toppick' => 'integer',
        'is_bump' => 'integer',
        'is_hightlight' => 'integer',
        'is_sale' => 'integer',
        'is_profile_promote' => 'integer',
        'sold_user_id' => 'integer',
        'sale_price' => 'double',        
		'style_id' => 'integer',
		'shopify_product_id' => 'string',
    ];

    public static function getAPIPostDetails($postId)
    {
		//return $postId;
        $post = Self::with(['brand','gender','condition','priceType'])->find($postId);
		

        $postCategories = $post->categories;
        $postSizes = $post->sizes;
        $postColors = $post->colors;
		$is_fav = 0;
		
		if(Auth::user()){
			$is_fav = Auth::user()->checkUserFavoutitePostById($post);
		}
		$style=style::find($post->style_id);

        $post['brand_name'] = $post->brand->name ?? '';
        $post['gender_name'] = $post->gender->name ?? '';
        $post['style_name'] = $style->name ?? '';
        $post['condition_name'] = $post->condition->name ?? '';
        $post['price_type_name'] = $post->priceType->name ?? '';
        $post['post_date_time'] = date('Y-m-d H:i:s',strtotime($post['created_at']));
        $post['is_favourite'] = $is_fav;
        $post['images'] = Self::getImages($post->images);
        $post['videos'] = Self::getVideos($post->videos);
        $post['sizes'] = Self::getSize($post->sizes);
        $post['categories'] = Self::getCategories($post->categories)??'';
        $post['variants'] = $post->productVariants->map(function ($variant) {
			return [
				'id'                 => $variant->id,
				'shopify_variant_id' => $variant->shopify_variant_id,
				'title'              => $variant->title,
				'sku'                => $variant->sku,
				'price'              => $variant->price,
				'compare_at_price'   => $variant->compare_at_price,
				'inventory_quantity' => $variant->inventory_quantity,
				'barcode'            => $variant->barcode,
				'color'              => Self::getColorName($variant->color),
				'size'               => Self::getSizeName($variant->size),
				'image'               => Self::getImages($variant->image,true),
			];
		});

		if($post->locations){
				$post['locations'] = Self::getLocations($post->locations);
		}else{
			$user = User::find($post->user_id);
			if($user->role_id == 8){
				$user = User::find($post->user_id);
				$store_data = store_details::where('user_id',$post->id)->first();
				//print_r($store_data);exit;
				$post['locations']= $store_data;           

			}else{
				$post['locations'] = $user->locations()->where('is_delete',0)->where('is_default',1)->select('address','postal_code','latitude','longitude','city','area')->orderBy('id','desc')->take(1)->get();
				
			}
		}
        $post['colors'] = Self::getColors($post->colors); 
		//return $post;
		//$post['style'] = style::findorfail($post->style_id);
		


        $user = User::find($post->user_id);
        if($user && $user->role_id != 8){
            $post['post_buynow_button'] = 0; 
			$post['type'] = 'post';
			 $post['user_profile_picture'] = ($user->photo !=null)?ResizeImage($user->photo,500,500):null;
			 $post['cover_image'] = '';
        }else{
            $post['post_buynow_button'] = 1;      
			$post['type']='store';
			$store_data = store_details::select('profile_image','cover_image')->where([['user_id',$user->id]])->first();
			$post['user_profile_picture'] = $store_data->profile_image;
			$post['cover_image'] = $store_data->cover_image;
        }
        $post['post_chat_button'] = $user->is_chat;
	//	$post['post_chat_button'] = 1;
        if($user && $user->role_id == 3){
        $post['post_location_button'] = 0;                        
        }else{
        $post['post_location_button'] = $user->is_findlocation_on;            
        }	
		

        $post['receiver_id'] = $post->user_id;

        
        $post['user_username'] = $user->username;
        $post['user_name'] = $user->name;
        $post['user_type'] = $user->role_id;
        $post['user_find_location'] = $user->is_findlocation_on;
        $post['total_posts'] = posts::where([['user_id',$user->id],['is_delete',0],['is_active',1],['is_sold',0]])->count();
        //$post['user_avg_review'] = round(reviews::where('post_id',$postId)->avg('rating'),2);
        $post['user_avg_review'] =round($user->reviews()->has('user')->has('reviewBy')->avg('rating'),2);
		
        $post['total_reviews'] =$user->reviews()->has('user')->has('reviewBy')->count();

       

		
        //dd($post);
        $post['user_posts'] = self::getAPIPostListData(posts::where([['user_id',$user->id],['is_delete',0],['is_sold',0],['is_active',1]])->orderby('created_at','desc')->take(10)->get());
        //$post['location'] = self::getLocations(posts::where([['user_id',$user->id],['is_delete',0],['is_sold',0],['is_active',1]])->orderby('created_at','desc')->take(10)->get());
		
        

		if(Auth::user()){
        $related_posts = posts::GetNearestPost(Auth::user(),true)
                        ->whereHas('user', function($q3){
                           $q3->where('is_delete',0)->where('is_active',1);
                        })->where([['posts.user_id','!=',\Auth::user()->id],['posts.user_id','!=',$user->id],['posts.is_delete',0],['posts.is_sold',0],['posts.is_active',1],['posts.gender_id',$post->gender_id]])
                         ->where(function($q) use($post) {

                            if(!empty($postCategories)){
                                $categories = explode(',',$postCategories);
                                $categories = implode('|',$categories);
                                $q->whereRaw('CONCAT(",", posts.categories, ",") REGEXP ",('.$categories.'),"');
                            }

                            
                            if(!empty($postColors)){
                                $colors = explode(',',$postColors);
                                $colors = implode('|',$colors);
                                $q->whereRaw('CONCAT(",", posts.colors, ",") REGEXP ",('.$colors.'),"');
                            }

                            if(!empty($postSizes)){
                                $sizes = explode(',',$postSizes);
                                $sizes = implode('|',$sizes);
                                $q->whereRaw('CONCAT(",", posts.sizes, ",") REGEXP ",('.$sizes.'),"');
                            }

                        }) 
                        ->orderby('id','desc')
                        ->take(10)
                        ->get();

        $post['related_posts'] = self::getAPIPostListData($related_posts); 
		
		
        $post['recently_viewed'] = self::getrecentlyViewedPost(Auth::user()->id);
		}else{
			$related_posts = posts::whereHas('user', function($q3){
                           $q3->where('is_delete',0)->where('is_active',1);
                        })->where([['posts.user_id','!=',$user->id],['posts.is_delete',0],['posts.is_sold',0],['posts.is_active',1],['posts.gender_id',$post->gender_id]])
                         ->where(function($q) use($post) {

                            if(!empty($postCategories)){
                                $categories = explode(',',$postCategories);
                                $categories = implode('|',$categories);
                                $q->whereRaw('CONCAT(",", posts.categories, ",") REGEXP ",('.$categories.'),"');
                            }

                            
                            if(!empty($postColors)){
                                $colors = explode(',',$postColors);
                                $colors = implode('|',$colors);
                                $q->whereRaw('CONCAT(",", posts.colors, ",") REGEXP ",('.$colors.'),"');
                            }

                            if(!empty($postSizes)){
                                $sizes = explode(',',$postSizes);
                                $sizes = implode('|',$sizes);
                                $q->whereRaw('CONCAT(",", posts.sizes, ",") REGEXP ",('.$sizes.'),"');
                            }

                        }) 
                        ->orderby('id','desc')
                        ->take(10)
                        ->get();
						$post['related_posts'] = self::getAPIPostListData($related_posts); 
						$post['recently_viewed'] = [];
		}
		//return $post;
       
        

        unset($post['brand'],$post['gender'],$post['condition'],$post['priceType'],$post['updated_at'],$post['is_delete'],$post['is_active']);
        
        $post['is_toppick'] = ($post->top_pick_expire_at !=null  && strtotime($post->top_pick_expire_at) > strtotime(now()))?1:0;
		
        $post['is_bump'] = ($post->bump_expire_at !=null  && strtotime($post->bump_expire_at) > strtotime(now()))?1:0;
		
        $post['is_hightlight'] = ($post->hightlight_expire_at !=null  && strtotime($post->hightlight_expire_at) > strtotime(now()))?1:0;
				 

        $post['is_profile_promote'] = ($post->profile_promote_expire_at !=null && strtotime($post->profile_promote_expire_at) > strtotime(now()))?1:0;
        $post['is_sale'] = ($post->sale_expire_at !=null && strtotime($post->sale_expire_at) > strtotime(now()))?1:0;
        //$post['is_sale'] = '222';
        return $post;
    }

	public static function getOrderPostDetails($postId)
    {
		
        $post = Self::select('title','price','images','sizes')->where('id',$postId)->first();
        $post['images'] = Self::getImages($post->images);
		
        $post['sizes'] = Self::getSize($post->sizes,true);

        
        return $post;
    }


    public static function getSize($sizes,$isName = false)
    {
        $sizes = explode(',',$sizes);
        if(!empty($sizes)){
            if($isName){
                return size::wherein('id',$sizes)->pluck('name')->toArray();
            }
            return size::wherein('id',$sizes)->select('id','name')->get();
        }
        return [];
    }

    public static function getColors($colors)
    {
        $colors = explode(',',$colors);
        if(!empty($colors)){
            return color::wherein('id',$colors)->select('id','name','colorcode')->get();
        }
        return [];
    }
   public static function getSizeName($sizes)
{
    if (!empty($sizes)) {
        return size::where('id', $sizes)->value('name');
    }

    return null;
}

public static function getColorName($colors)
{
    if (!empty($colors)) {
        return color::where('id', $colors)->value('name');
    }

    return null;
}

    public static function getLocations($locations)
    {
        $locations = explode(',',$locations);
        if(!empty($locations)){
            return location::wherein('id',$locations)->select('id','address','city','area','postal_code','latitude','longitude')->get();
        }
        return [];
    }

    public static function getCategories($categories)
    {
        $categories = explode(',',$categories);
        if(!empty($categories)){
            return category::wherein('id',$categories)->select('id','name')->get();
        }
        return [];
    }

    public static function getImages($images,$isSingle = false)
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

    public static function getVideos($videos,$isSingle = false)
    {

        if(empty($videos)){
            return [];
        }

        $videos = explode(',',$videos);

        $res = [];
        foreach($videos as $key => $vid){
            array_push($res,[
                'id' => (int)$vid,
                'video' => getVideoUrl($vid),
            ]);

            if($isSingle){
                break;
            }
        }

        return $res;
    }

    public function user()
    {
        return $this->belongsTo('App\User','user_id','id');
    }
	
	public function getStyle($style_id)
    {
        $style=style::find($style_id);
		return $style->name;
    }

    // public function brand()
    // {
    //     return $this->belongsTo('App\User','brand_id','id');
    // }


    public function brand()
    {
        return $this->belongsTo('App\brand','brand_id','id');
    }    

    public function categories()
    {
        return $this->hasMany('App\post_categories','post_id','id');
    }
    
    
    
    public function getpostcategoriesname()
    {
        $category = explode(',',$this->categories);
       $categorydata = category::wherein('id',$category)->pluck('name')->toArray();
       return implode(',',$categorydata);
    }
    public function getpostsizename()
    {
        $size = explode(',',$this->sizes);
       $sizedata = size::wherein('id',$size)->pluck('name')->toArray();
       return implode(',',$sizedata);
    }
    public function getpostcolorsname()
    {
        $color = explode(',',$this->colors);
       $colordata = color::wherein('id',$color)->pluck('name')->toArray();
       return implode(',',$colordata);
    }

    public function colors()
    {
        return $this->hasMany('App\post_colors','post_id','id');
    }

    public function sizes()
    {
        return $this->hasMany('App\post_sizes','post_id','id');
    }

    public function locations()
    {
        return $this->hasMany('App\post_locations','post_id','id');
    }

    public function priceType()
    {
        return $this->belongsTo('App\price_types','price_type','id');
    }

    public function condition()
    {
        return $this->belongsTo('App\condition','condition_id','id');
    }

    public function gender()
    {
        return $this->belongsTo('App\gender','gender_id','id');
    }

    public static function getAPIPostListData($posts = [], $user_id = '')
    {
        $res = [];
		//return $posts;
        foreach($posts as $post){
			$is_fav = 0;
			if(Auth::user()){
				 $check = Auth::user()->userFavouritePosts()->where('post_id',$post->id)->count();
				if($check > 0){
					$is_fav = 1;
				}
			}else if($user_id){
				$check =user_favourite_posts::where('post_id',$post->id)->where('user_id', $user_id)->count();
				print_r($check);exit;
				if($check > 0){
					$is_fav = 1;
				}
			}
           array_push($res, [
				'id' => $post->id,
				'title' => $post->title,
				'price' => $post->price,
				'price_type' => $post->price_type,
				'price_type_name' => optional($post->priceType)->name,
				'brand_id' => $post->brand_id,
				'style_id' => $post->style_id,
				'gender_id' => $post->gender_id,
				'color' => $post->colors,
				'user_id' => $post->user_id,
				'brand_name' => optional($post->brand)->name ?? '',
				'is_sold' => $post->is_sold,
				'is_toppick' => ($post->top_pick_expire_at && strtotime($post->top_pick_expire_at) > time()) ? 0 : $post->is_toppick,
				'is_bump' => ($post->bump_expire_at && strtotime($post->bump_expire_at) > time()) ? 1 : 0,
				'is_hightlight' => ($post->hightlight_expire_at && strtotime($post->hightlight_expire_at) > time()) ? 1 : 0,
				'is_profile_promote' => ($post->profile_promote_expire_at && strtotime($post->profile_promote_expire_at) > time()) ? 1 : 0,
				'is_sale' => ($post->sale_expire_at && strtotime($post->sale_expire_at) > time()) ? 1 : 0,
				'sale_price' => $post->sale_price,
				'created_at' => $post->created_at->format('Y-m-d H:i:s'),
				'image' => Self::getImages($post->images, true),
				'is_favourite' => $is_fav,
				'size_name' => implode(',', Self::getSize($post->sizes, true)),
				'category_ids' => $post->categories,
				'variants' => $post->productVariants->map(function ($variant) {
					return [
						'id'                 => $variant->id,
						'shopify_variant_id' => $variant->shopify_variant_id,
						'title'              => $variant->title,
						'sku'                => $variant->sku,
						'price'              => $variant->price,
						'compare_at_price'   => $variant->compare_at_price,
						'inventory_quantity' => $variant->inventory_quantity,
						'barcode'            => $variant->barcode,
						'color'              => Self::getColorName([$variant->color]), // ensure array
						'size'               => Self::getSizeName([$variant->size]),   // ensure array
						'image'               => Self::getImages($variant->image,true),   // ensure array
					];
				})->toArray(), // ✅ Convert to plain array
			]);
        }
        return $res;
    }
    public static function getAPIPostListDataAdmin($posts = [])
    { 
        $res = [];
        foreach($posts as $post){
			$user = User::find($post->user_id);
            array_push($res, [
				'id' => $post->id,
				'title' => $post->title,
				'price' => $post->price,
				'price_type' => $post->price_type,
				'price_type_name' => optional($post->priceType)->name,
				'brand_id' => $post->brand_id,
				'style_id' => $post->style_id,
				'gender_id' => $post->gender_id,
				'color' => $post->colors,
				'user_id' => $post->user_id,
				'brand_name' => optional($post->brand)->name ?? '',
				'is_sold' => $post->is_sold,
				'is_toppick' => ($post->top_pick_expire_at && strtotime($post->top_pick_expire_at) > time()) ? 0 : $post->is_toppick,
				'is_bump' => ($post->bump_expire_at && strtotime($post->bump_expire_at) > time()) ? 1 : 0,
				'is_hightlight' => ($post->hightlight_expire_at && strtotime($post->hightlight_expire_at) > time()) ? 1 : 0,
				'is_profile_promote' => ($post->profile_promote_expire_at && strtotime($post->profile_promote_expire_at) > time()) ? 1 : 0,
				'is_sale' => ($post->sale_expire_at && strtotime($post->sale_expire_at) > time()) ? 1 : 0,
				'sale_price' => $post->sale_price,
				'created_at' => $post->created_at->format('Y-m-d H:i:s'),
				'image' => Self::getImages($post->images, true),
				'is_favourite' => $is_fav,
				'size_name' => implode(',', Self::getSize($post->sizes, true)),
				'category_ids' => $post->categories,
				'variants' => $post->productVariants->map(function ($variant) {
					return [
						'id'                 => $variant->id,
						'shopify_variant_id' => $variant->shopify_variant_id,
						'title'              => $variant->title,
						'sku'                => $variant->sku,
						'price'              => $variant->price,
						'compare_at_price'   => $variant->compare_at_price,
						'inventory_quantity' => $variant->inventory_quantity,
						'barcode'            => $variant->barcode,
						'color'              => Self::getColorName([$variant->color]), // ensure array
						'size'               => Self::getSizeName([$variant->size]),   // ensure array
						'image'               => Self::getImages($variant->image,true),   // ensure array
					];
				})->toArray(), // ✅ Convert to plain array
			]);
        }

        return $res;
    }
	public static function getrecentlyViewedPost($user_id)
    {
        $res = [];
		
		$posts = recent_postview::select('post_id')->where('user_id',$user_id)
		->orderBy('id','desc')->take(25)->get()->unique('post_id');
		if($posts->count() > 0){
			foreach($posts as $p){
				if(!empty($p->post_id)){
					$user = Auth::user();
				$post = posts::find($p->post_id);
				$is_fav ='';
				
				if($post){
					if(Auth::user()->checkUserFavoutitePostById($post) === true){
						$is_fav = Auth::user()->checkUserFavoutitePostById($post);
					}
					array_push($res,[
						'id' => $post->id,
						'title' => $post->title,
						'price' => $post->price,
						'price_type' => $post->price_type,
						'price_type_name' => ($post->priceType)?$post->priceType->name:null,
						'user_id' => $post->user_id,
						'is_sold' => $post->is_sold,
						'is_hightlight' => ($post->hightlight_expire_at !=null  && strtotime($post->hightlight_expire_at) > strtotime(now()))?1:0,
						'is_profile_promote' => ($post->profile_promote_expire_at !=null && strtotime($post->profile_promote_expire_at) > strtotime(now()))?1:0,
						'created_at' => date('Y-m-d H:i:s',strtotime($post->created_at)),
						'image' => Self::getImages($post->images,true),
						'is_favourite' => $is_fav,
						'category_ids' => $post->categories,
					]);
				}
			
				}
			}
		}

        return $res;
    }

    public function scopePostFollowings($query, $user)
    {
        $followings = $user->followings()->where([['is_active',1],['is_delete',0]])->pluck('following_user_id')->toArray();
        
        if(!empty($followings)){
            return $query->whereIn('user_id',$followings);
        }//** start update by mahesh
        else{
            $followings = [];
            return $query->whereIn('user_id',$followings);                        
        }

//        return $query;
    }

    public function scopePostTopPicks($query, $user)
    {
        return $query->where('is_toppick', 1)->whereNotNULL('top_pick_expire_at')->whereDate('top_pick_expire_at', '>' ,\Carbon\Carbon::now());
        // updated by mahesh        
        // $sizes = $user->userSizes()->where('is_active',1)->pluck('size_id')->toArray();
        
        // if(!empty($sizes)){
        //     $sizes = implode('|',$sizes);
        //     $query->whereRaw('CONCAT(",", `sizes`, ",") REGEXP ",('.$sizes.'),"');
        // }
        
        // return $query;
    }

    public function scopePostDistance($query, $user, $distance = 50)
    {
        $userSelectedLocation = $user->locations()->where([['is_default',1],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();

        if(empty($userSelectedLocation)){
            $userSelectedLocation = $user->locations()->where([['is_active',1],['is_delete',0]])->orderby('id','asc')->first();
        }
        
        if(!empty($userSelectedLocation)){

            $getNearestLocationIds = location::where([['is_active',1],['is_delete',0],['appuser_id','!=',$user->id]])
                                ->select('id', DB::raw("6371 * acos(cos(radians(" . $userSelectedLocation->latitude . "))
                                * cos(radians(latitude)) * cos(radians(longitude) - radians(" . $userSelectedLocation->longitude . "))
                                + sin(radians(" .$userSelectedLocation->latitude. ")) * sin(radians(latitude))) AS distance"))
                                ->having('distance', '<=', $distance)
                                ->pluck('id')->toArray();
            
            if(!empty($getNearestLocationIds)){
                $query->whereHas('locations',function($q)use($getNearestLocationIds){
                    $q->whereIn('location_id',$getNearestLocationIds);
                });
            }

            // $query->join('post_locations','post_locations.post_id','=','posts.id')
            //     ->join('location','location.id','=','post_locations.location_id')
            //     ->select('posts.*',DB::raw("6371 * acos(cos(radians(" . $userSelectedLocation->latitude . ")) * cos(radians(latitude)) * cos(radians(longitude) - radians(" . $userSelectedLocation->longitude . ")) + sin(radians(" .$userSelectedLocation->latitude. ")) * sin(radians(latitude))) AS distance"))
            //     ->having('distance','<=',$distance)
            //     ->orderby('distance','asc')
            //     ->groupby('posts.id');
        }

        return $query;
    }

    public function scopeGetNearestPost($query, $user, $isDistanceCondition = false, $distance = 50, )
    {	
		$userSelectedLocation = [];
		///$query =[];
		if($user){
			$userSelectedLocation = $user->locations()->where([['is_default',1],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();
			if(empty($userSelectedLocation)){
				$userSelectedLocation = $user->locations()->where([['is_active',1],['is_delete',0]])->orderby('id','asc')->first();
			}
        
			if(!empty($userSelectedLocation)){

				$query->select('posts.*', DB::raw("6371 * acos(cos(radians(" . $userSelectedLocation->latitude . ")) * cos(radians(location.latitude)) * cos(radians(location.longitude) - radians(" . $userSelectedLocation->longitude . ")) + sin(radians(" .$userSelectedLocation->latitude. ")) * sin(radians(location.latitude))) AS distance"))
					->join('post_locations','post_locations.post_id','=','posts.id')
					->join('location','post_locations.location_id','=','location.id')
					->where([['location.is_active',1],['location.is_delete',0]])
					->groupby('posts.id');

				if($isDistanceCondition){
					$query->having('distance','<=',$distance);
				}
			}
		}

        
        
        return $query;

        
    }
    
    public function scopeGetNearestPostDashboard($query, $cordinates, $isDistanceCondition = false, $distance = 50)
    {	
		$userSelectedLocation = [];
		///$query =[];
		 if (!empty($cordinates['latitude']) && !empty($cordinates['longitude'])) {
        $latitude = (float) $cordinates['latitude'];
        $longitude = (float) $cordinates['longitude'];

        $query->select(
            'posts.*',
            'location.latitude',
            'location.longitude',
            DB::raw("6371 * acos(
                cos(radians($latitude)) * 
                cos(radians(location.latitude)) * 
                cos(radians(location.longitude) - radians($longitude)) + 
                sin(radians($latitude)) * 
                sin(radians(location.latitude))
            ) AS distance")
        )
        ->join('location', 'posts.locations', '=', 'location.id')
        ->where([
            ['location.is_active', 1],
            ['location.is_delete', 0],
        ]);

        if ($isDistanceCondition) {
            $query->having('distance', '<=', $distance);
        }
    }

        return $query;

        
    }
    
    // remove post from both side of user, if user1 block user2 than hide all post of user2 for user1 viceversa.
     public function scopeRemoveBlockUserPosts($query)
    {
		    \Log::debug('ScopeRemoveBlockUserPosts called');

        if (!Auth::check()) {
        return $query;
    }

    $blockedByMe = Auth::user()->getBlockedUsers()->pluck('blocked_user_id');
    $blockedMe = Auth::user()->listUserBlocked()->pluck('user_id');
    $allBlocked = $blockedByMe->merge($blockedMe)->unique();

    if ($allBlocked->isNotEmpty()) {
        $query->whereNotIn('posts.user_id', $allBlocked);
    }

    return $query;
    
    } 
	
	/* public function scopeRemoveBlockUserPosts($query)
	{
		//if (Auth::check()) {
			$blockedUsers = Auth::user()->blockedUsers()
								->pluck('blocked_user_id')
								->toArray();

			if (!empty($blockedUsers)) {
				$query->whereNotIn('posts.user_id', $blockedUsers);
			}
		//}

		return $query;
	} */

    public function scopeRemoveSoldPost($query){
        return $query->where('posts.is_sold', 0);
    }
    
    public function scopePrivateAccountPost($query, $user = false)
    {
		if($user){
        $followings = $user->followings()->where([['is_active',1],['is_delete',0]])->pluck('following_user_id')->toArray();
        if(count($followings) > 0){
            array_push($followings, $user->id);
            return $query->whereHas('user', function($q){
                $q->where('is_private',0);
            })->orWhereHas('user', function($q) use($followings){
                $q->where('is_private',1)->whereIn('user_id',$followings);
            });

        }//** start update by mahesh
            $followings = [];
            return $query->whereHas('user', function($q){
                $q->where('is_private',0);
            }); 
		}else{
			return [];
		}

    } 
    
    public function scopeFullTextSearch($query, $searchText)
    {

                // ->join('post_locations','post_locations.post_id','=','posts.id')
                // ->join('location','post_locations.location_id','=','location.id')
                // ->where([['location.is_active',1],['location.is_delete',0]])
                // ->groupby('posts.id');
                
        if(!empty($searchText)){
// SELECT id, MATCH (title,content,author) AGAINST ('traveling to parks') as score FROM news WHERE MATCH (title,content,author) AGAINST ('traveling to parks') > 0 ORDER BY score DESC;
    //      \DB::raw("MATCH (document) AGAINST ('".$term."') AS score")]);
    // $query->whereRaw("MATCH(posts.title,posts.description,posts.search_gender,posts.search_brand,posts.search_category,posts.search_size,posts.search_condition,posts.search_color) AGAINST (".'$searchText'.") as score");
                $query->select('posts.*', \DB::raw("MATCH (posts.title,posts.description,posts.search_gender,posts.search_brand,posts.search_category,posts.search_size,posts.search_condition,posts.search_color) AGAINST ('".$searchText."') AS score"));
//    $query_data = $query_data->whereRaw(MATCH (title,content) AGAINST (tset));
//        $query_data = $query_data->whereRaw( MATCH (title,content) AGAINST ($searchTerm IN BOOLEAN MODE)");

    }
        
        return $query;

        
    }    
    
    public function messagedeleted(){
        return $this->hasMany(messagedelete::class,'post_id')->where('deleted_by', \Auth::user()->id)->where('type','buyer');
    }
    
    public function messagedeletedselling(){
        return $this->hasMany(messagedelete::class,'post_id')->where('deleted_by', \Auth::user()->id)->where('type','seller');
    }
    
    public function remove_message_selling_count(){
        $getdeleted = messagedelete::where('type', 'seller')->where('deleted_by', Auth::user()->id)->pluck('deleted_for')->toArray();
        array_push($getdeleted, \Auth::user()->id);
        
        return $this->hasMany(messagedelete::class,'post_id')->where('deleted_by', \Auth::user()->id)->where('type','seller')->whereIn('deleted_for',$getdeleted);
    }    
    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class, 'post_id');
    }
}
