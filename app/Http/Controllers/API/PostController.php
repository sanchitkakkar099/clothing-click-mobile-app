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
use App\recent_postview;
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
use DB;

class PostController extends APIResponseController {

    public function create(Request $request)
    {
        $all = $request->all();
        $group_id = Auth::user()->role_id;
    
        if($group_id == 3){        
            $validator = Validator::make($all, [
                'brand_id' => 'required|numeric',
                'gender_id' => 'required|numeric',
                'categories' => 'required|string|max:255',
                'sizes' => 'nullable|string|max:255',
				// 'condition_id' => 'required|numeric',
                'colors' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:5000',
                //'locations' => 'required|string|max:255',
                'price_type' => 'required|numeric',
                'price' => 'required|numeric|gt:0',
                'image' => 'required|array|max:9',
                'image.*' => 'required|image|mimes:png,jpeg,jpg|max:5120',
                'video' => 'nullable|array|max:2',
                'video.*' => 'nullable|file|mimes:flv,mp4,avi,wmv,3gp,mkv|max:15360',
            ]);
        }else{
            $validator = Validator::make($all, [
                'brand_id' => 'required|numeric',
                'gender_id' => 'required|numeric',
                'categories' => 'required|string|max:255',
                'sizes' => 'nullable|string|max:255',
                'condition_id' => 'required|numeric',
                'colors' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:5000',
                //'locations' => 'required|string|max:255',
                'price_type' => 'required|numeric',
                'price' => 'required|numeric|gt:0',
                'image' => 'required|array|max:9',
                'image.*' => 'required|image|mimes:png,jpeg,jpg|max:5120',
                'video' => 'nullable|array|max:2',
                'video.*' => 'nullable|file|mimes:flv,mp4,avi,wmv,3gp,mkv|max:15360',
            ]);            
        }
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{

            $images = [];
            if(isset($all['image']) && !empty($all['image'])){
                foreach($all['image'] as $key => $value){
                    array_push($images,UploadImage($value,'post_images','posts'));
                }
            }

            $videos = [];
            if(isset($all['video']) && !empty($all['video'])){
                foreach($all['video'] as $key => $value){
                    array_push($videos,UploadVideo($value,'post_videos','posts'));
                }
            }

            $post = posts::create([
                'user_id' => Auth::user()->id,
                'brand_id' => $all['brand_id'],
                'gender_id' => $all['gender_id'],
                'categories' => $all['categories'],
                'sizes' => $all['sizes'] ?? 0,
                'colors' => $all['colors'],
                //'locations' => $all['locations'],
                'style_id' => $all['style'],
    //            'condition_id' => $all['condition_id'],
                'condition_id' => ($group_id ==3)?125:$all['condition_id'],    
                'title' => $all['title'],
                'description' => $all['description'] ?? null,
                'price_type' => $all['price_type'],
                'price' => $all['price'],
                'images' => implode(',',$images),
                'videos' => !empty($videos) ? implode(',',$videos) : null,
                'product_url' => (isset($all['product_url']))?$all['product_url']:null,
            ]);

            if(isset($all['categories']) && !empty($all['categories'])){
                $data = [];
                foreach(explode(',',$all['categories']) as $key => $value){
                    array_push($data,[
                        'post_id' => $post->id,
                        'category_id' => $value,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                post_categories::insert($data);
            }

           if(isset($all['sizes']) && !empty($all['sizes'])){
                $data = [];
                foreach(explode(',',$all['sizes']) as $key => $value){
                    array_push($data,[
                        'post_id' => $post->id,
                        'size_id' => $value,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                post_sizes::insert($data);
            } 

            if(isset($all['colors']) && !empty($all['colors'])){
                $data = [];
                foreach(explode(',',$all['colors']) as $key => $value){
                    array_push($data,[
                        'post_id' => $post->id,
                        'color_id' => $value,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                post_colors::insert($data);
            }

			/*if(isset($all['locations']) && !empty($all['locations'])){
                $data = [];
                $locationIds = [];
                foreach(explode(',',$all['locations']) as $key => $value){
                    // array_push($data,[
                    //     'post_id' => $post->id,
                    //     'location_id' => $value,
                    //     'created_at' => date('Y-m-d H:i:s'),
                    //     'updated_at' => date('Y-m-d H:i:s')
                    // ]);
                    $data = [
                        'post_id' => $post->id,
                        'location_id' => $value,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
					$last_location = post_locations::create($data);             
             //   array_push($locationIds, $last_location->id);
                }


//                dd($last_location);
                $post->locations = $all['locations'];
                $post->save();                
            }*/
			//echo "I am here";exit;*/
           
            if(isset($all['latitude']) && $all['longitude'] !==''){
				//echo "Inside location code";exit;
				//echo "<pre>";print_r($all['locations']);
				//echo "<pre>";print_r($d);exit;
                    $locationArr = [
                        'appuser_id' => Auth::user()->id,
                        'address' => $all['address'] ?? null,
                        'city' => $all['city'] ?? null,
                        'area' => $all['area'] ?? null,
                        'postal_code' => $all['postal_code'] ?? null,
                        'latitude' => $all['latitude'] ?? null,
                        'longitude' => $all['longitude'] ?? null
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

            $getTotalPostCount = posts::where('user_id',Auth::user()->id)->count();
            /*
            if($getTotalPostCount == 3){
                $checkTransaction = user_coin_transactions::where([['type','EARN_COIN_AFTER_POST_THEREE_ITEMS'],['user_id',Auth::user()->id]])->count();
                if($checkTransaction == 0){

                    user_coin_transactions::create([
                        'type' => 'EARN_COIN_AFTER_POST_THEREE_ITEMS',
                        'user_id' => Auth::user()->id,
                        'is_credit' => 1,
                        'amount' => config('constants.COIN_POST_THREE_ITEMS'),
                        'opening_balance' => Auth::user()->coins,
                        'closing_balance' => Auth::user()->coins + config('constants.COIN_POST_THREE_ITEMS'),
                        'resource_id' => null,
                        'description' => 'Earn coin after post three items'
                    ]);
					
					user_notifications::create([
						'type' => 'EARN_CLICK_COIN',
						'user_id' => Auth::user()->id,
						'resource_id' => config('constants.COIN_POST_THREE_ITEMS'),
						'title' => 'Click Coins Earned',
						'message' => 'Post three items',
					]);

                    User::find(Auth::user()->id)->update(['coins' => Auth::user()->coins + config('constants.COIN_POST_THREE_ITEMS')]);
                    $_getUser = User::with('notificationSetting')->find(Auth::user()->id);
                    if($_getUser && $_getUser->notificationSetting && $_getUser->notificationSetting->is_coin == 1){
                        SendFirebaseNotification('Click Coin','Congratulations, you have earned '.config('constants.COIN_POST_THREE_ITEMS').' Click Coins.',getUsersFCMTokens(Auth::user()->id),['type' => 'EARN_CLICK_COIN','u_id' =>Auth::user()->id]);
                    }
                }
            }

            if($getTotalPostCount == 15){
                $checkTransaction = user_coin_transactions::where([['type','EARN_COIN_AFTER_POST_FIFTEEN_ITEMS'],['user_id',Auth::user()->id]])->count();
                if($checkTransaction == 0){

                    user_coin_transactions::create([
                        'type' => 'EARN_COIN_AFTER_POST_FIFTEEN_ITEMS',
                        'user_id' => Auth::user()->id,
                        'is_credit' => 1,
                        'amount' => config('constants.COIN_POST_FIFTEEN_ITEMS'),
                        'opening_balance' => Auth::user()->coins,
                        'closing_balance' => Auth::user()->coins + config('constants.COIN_POST_FIFTEEN_ITEMS'),
                        'resource_id' => null,
                        'description' => 'Earn coin after post fifteen items'
                    ]);
					
					user_notifications::create([
						'type' => 'EARN_CLICK_COIN',
						'user_id' => Auth::user()->id,
						'resource_id' => config('constants.COIN_POST_FIFTEEN_ITEMS'),
						'title' => 'Click Coins Earned',
						'message' => 'Post fifteen items',
					]);

                    User::find(Auth::user()->id)->update(['coins' => Auth::user()->coins + config('constants.COIN_POST_FIFTEEN_ITEMS')]);
                    $_getUser = User::with('notificationSetting')->find(Auth::user()->id);
                    if($_getUser && $_getUser->notificationSetting && $_getUser->notificationSetting->is_coin == 1){
                        SendFirebaseNotification('Click Coin','Congratulations, you have earned '.config('constants.COIN_POST_FIFTEEN_ITEMS').' Click Coins.',getUsersFCMTokens(Auth::user()->id),['type' => 'EARN_CLICK_COIN','u_id' =>Auth::user()->id]);
                    }

                }
            }
            */

            // first get all nearst users of particular post
            $p_location = $post->locations()->where([['is_active',1],['is_delete',0]])->orderby('id','desc')->first();

            $postlocation = location::where('id',$p_location->location_id)->select('id','address','city','area','postal_code','latitude','longitude')->first();

            $blockedUsers = Auth::user()->listUserBlocked()->pluck('user_id')->toArray();
            $blockedByUsers = Auth::user()->getBlockedUsers()->pluck('blocked_user_id')->toArray();
            $blockUserList = array_merge($blockedUsers,$blockedByUsers);
            
            $postNearstUsers = \DB::table('users')->select('users.*', DB::raw("6371 * acos(cos(radians(" . $postlocation->latitude . ")) * cos(radians(location.latitude)) * cos(radians(location.longitude) - radians(" . $postlocation->longitude . ")) + sin(radians(" .$postlocation->latitude. ")) * sin(radians(location.latitude))) AS distance"))
                ->join('location','users.id','=','location.appuser_id')
                ->where([['location.is_active',1],['location.is_delete',0],['location.is_default',1]])->whereNotIn('users.id', $blockUserList)
                ->groupby('users.id')->having('distance','<=',50)->pluck('id')->toArray();
                    
            //Saved search notification
            // $savedSearches = user_saved_searches::where([['is_active',1],['is_delete',0],['user_id','!=',Auth::user()->id],['gender_id',$post->gender_id]])->whereIn('user_id',$postNearstUsers)->orwhere('condition_id',$post->condition_id)->orwhere('brand_id',$post->brand_id)
            //                 ->where(function($q) use($post) {
            //                     if(!empty($post->categories)){
            //                         $categories = explode(',',$post->categories);
            //                         $categories = implode('|',$categories);
            //                         $q->whereRaw('CONCAT(",", categories, ",") REGEXP ",('.$categories.'),"');
            //                     }
            //                     if(!empty($post->colors)){
            //                         $colors = explode(',',$post->colors);
            //                         $colors = implode('|',$colors);
            //                         $q->whereRaw('CONCAT(",", colors, ",") REGEXP ",('.$colors.'),"');
            //                     }
            //                     if(!empty($post->sizes)){
            //                         $sizes = explode(',',$post->sizes);
            //                         $sizes = implode('|',$sizes);
            //                         $q->whereRaw('CONCAT(",", sizes, ",") REGEXP ",('.$sizes.'),"');
            //                     }                                
            //                     // if($post->price_type == 1){
            //                     //     $q->where('price_from','<=',$post->price)
            //                     //     ->where('price_to','>=',$post->price);
            //                     // }
            //                 })
            //                 ->groupby('user_id')
            //                 ->get();
            
            $postNearstUsers = implode(",",$postNearstUsers);
            $sizes = explode(',',$post->sizes);
            $sizes = implode('|',$sizes);      
            $conditionId = $post->condition_id;
            $brandId = $post->brand_id;
            $categories = explode(',', $post->categories);
            // $categories = implode('|', $categories);
            $finalArray = category::whereDoesntHave('childCategories')->wherein('id',$categories)->where([['is_active',1],['is_delete',0]])->pluck('id')->toArray();            
            $categories = implode('|', $finalArray);            
            $colors = explode(',', $post->colors);
            $colors = implode('|', $colors);
            $userid = Auth::user()->id;
            $priceFrom = $post->price;
            $priceTo = $post->price;
            $roleId = $post->user->role_id;
            
           /*  $searchResult   = 'select * from `user_saved_searches` where (`is_active` = 1 and `is_delete` = 0 and `gender_id` = ' . $post->gender_id . ') and  user_id IN(' . $postNearstUsers . ') and ((`sizes` is not null and (CONCAT(",", sizes, ",") REGEXP ",(' . $sizes . '),") or `sizes` is null) and ((`categories` is not null and (CONCAT(",", categories, ",") REGEXP ",(' . $categories . '),") or `categories` is null)) and ((`brand_id` is not null and (`brand_id` = ' . $brandId . ') or `brand_id` is null)) and ((`colors` is not null and (CONCAT(",", colors, ",") REGEXP ",(' . $colors . '),") or `colors` is null)) and ((`condition_id` is not null and (`condition_id` = ' . $conditionId . ') or `condition_id` is null)) and ((`seller` is not null and (`seller` = ' . $roleId . ') or `seller` is null)) )';
            \Log::debug('query echo started ');
            \Log::debug($searchResult);
            \Log::debug('query echo end ');
            // $searchResult = 'select * from `user_saved_searches` where (`is_active` = 1 and `is_delete` = 0 and `gender_id` = '.$post->gender_id.') and  user_id IN('.$postNearstUsers.') and ((`sizes` is not null and (CONCAT(",", sizes, ",") REGEXP ",('.$sizes.'),") or `sizes` is null) and ((`categories` is not null and (CONCAT(",", categories, ",") REGEXP ",('.$categories.'),") or `categories` is null)) and ((`brand_id` is not null and (`brand_id` = '.$brandId.') or `brand_id` is null)) and ((`colors` is not null and (CONCAT(",", colors, ",") REGEXP ",('.$colors.'),") or `colors` is null)) and ((`condition_id` is not null and (`condition_id` = '.$conditionId.') or `condition_id` is null)) )';         
                $savedSearches = \DB::select(\DB::raw($searchResult));
                $savedSearches = collect($savedSearches);
            if($savedSearches->count() > 0){
                foreach($savedSearches as $search){
                    // check for price type condition
                    // and ((`price_type` is not null and  (`price_type`=1 and `price_from` <= '.$priceFrom.' and `price_to` >= '.$priceTo.' or `price_type` = 2 or `price_type` = 3) or `price_type` is null))
                    
                    $allowedToNotifiy =false;
                    if($search->price_type == null){
                        $allowedToNotifiy =true;
                    }elseif($search->price_type ==1 && $search->price_from <= $priceFrom && $search->price_to >= $priceTo){
                        $allowedToNotifiy =true;
                    }elseif($search->price_type !=1 && $search->price_type == $post->price_type){
                        $allowedToNotifiy =true;
                    }
//                    \Log::debug('allowed to notify');
  //                  \Log::debug($allowedToNotifiy);
    //                \Log::debug($search->price_type);
      //              \Log::debug($post->price_type);
        //            \Log::debug('end allowe to notify');                    
                    if($allowedToNotifiy == false){
                        continue;
                    }
                    \Log::debug('if not allowed continue than why goieng');                    
                    // if user has not distance than default consider as 50, and find the distance from particular post to users distance, if user contain within distance than notify user for that post
                    $makeDistance = 50;
                    if ($search->distance != 0) {
                        $makeDistance = $search->distance;
                    }
                    \Log::debug('get distance for search');
                    \Log::debug($makeDistance);                    
                    \Log::debug('get distance for end');                    
                    $getSaveSearchLocation = \DB::table('users')->select('users.*', DB::raw("6371 * acos(cos(radians(" . $postlocation->latitude . ")) * cos(radians(location.latitude)) * cos(radians(location.longitude) - radians(" . $postlocation->longitude . ")) + sin(radians(" . $postlocation->latitude . ")) * sin(radians(location.latitude))) AS distance"))
                        ->join('location', 'users.id', '=', 'location.appuser_id')
                        ->where([['location.is_active', 1], ['location.is_delete', 0], ['location.is_default', 1], ['users.id', $search->user_id]])
                        ->having('distance', '<=', $makeDistance)->first();
                    if (!$getSaveSearchLocation) {
                        continue;
                    }
                        
                        
                            \Log::debug('before send '.$search->id.' - '.$search->user_id);                        
                            if($search->user_id != Auth::user()->id){
                            \Log::debug('sending to ntofi search '.$search->id.' - '.$search->user_id);                         
                                $new_search = user_saved_searches::find($search->id);
                                $new_search->matched_product_count +=1;
                                $new_search->save();
                                
                    \Log::debug('start module');
                    \Log::debug($new_search->matched_product_count % $new_search->notification_item_counter);
                    \Log::debug('end moulde');                                        
                                $notification_counter = false;
                                if($new_search->notification_item_counter > 1 && $new_search->matched_product_count !=null && $new_search->matched_product_count % $new_search->notification_item_counter == 0) {
                                    $notification_counter = true;
                                }
                                // $search->notification_item_counter == 1 || $search->notification_item_counter <= $new_search->matched_product_count
                            \Log::debug('sending counttt '.$new_search);
                                if($search->notification_item_counter == 1 || $notification_counter == true){
                            \Log::debug('goint for notifii');                                    
                                    user_notifications::create([
                                        'type' => 'SAVED_SEARCH_ALERT',
                                        'user_id' => $search->user_id,
                                        'resource_id' => $post->id,
                                        'resource_id_two' => $search->id,
                                        'title' => 'Saved Search'
                                    ]);
                                    $_getUser = User::with('notificationSetting')->find($search->user_id);
                                    if($_getUser && $_getUser->notificationSetting && $_getUser->notificationSetting->is_save_search == 1)
                                    {                                         
                                            SendFirebaseNotification('Save Search','New post added in your saved search.',getUsersFCMTokens($search->user_id),['post_id' => $post->id, 'save_search_id' => $search->id, 'type' => 'SAVED_SEARCH_ALERT','u_id' =>$search->user_id]);                     
                                    }                                      
                                }
                            }

                    // }                    

                }
            } */
            $this->SearchResultUpdate($post);
            //dd($post);
            $res = posts::getAPIPostDetails($post->id);

            return $this->SendResponse($res, 'Post created successfully', 200);

        }catch(\Exception $e){
            return $this->SendResponse($e, $e->getMessage(), 500);
        }
    }

    public function update(Request $request)
    {
        $all = $request->all();
        $group_id = Auth::user()->role_id;
        
        if($group_id == 3){
            $validator = Validator::make($all, [
                'post_id' => 'required|numeric',
                'brand_id' => 'required|numeric',
                'gender_id' => 'required|numeric',
                'categories' => 'required|string|max:255',
                'sizes' => 'nullable|string|max:255',
//                'condition_id' => 'required|numeric',
                'colors' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:5000',
                //'locations' => 'required|string|max:255',
                'price_type' => 'required|numeric',
                'price' => 'required|numeric',
                'deleted_image_ids' => 'nullable',
                'deleted_video_ids' => 'nullable',
                'image' => 'nullable|array|max:7',
                'image.*' => 'nullable|image|mimes:png,jpeg,jpg|max:5120',
                'video' => 'nullable|array|max:2',
                'video.*' => 'nullable|file|mimes:flv,mp4,avi,wmv,3gp,mkv|max:15360',
            ]);            
        }else{
            $validator = Validator::make($all, [
                'post_id' => 'required|numeric',
                'brand_id' => 'required|numeric',
                'gender_id' => 'required|numeric',
                'categories' => 'required|string|max:255',
                'sizes' => 'nullable|string|max:255',
                'condition_id' => 'required|numeric',
                'colors' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:5000',
                //'locations' => 'required|string|max:255',
                'price_type' => 'required|numeric',
                'price' => 'required|numeric',
                'deleted_image_ids' => 'nullable',
                'deleted_video_ids' => 'nullable',
                'image' => 'nullable|array|max:7',
                'image.*' => 'nullable|image|mimes:png,jpeg,jpg|max:5120',
                'video' => 'nullable|array|max:2',
                'video.*' => 'nullable|file|mimes:flv,mp4,avi,wmv,3gp,mkv|max:15360',
            ]);            
        }

        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{

            $post = posts::where([['id',$all['post_id']],['user_id',Auth::user()->id]])->first();

            if(empty($post)){
                return $this->SendResponse(null, 'Invalid post id', 400);
            }
			$images =[];
            $images = explode(',',$post->images);
            if(isset($all['image']) && !empty($all['image'])){
                foreach($all['image'] as $key => $value){
                    array_push($images,UploadImage($value,'post_images','posts'));
                }
            }

            $videos = explode(',',$post->videos);
            if(isset($all['video']) && !empty($all['video'])){
                foreach($all['video'] as $key => $value){
                    array_push($videos,UploadVideo($value,'post_videos','posts'));
                }
            }

            if(isset($all['deleted_image_ids']) && !empty($all['deleted_image_ids'])){
                $deletedImages = explode(',',$all['deleted_image_ids']);
                foreach($deletedImages as $key => $value){
                    foreach (array_keys($images, $value) as $key) {
                        unset($images[$key]);
                        DeleteImage($value);
                    }
                }
            }

            if(isset($all['deleted_video_ids']) && !empty($all['deleted_video_ids'])){
                $deletedVideos = explode(',',$all['deleted_video_ids']);
                foreach($deletedVideos as $key => $value){
                    foreach (array_keys($videos, $value) as $key) {
                        unset($videos[$key]);
                        DeleteVideo($value);
                    }
                }
            }

            $images = array_filter($images);
            $videos = array_filter($videos);

            $update = posts::find($all['post_id'])->update([
                'brand_id' => $all['brand_id'],
                'gender_id' => $all['gender_id'],
                'categories' => $all['categories'],
                'sizes' => $all['sizes'] ?? 0,
                'colors' => $all['colors'],
                //'locations' => (isset($all['locations']))?$all['locations']:null,
                'condition_id' => ($group_id == 3)?125:$all['condition_id'],
                'title' => $all['title'],
                'description' => $all['description'] ?? null,
                'price_type' => $all['price_type'],
                'price' => $all['price'],
                'style_id' => $all['style'],
                'images' => !empty($images) ? implode(',',$images) : null,
                'videos' => !empty($videos) ? implode(',',$videos) : null,
                'product_url' => (isset($all['product_url']))?$all['product_url']:null,
            ]);

            if(isset($all['categories']) && !empty($all['categories'])){
                $data = [];
                post_categories::where('post_id',$all['post_id'])->delete();
                foreach(explode(',',$all['categories']) as $key => $value){
                    array_push($data,[
                        'post_id' => $post->id,
                        'category_id' => $value,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                post_categories::insert($data);
            }

            if(isset($all['sizes']) && !empty($all['sizes'])){
                $data = [];
                post_sizes::where('post_id',$all['post_id'])->delete();
                foreach(explode(',',$all['sizes']) as $key => $value){
                    array_push($data,[
                        'post_id' => $post->id,
                        'size_id' => $value,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                post_sizes::insert($data);
            }

            if(isset($all['colors']) && !empty($all['colors'])){
                $data = [];
                post_colors::where('post_id',$all['post_id'])->delete();
                foreach(explode(',',$all['colors']) as $key => $value){
                    array_push($data,[
                        'post_id' => $post->id,
                        'color_id' => $value,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                post_colors::insert($data);
            }

            if(isset($all['locations']) && !empty($all['locations'])){
                $data = [];
                post_locations::where('post_id',$all['post_id'])->delete();
                foreach(explode(',',$all['locations']) as $key => $value){
                    array_push($data,[
                        'post_id' => $post->id,
                        'location_id' => $value,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                $updatedLocation =post_locations::insert($data);

            }
            
            if(isset($all['latitude']) && $all['latitude'] !="" && isset($all['longitude']) && $all['longitude'] !=""){
                    $locationArr = [
                        'appuser_id' => Auth::user()->id,
                        'address' => $all['address'] ?? null,
                        'city' => $all['city'] ?? null,
                        'area' => $all['area'] ?? null,
                        'postal_code' => $all['postal_code'] ?? null,
                        'latitude' => $all['latitude'] ?? null,
                        'longitude' => $all['longitude'] ?? null
                    ];
                    $last_location = location::create($locationArr);
                    $last_location->save();                    
                    $update = posts::find($all['post_id'])->update([
                        'locations' => $last_location->id,
                    ]);                    
                $data = [
                        'post_id' => $post->id,
                        'location_id' => $last_location->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')                    
                    ];                
                post_locations::insert($data);                              
                $post->locations = $last_location->id;
                $post->save();
            }   

            if(isset($all['locations']) && $all['locations'] !==''){
				 $d = json_decode($all['locations']);
                    $locationArr = [
                        'appuser_id' => Auth::user()->id,
                        'address' => $d->address,
                        'city' => $d->city ,
                        'area' => $d->area ,
                        'postal_code' => $d->postal_code ,
                        'latitude' => $d->latitude ,
                        'longitude' => $d->longitude
                    ];
					//echo "<pre>";print_r($locationArr);exit;
                    $last_location = location::create($locationArr);
                    $last_location->save();   
					$update = posts::find($all['post_id'])->update([
                        'locations' => $last_location->id,
                    ]); 					
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

            // if price is update and new price is lower than previous than we will send notification            
            $newPost = posts::find($all['post_id']);
            if($newPost->price_type == 1 && $all['price'] < $post->price){
                $getWhishlistedUsers = user_favourite_posts::where([['is_active',1],['is_delete',0],['user_id','!=',Auth::user()->id],['post_id',$all['post_id']]])->groupby('user_id')->get();
                foreach($getWhishlistedUsers as $watchlist){
                    user_notifications::create([
                        'type' => 'WATCHLIST_PRICE_DROP',
                        'user_id' => $watchlist->user_id,
                        'resource_id' => $watchlist->post_id,
                        'title' => 'Price Drop'
                    ]);
                    SendFirebaseNotification('Price Drop','A favorite post has dropped in price.',getUsersFCMTokens($watchlist->user_id),['post_id' => $watchlist->post_id,'type' => 'WATCHLIST_PRICE_DROP']);
                }                
            }
            
            $this->SearchResultUpdate($post);
            $res = posts::getAPIPostDetails($post->id);

            return $this->SendResponse($res, 'Post updated successfully', 200);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function details(Request $request)
    {
        $all = $request->all();
        $validator = Validator::make($all, [
            'post_id' => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{
            $post = posts::find($all['post_id']);
			//echo "<pre>";print_r($post);exit;
			if(Auth::user()){
				recent_postview::create(['user_id' => Auth::user()->id,'post_id'=> $post->id]);
				if(Auth::user()->id != $post->user_id){
					posts::find($post->id)->update(['post_views' => $post->post_views + 1]);
					
				}
			}
			
            if(isset($all['m__test'])){
				//  dd($post->locations);
            }
            $res = posts::getAPIPostDetails($all['post_id']);
			//echo "<pre>";print_r($res);exit;
            if(isset($all['m__test'])){
							

			
            // first get all nearst users of particular post
            $p_location = $post->locations()->where([['is_active',1],['is_delete',0]])->orderby('id','desc')->first();
           // dd($p_location);

            $postlocation = location::where('id',$p_location->location_id)->select('id','address','city','area','postal_code','latitude','longitude')->first();
            $postNearstUsers = \DB::table('users')->select('users.*', DB::raw("6371 * acos(cos(radians(" . $postlocation->latitude . ")) * cos(radians(location.latitude)) * cos(radians(location.longitude) - radians(" . $postlocation->longitude . ")) + sin(radians(" .$postlocation->latitude. ")) * sin(radians(location.latitude))) AS distance"))
                ->join('location','users.id','=','location.appuser_id')
                ->where([['location.is_active',1],['location.is_delete',0],['location.is_default',1]])
                ->groupby('users.id')->having('distance','<=',100)->pluck('id')->toArray();
//                dd($postNearstUsers);
            $postNearstUsers = implode(",",$postNearstUsers);
            $sizes = explode(',',$post->sizes);
            $sizes = implode('|',$sizes);      
            $conditionId = $post->condition_id;
            $brandId = $post->brand_id;
            $styleId = $post->style;
            $categories = explode(',', $post->categories);
            $categories = implode('|', $categories);
            $colors = explode(',', $post->colors);
            $colors = implode('|', $colors);
            $userid = Auth::user()->id;
            $priceFrom = ($post->price_from !=null)?$post->price_from:0;
            $priceTo = ($post->price_to !=null)?$post->price_to:0;
            $roleId = $post->user->role_id;
            
            $searchResult   = 'select * from `user_saved_searches` where (`is_active` = 1 and `is_delete` = 0 and `gender_id` = ' . $post->gender_id . ') and  user_id IN(' . $postNearstUsers . ') and ((`sizes` is not null and (CONCAT(",", sizes, ",") REGEXP ",(' . $sizes . '),") or `sizes` is null) and ((`categories` is not null and (CONCAT(",", categories, ",") REGEXP ",(' . $categories . '),") or `categories` is null)) and ((`brand_id` is not null and (`brand_id` = ' . $brandId . ') or `brand_id` is null)) and ((`colors` is not null and (CONCAT(",", colors, ",") REGEXP ",(' . $colors . '),") or `colors` is null)) and ((`condition_id` is not null and (`condition_id` = ' . $conditionId . ') or `condition_id` is null)) and ((`seller` is not null and (`seller` = ' . $roleId . ') or `seller` is null)) and ((`price_type` is not null and  (`price_type`=1 and `price_from` <= '.$priceFrom.' and `price_to` >= '.$priceTo.' or `price_type` = 2 or `price_type` = 3) or `price_type` is null)) )';
          //  echo $searchResult;exit;
        $savedSearches = \DB::select(\DB::raw($searchResult));
        $savedSearches = collect($savedSearches);
        //dd($savedSearches);
            if($savedSearches->count() > 0){
                foreach($savedSearches as $search){
                    //dd($search);
                    // if user has not distance than default consider as 50, and find the distance from particular post to users distance, if user contain within distance than notify user for that post
                    $makeDistance = 50;
                    if ($search->distance != 0) {
                        $makeDistance = $search->distance;
                    }
                    $getSaveSearchLocation = \DB::table('users')->select('users.*', DB::raw("6371 * acos(cos(radians(" . $postlocation->latitude . ")) * cos(radians(location.latitude)) * cos(radians(location.longitude) - radians(" . $postlocation->longitude . ")) + sin(radians(" . $postlocation->latitude . ")) * sin(radians(location.latitude))) AS distance"))
                        ->join('location', 'users.id', '=', 'location.appuser_id')
                        ->where([['location.is_active', 1], ['location.is_delete', 0], ['location.is_default', 1], ['users.id', $search->user_id]])
                        ->having('distance', '<=', $makeDistance)->first();
                    if (!$getSaveSearchLocation) {
                        continue;
                    }
                    
                    \Log::debug('before send '.$search->id.' - '.$search->user_id);                        
                    if($search->user_id != Auth::user()->id){
                    \Log::debug('sending to ntofi search '.$search->id.' - '.$search->user_id);                            
                        user_notifications::create([
                            'type' => 'SAVED_SEARCH_ALERT',
                            'user_id' => $search->user_id,
                            'resource_id' => $post->id,
                            'resource_id_two' => $search->id,
                            'title' => 'Saved Search'
                        ]);
                        $_getUser = User::with('notificationSetting')->find($search->user_id);
                        if($_getUser && $_getUser->notificationSetting && $_getUser->notificationSetting->is_save_search == 1){                                         
                            SendFirebaseNotification('Save Search','New post added in your saved search.',getUsersFCMTokens($search->user_id),['post_id' => $post->id, 'save_search_id' => $search->id, 'type' => 'SAVED_SEARCH_ALERT','u_id' =>$search->user_id]);                     
                        }                            
                    }
                }
            }
            }
            return $this->SendResponse($res, 'Success', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function delete(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($all, [
            'post_id' => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{
            
            $check = posts::where([['user_id',Auth::user()->id],['id',$all['post_id']],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();

            posts::where('id',$all['post_id'])->delete();

            return $this->SendResponse(null, 'Post deleted successfully.', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function DeleteImage(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($all, [
            'post_id' => 'required|numeric',
            'image_id' => 'required|numeric',
        ]);
        //echo "<pre>";print_r($all);exit;
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{
            
            $post = posts::where('id',$all['post_id'])->first();
			$images = explode(',', $post->images); // Convert string to array
			$images = array_filter($images, fn($image) => $image !== $all['image_id']);
			$post->images = implode(',', $images); // Convert array back to string
			$post->save(); // Save to DB

            return $this->SendResponse(null, 'Image deleted successfully.', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function sold(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($all, [
            'post_id' => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{
            
            $check = posts::where([['user_id',Auth::user()->id],['id',$all['post_id']],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();

            if(empty($check)){
                return $this->SendResponse(null, 'Invalid post id', 400);
            }

            posts::find($check->id)->update(['is_sold' => 1, 'sold_at' => date('Y-m-d H:i:s')]);

            $getSoldItems = posts::where([['user_id',Auth::user()->id],['is_sold',1]])->count();

            if($getSoldItems == 3){
                $checkTransaction = user_coin_transactions::where([['type','EARN_COIN_AFTER_SELL_THREE_ITEMS'],['user_id',Auth::user()->id]])->count();
                if($checkTransaction == 0){

                    user_coin_transactions::create([
                        'type' => 'EARN_COIN_AFTER_SELL_THREE_ITEMS',
                        'user_id' => Auth::user()->id,
                        'is_credit' => 1,
                        'amount' => config('constants.COIN_SELL_THREE_ITEMS'),
                        'opening_balance' => Auth::user()->coins,
                        'closing_balance' => Auth::user()->coins + config('constants.COIN_SELL_THREE_ITEMS'),
                        'resource_id' => null,
                        'description' => 'Earn coin after sell three items'
                    ]);
					
					user_notifications::create([
						'type' => 'EARN_CLICK_COIN',
						'user_id' => Auth::user()->id,
						'resource_id' => config('constants.COIN_SELL_THREE_ITEMS'),
						'title' => 'Click Coins Earned',
						'message' => 'Sell three items',
					]);

                    User::find(Auth::user()->id)->update(['coins' => Auth::user()->coins + config('constants.COIN_SELL_THREE_ITEMS')]);
                    $_getUser = User::with('notificationSetting')->find(Auth::user()->id);
                    if($_getUser && $_getUser->notificationSetting && $_getUser->notificationSetting->is_coin == 1){
                        SendFirebaseNotification('Click Coin','Congratulations, you have earned '.config('constants.COIN_SELL_THREE_ITEMS').' Click Coins.',getUsersFCMTokens(Auth::user()->id),['type' => 'EARN_CLICK_COIN','u_id' =>Auth::user()->id]);
                    }

                }
            }

            return $this->SendResponse(null, 'Item sold successfully.', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function changeType(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($all, [
            'post_id' => 'required|numeric',
            'type' => 'required|numeric|min:1|max:5',
            'sale_price' => 'nullable|required_if:type,4|numeric'
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{
            
            $check = posts::where([['user_id',Auth::user()->id],['id',$all['post_id']],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();

            if(empty($check)){
                return $this->SendResponse(null, 'Invalid post id', 400);
            }

            $expireDate = null;
            $type = null;

            if($all['type'] == 1){

                if(!empty(config('constants.POST_TOP_PICK_DAYS'))){
                    $expireDate = date('Y-m-d H:i:s',strtotime('+'.config('constants.POST_TOP_PICK_DAYS').' days'));
                }
                $type = 1;
                $update = ['is_toppick' => 1, 'top_pick_expire_at' => $expireDate];
                $coin = config('constants.POST_TOP_PICK_COIN');
                $description = 'Top picks';
            }elseif($all['type'] == 2){
                if(!empty(config('constants.POST_BUMP_DAYS'))){
                    $expireDate = date('Y-m-d H:i:s',strtotime('+'.config('constants.POST_BUMP_DAYS').' days'));
                }
                $type = 2;
                $update = ['is_bump' => 1,'bump_expire_at' => $expireDate];
                $coin = config('constants.POST_BUMP_COIN');
                $description = 'Bump';
            }elseif($all['type'] == 3){
                if(!empty(config('constants.POST_HIGHTLIGHT_DAYS'))){
                    $expireDate = date('Y-m-d H:i:s',strtotime('+'.config('constants.POST_HIGHTLIGHT_DAYS').' days'));
                }
                $type = 3;
                $update = ['is_hightlight' => 1,'hightlight_expire_at' => $expireDate];
                $coin = config('constants.POST_HIGHTLIGHT_COIN');
                $description = 'Highlight';
            }elseif($all['type'] == 4){
                if(!empty(config('constants.POST_SALE_DAYS'))){
                    $expireDate = date('Y-m-d H:i:s',strtotime('+'.config('constants.POST_SALE_DAYS').' days'));
                }
                $type = 4;
                $update = ['is_sale' => 1,'sale_price' => $all['sale_price'],'sale_expire_at' => $expireDate];
                $coin = config('constants.POST_SALE_COIN');
                $description = 'Sale';
            }elseif($all['type'] == 5){
                if(!empty(config('constants.POST_PROFILE_PROMOTE_DAYS'))){
                    $expireDate = date('Y-m-d H:i:s',strtotime('+'.config('constants.POST_PROFILE_PROMOTE_DAYS').' days'));
                }
                $type = 5;
                $update = ['is_profile_promote' => 1,'profile_promote_expire_at' => $expireDate];
                $coin = config('constants.POST_PROFILE_PROMOTE_COIN');
                $description = 'Profile Promote';
            }

            if(Auth::user()->coins < $coin){
                return $this->SendResponse(null, 'You dont have sufficient coins, please purchase coin to continue.', 400);
            }

            $openingBalance = Auth::user()->coins;
            $closingBalance = Auth::user()->coins - $coin;

            User::find(Auth::user()->id)->update(['coins' => $closingBalance]);

            user_coin_transactions::create([
                'type' => 'PROMOTE_POST',
                'user_id' => Auth::user()->id,
                'is_credit' => 0,
                'amount' => $coin,
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'resource_id' => $check->id,
                'description' => $description
            ]);

            if($all['type'] == 4){
                $getWhishlistedUsers = user_favourite_posts::where([['is_active',1],['is_delete',0],['user_id','!=',Auth::user()->id],['post_id',$check->id]])->groupby('user_id')->get();
                foreach($getWhishlistedUsers as $watchlist){
                    user_notifications::create([
                        'type' => 'WATCHLIST_PRICE_DROP',
                        'user_id' => $watchlist->user_id,
                        'resource_id' => $watchlist->post_id,
                        'title' => 'Price Drop'
                    ]);
                    $_getUser = User::with('notificationSetting')->find($watchlist->user_id);
                    if($_getUser && $_getUser->notificationSetting && $_getUser->notificationSetting->is_watchlist == 1){
                        SendFirebaseNotification('Price Drop','A favorite post has dropped in price.',getUsersFCMTokens($watchlist->user_id),['post_id' => $watchlist->post_id, 'type'=>'WATCHLIST_PRICE_DROP','u_id' =>$watchlist->user_id]);
                    }
                }
            }

            posts::find($check->id)->update($update);

            if(!empty($type)){
                user_post_promotion_logs::create([
                    'user_id' => Auth::user()->id,
                    'post_id' => $check->id,
                    'promotion_type' => $type
                ]);
            }

            return $this->SendResponse(null, 'Post promoted successfully.', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function soldUserList(Request $request)
    {
        try{

            $all = $request->all();

            $validator = Validator::make($all, [
                'post_id' => 'required|numeric',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $getSenders = messages::where([['post_id',$all['post_id']],['sender_user_id','!=',Auth::user()->id]])->groupby('sender_user_id')->pluck('sender_user_id')->toarray();
            $getReceivers = messages::where([['post_id',$all['post_id']],['receiver_user_id','!=',Auth::user()->id]])->groupby('receiver_user_id')->pluck('receiver_user_id')->toarray();

            $finalUsers = array_unique(array_merge($getSenders,$getReceivers));

            $users = User::wherein('id',$finalUsers)->where([['is_active',1],['is_delete',0]])->get();

            $data = [];
            foreach($users as $user){
                array_push($data,[
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'photo' => ($user->photo !=null)?ResizeImage($user->photo,500,500):null,
                    'avg_review' => $user->reviews()->avg('rating'),
                    'total_posts' => $user->posts()->where([['is_active',1],['is_delete',0]])->count(),
                    'location' => $user->locations()->where([['is_active',1],['is_delete',0],['is_default',0]])->select('id','address','city','area','postal_code','latitude','longitude')->orderby('id','desc')->first()
                ]);
            }

            $post = posts::find($all['post_id']);

            $res['total_sold_items'] = posts::where([['user_id',Auth::user()->id],['is_sold',1]])->count();
            $res['post'] = [
                'id' => $post->id,
                'name' => $post->title,
                'brand_name' => $post->brand->name ?? null,
                'photo' => posts::getImages($post->images,true),
                'size' => posts::getSize($post->sizes,true),
                'price' => $post->price,
                'price_type' => $post->price_type
            ];

            $res['user_list'] = $data;

            return $this->SendResponse($res, 'Success', 200);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    // public function soldPostToUser(Request $request)
    // {
    //     try{

    //         $all = $request->all();

    //         $validator = Validator::make($all, [
    //             'post_id' => 'required|numeric',
    //             'user_id' => 'required|numeric'
    //         ]);
            
    //         if ($validator->fails()) {
    //             return $this->SendResponse(null, $validator->messages()->first(), 400);
    //         }

    //         $post = posts::where([['user_id',Auth::user()->id],['id',$all['post_id']]])->first();

    //         if(empty($post)){
    //             return $this->SendResponse(null, 'Invalid post id', 400);
    //         }

    //         // if($post->is_sold == 0){
    //         //     return $this->SendResponse(null, 'Post is not sold yet.', 400);
    //         // }

    //         posts::find($all['post_id'])->update(['sold_user_id' => $all['user_id']]);

    //         $user = User::find($all['user_id']);

    //         $res = [];
    //         $res['user'] = [
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'username' => $user->username,
    //             'photo' => ResizeImage($user->photo,500,500),
    //             'avg_review' => $user->reviews()->avg('rating'),
    //             'total_posts' => $user->posts()->where([['is_active',1],['is_delete',0]])->count(),
    //             'location' => $user->locations()->where([['is_active',1],['is_delete',0],['is_default',0]])->select('id','address','city','area','postal_code','latitude','longitude')->orderby('id','desc')->first()
    //         ];

    //         $res['post'] = [
    //             'id' => $post->id,
    //             'name' => $post->title,
    //             'brand_name' => $post->brand->name ?? null,
    //             'photo' => posts::getImages($post->images,true),
    //             'size' => posts::getSize($post->sizes,true),
    //             'price' => $post->price,
    //             'price_type' => $post->price_type
    //         ];

    //         return $this->SendResponse($res, 'Success', 200);

    //     }catch(\Exception $e){
    //         return $this->SendResponse(null, $e->getMessage(), 500);
    //     }
    // }
    
    public function SearchResultUpdate(posts $post){
        // dd($post);
        $post = posts::with('sizes','gender','brand','condition')->find($post->id);
        // dd($post->categories);
        $get_categories = explode(',',$post->categories);

        $finalArray = category::whereDoesntHave('childCategories')->whereIn('id',$get_categories)->where([['is_active',1],['is_delete',0]])->pluck('name','id')->first();
            // dd($post->sizes);
            // dd($finalArray);
        $sizeId = explode(',',$post->sizes);            
        $colorId = explode(',',$post->colors);                    
        // $sizeId = $post->sizes()->toArray();
        // dd($sizeId);
        $sizeName = size::whereIn('id',$sizeId)->pluck('name')->toArray();
        $colorName = color::whereIn('id',$colorId)->pluck('name')->toArray();

        $condition_name = $post->condition->name;

        $post->search_gender = $post->gender->name;
        $post->search_brand = ($post->brand)?$post->brand->name:null;
        $post->search_category = $finalArray;
        $post->search_size = implode(",", $sizeName);
        $post->search_condition = $condition_name;        
        $post->search_color = implode(",", $colorName);

        // search_size  
        $post->save();
        return $post;
        // return 
    }
    public function calculateOrderAmount(array $items): int {
		// Calculate the order total on the server to prevent
		// people from directly manipulating the amount on the client
		$total = 0;
		foreach($items as $item) {
		  $total += $item->amount;
		}
		return $total;
	}
   
	public function createPaymentIntent(Request $request){
		$all = $request->all();
		$post = posts::where([['id',$all['post_id']]])->first();
		//$user_id = Auth::user()->id;
		$user_id = 2;
		$location = location::where([['appuser_id',$user_id]])->orderBy('id','desc')->first();
		$address = explode(',', $location->address);
		//echo "<pre>";print_r($address);exit;
		$country = $address[count($address)-1];
		//$total_element = count($address)-2;
		//$post_code = $address[$total_element];
//echo "<pre>";print_r($_SERVER['DOCUMENT_ROOT'].'/vendor/stripe/stripe-php/init.php');
		require_once('/var/www/html/vendor/stripe/stripe-php/init.php');
//exit;

        $stripe =getStripeObject(); 

		header('Content-Type: application/json');

		try {
			// retrieve JSON from POST body
			$jsonStr = file_get_contents('php://input');
			$jsonObj = json_decode($jsonStr);
			$customer = $stripe->customers->create([
			  'name' => Auth::user()->name,
			  'email' => Auth::user()->email,
			]);
			
			// Create a PaymentIntent with amount and currency
			$paymentIntent = $stripe->paymentIntents->create([
				'amount' => $post->price * 100,
				'currency' => 'cad',
				// In the latest version of the API, specifying the `automatic_payment_methods` parameter is optional because Stripe enables its functionality by default.
				'automatic_payment_methods' => [
				'enabled' => true,	
				  'allow_redirects' => 'never'			
					], 
				//'payment_method_types'=>['card'],	
				'setup_future_usage' => 'off_session',
				'customer' => $customer->id,
				//'payment_method' => $payment_method->id,
				'shipping'=> [
					  'name'=> Auth::user()->name,
					  'address'=> [
						'line1'=> $location->address,
						'city'=> $location->city,
						'postal_code'=> $location->postal_code,
						'state'=> $location->city,
						'country'=> $country,
					  ],
				],				
			]);

			$paymentIntent->sk = env('STRIPE_SECRET_KEY');
			return json_encode($paymentIntent);
		} catch(Stripe_CardError $e) {
			  $error1 = $e->getMessage();
			} catch (Stripe_InvalidRequestError $e) {
			  // Invalid parameters were supplied to Stripe's API
			  $error2 = $e->getMessage();
			} catch (Stripe_AuthenticationError $e) {
			  // Authentication with Stripe's API failed
			  $error3 = $e->getMessage();
			} catch (Stripe_ApiConnectionError $e) {
			  // Network communication with Stripe failed
			  $error4 = $e->getMessage();
			} catch (Stripe_Error $e) {
			  // Display a very generic error to the user, and maybe send
			  // yourself an email
			  $error5 = $e->getMessage();
			} catch (Exception $e) {
			  // Something else happened, completely unrelated to Stripe
			  $error6 = $e->getMessage();
			}
    }	
	public function getPaymentIntent(Request $request){


		
        $stripe =getStripeObject(); 


		header('Content-Type: application/json');

		try {
			// retrieve JSON from POST body
			$jsonStr = file_get_contents('php://input');
			$jsonObj = json_decode($jsonStr);
			$customer = $stripe->customers->create([
			  'name' => Auth::user()->name,
			  'email' => Auth::user()->email,
			]);
			
			// Create a PaymentIntent with amount and currency
			$paymentIntent = $stripe->paymentIntents->retrieve('pi_3PzjLsH8pVCp4ycE1qHRVUqP',[]);


			return json_encode($paymentIntent);
		} catch(Stripe_CardError $e) {
			  $error1 = $e->getMessage();
			} catch (Stripe_InvalidRequestError $e) {
			  // Invalid parameters were supplied to Stripe's API
			  $error2 = $e->getMessage();
			} catch (Stripe_AuthenticationError $e) {
			  // Authentication with Stripe's API failed
			  $error3 = $e->getMessage();
			} catch (Stripe_ApiConnectionError $e) {
			  // Network communication with Stripe failed
			  $error4 = $e->getMessage();
			} catch (Stripe_Error $e) {
			  // Display a very generic error to the user, and maybe send
			  // yourself an email
			  $error5 = $e->getMessage();
			} catch (Exception $e) {
			  // Something else happened, completely unrelated to Stripe
			  $error6 = $e->getMessage();
			}
    }
    
}
