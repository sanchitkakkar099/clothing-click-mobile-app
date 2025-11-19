<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use DB;
use App\user_notification_settings;
use App\user_logindevice_info;
use App\User;
use App\posts;
use App\post_categories;
use App\post_colors;
use App\post_locations;
use App\post_sizes;
use App\category;
use App\size;
use App\color;
use App\brand;
use App\style;

class UserController extends APIResponseController {

    public function getOtherUserDetails(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'username' => 'nullable|required_without:user_id|string|max:255',
                'user_id' => 'nullable|required_without:username|numeric'
            ]);
    
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $all = $request->all();
            if(isset($all['username']) && !empty($all['username'])){
                $user = User::where([['username',$all['username']],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();
            }else{
                $user = User::where([['id',$all['user_id']],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();
            }

            if(empty($user)){
                return $this->SendResponse(null, 'Invalid input parameter', 400);
            }

            $user = User::getAPIUserDetails($user,true);

            return $this->SendResponse($user, 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function getUserPost(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'tab' => 'nullable|numeric|min:1|max:2',
                'sort_by' => 'nullable|string',
                'sort_value' => 'nullable|string',
				'gender_id' => 'nullable|string',
                'sizes' => 'nullable|string',
                'colors' => 'nullable|string',
                'condition_id' => 'nullable|numeric',
                'distance' => 'nullable|numeric',
                'style' => 'nullable|string',
                'brand_id' => 'nullable|string',
                'price_type' => 'nullable|numeric',
                'price_from' => 'nullable|required_if:price_type,1|numeric',
                'price_to' => 'nullable|required_if:price_type,1|numeric' 
            ]);
    
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $all = $request->all();

            $user = User::find($all['user_id']);

            if(empty($user)){
                return $this->SendResponse(null, 'Invalid user id', 400);
            }

            if(!isset($all['tab']) || empty($all['tab'])){
                $all['tab'] = 1;
			}
            $parentCategory = category::with(['childCategories' => function ($q2) {
                $q2->where('is_delete', 0)->where('is_active', 1);
            }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();

            $groupedCategories = [];
            $gropItem          = [];
            $auth_user = Auth::user();  



			$isDistanceCodition = true;
            $distance = 50;
            if(isset($all['distance']) && $all['distance'] > 0){
                $isDistanceCodition = true;
                $distance = $all['distance'];
            }

            $sizes = [];
            

            //$user = $all['user_id'];
            
            $finalArray = [];
            
            if(!empty($all['categories'])){
                $get_categories = explode(',',$all['categories']);
                $finalArray = category::whereDoesntHave('childCategories')->wherein('id',$get_categories)->where([['is_active',1],['is_delete',0]])->pluck('id')->toArray();                            
            }

           


			if($auth_user){
				//echo "in if";exit;
            if (isset($all['sort_by']) && $all['sort_by'] == 'category') {            
                foreach ($parentCategory as $category) {
                    $catSubCatPost = posts::where([['posts.is_active',1],['posts.is_delete',0],['posts.user_id',$user->id]])
                    ->where(function($q) use($all,$user, $auth_user) {
                        if($user->id != $auth_user->id){
                            $q->PrivateAccountPost($auth_user);
                        }
                        if($user->role_id == 1){
    
                            if($all['tab'] == 1){
                                $isSold = 0;
                            }else{
                                $isSold = 1;
                            }
    
                            $q->where('posts.is_sold',$isSold);
                        }else{
							if($all['tab'] == 1){
                                $isSold = 0;
                            }else{
                                $isSold = 1;
                            }
							 $q->where('posts.is_sold',$isSold);
                           if(!empty($all['gender_id'])){
                        $q->where('posts.gender_id', $all['gender_id'])->where('posts.is_sold', 0);
						}
                        }
    
                    });
                    if ($category->childCategories && count($category->childCategories) > 0) {
                        $subCategories = $category->childCategories()->pluck('id');
                        $catSubCatPost = $catSubCatPost->whereHas('categories', function ($q3) use ($subCategories) {
                                $q3->whereIn('category_id', $subCategories->toArray());
                            })->get();

                        if (count($catSubCatPost) > 0) {
                            //grouping all subcategories 
                            $grouped = $catSubCatPost->groupBy(function($item, $key){
                                        $item_val = explode(",",$item->categories);
                                        return $item_val[1];
                            })->sortByDesc('categories');   

                            $grouped = $grouped->toArray();
                            ksort($grouped);

                            foreach ($grouped as $key => $items) {
                                foreach ($items as $get_item => $item) {
                                    array_push($gropItem, $item['id']);
                                }
                            }
                        }                        

                    } else {
                        $catSubCatPost = $catSubCatPost->whereHas('categories', function ($q3) use ($category) {
                                $q3->where('category_id', $category->id);
                            })->get();
                        if (count($catSubCatPost) > 0) {
                            foreach ($catSubCatPost as $key => $item) {
                                    array_push($gropItem, $item->id);
                            }
                        }                    
                    }                    
                }
            }
            
             $posts  = posts::where([['posts.is_active', 1], ['posts.is_delete', 0], ['posts.user_id', $all['user_id']]])
                ->where(function ($q) use ($all, $user, $auth_user) {
                    if ($user->id != $auth_user->id) {
                        $q->PrivateAccountPost($auth_user);
                    }
                    if ($user->role_id == 1) {

                        if ($all['tab'] == 1) {
                            $isSold = 0;
                        } else {
                            $isSold = 1;
                        }

                        $q->where('posts.is_sold', $isSold);
                    } else {
						if($all['tab'] == 1){
                                $isSold = 0;
                            }else{
                                $isSold = 1;
                            }
							 $q->where('posts.is_sold',$isSold);
                        if(!empty($all['gender_id'])){
                        $q->where('posts.gender_id', $all['gender_id'])->where('posts.is_sold', 0);
						}
                    }

                }); 
			}else{
				//echo "in else";exit;
				
				if (isset($all['sort_by']) && $all['sort_by'] == 'category') {
					
                foreach ($parentCategory as $category) {
                    $catSubCatPost = posts::where([['posts.is_active',1],['posts.is_delete',0],['posts.user_id',$user->id]])
                    ->where(function($q) use($all,$user) {
                       
                        if($user->role_id == 1){
    
                            if($all['tab'] == 1){
                                $isSold = 0;
                            }else{
                                $isSold = 1;
                            }
    
                            $q->where('posts.is_sold',$isSold);
                        }else{
							if($all['tab'] == 1){
                                $isSold = 0;
                            }else{
                                $isSold = 1;
                            }
							 $q->where('posts.is_sold',$isSold);
                           if(!empty($all['gender_id'])){
                        $q->where('posts.gender_id', $all['gender_id'])->where('posts.is_sold', 0);
						}
                        }
    
                    });
                    if ($category->childCategories && count($category->childCategories) > 0) {
                        $subCategories = $category->childCategories()->pluck('id');
                        $catSubCatPost = $catSubCatPost->whereHas('categories', function ($q3) use ($subCategories) {
                                $q3->whereIn('category_id', $subCategories->toArray());
                            })->get();

                        if (count($catSubCatPost) > 0) {
                            //grouping all subcategories 
                            $grouped = $catSubCatPost->groupBy(function($item, $key){
                                        $item_val = explode(",",$item->categories);
                                        return $item_val[1];
                            })->sortByDesc('categories');   

                            $grouped = $grouped->toArray();
                            ksort($grouped);

                            foreach ($grouped as $key => $items) {
                                foreach ($items as $get_item => $item) {
                                    array_push($gropItem, $item['id']);
                                }
                            }
                        }                        

                    } else {
                        $catSubCatPost = $catSubCatPost->whereHas('categories', function ($q3) use ($category) {
                                $q3->where('category_id', $category->id);
                            })->get();
                        if (count($catSubCatPost) > 0) {
                            foreach ($catSubCatPost as $key => $item) {
                                    array_push($gropItem, $item->id);
                            }
                        }                    
                    }                    
                }
            }
            
             $posts  = posts::where([['posts.is_active', 1], ['posts.is_delete', 0], ['posts.user_id', $all['user_id']]])
                ->where(function ($q) use ($all, $user) {
                   
                    if ($user->role_id == 1) {

                        if ($all['tab'] == 1) {
                            $isSold = 0;
                        } else {
                            $isSold = 1;
                        }

                        $q->where('posts.is_sold', $isSold);
                    } else {
						if($all['tab'] == 1){
                                $isSold = 0;
                            }else{
                                $isSold = 1;
                            }
							 $q->where('posts.is_sold',$isSold);
						if(!empty($all['gender_id'])){
                        $q->where('posts.gender_id', $all['gender_id'])->where('posts.is_sold', 0);
						}
                    }

                }); 
				
				
				
			}
			  $brands = '';
			if(isset($all['brand_id'])){
				$brands = explode(',',$all['brand_id']);
			}
					if(!empty($all['gender_id'])){
						$posts->whereIn('posts.gender_id',explode(',',$all['gender_id']));
					}
					if($brands){
						$posts->whereIn('posts.brand_id',$brands);
					}
					if(isset($all['condition_id'])){
						$posts->where('posts.condition_id',$all['condition_id']);
					}
					if(isset($all['style'])){
						$posts->whereIn('posts.style_id',explode(',',$all['style']));
					}
					if(isset($all['price_type'])){
						$posts->where('posts.price_type',$all['price_type']);
					}
					if(isset($all['sizes'])){
						$posts->whereIn('posts.sizes',explode(',',$all['style']));
					}
					if(!empty($all['categories'])){
						$posts->whereIn('posts.categories',explode(',',$all['categories']));
                    }
					if(!empty($all['colors'])){
                        $posts->whereIn('posts.colors',explode(',',$all['colors']));
                    }
					
					if(!empty($all['price_from'])){
						$posts->where('posts.price','>=',(int)$all['price_from']);
					}
					if(!empty($all['price_to'])){
						$posts->where('posts.price','<=',(int)$all['price_to']);
					}
					
					
            if(isset($all['sort_by']) && !empty($all['sort_by'])){
                switch($all['sort_by']){
                    case 'date':
                        $posts->orderby('posts.created_at',$all['sort_value']);
                        break;
                    case 'price':
                        $query = "CAST(posts.price AS INT) ".$all['sort_value'];
                        $posts->orderByRaw($query);
                        break;
                    case 'category':
                        $gropItem = implode(",", $gropItem);
                        $posts->orderByRaw('FIELD(posts.id, ' . $gropItem . ') asc');
                        break;
                    case 'size':
                        $authUser = \Auth::user();
						if($authUser){
                        $userSizes = $authUser->userSizes()->pluck('size_id')->toArray();
                        $posts->whereIn('posts.sizes',$userSizes);
						}
                        break;                        
                    default:
                        $posts->orderBy('posts.id','desc');
                        break;
                }
            }else{
				$posts->orderBy('posts.id','desc');
			}
            if(isset($all['m__test'])){
              // dd($posts->toSql());
            }
			//dd($posts->get());
            $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
    
            $postData['currentPage'] = $posts->currentPage();
            $postData['hasMorePages'] = $posts->hasMorePages();
            $postData['posts'] = posts::getAPIPostListData($posts->items());
            
            return $this->SendResponse($postData, 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
    
    public function getBrandPost(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'brand_id' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
		/* 	if(request()->bearerToken()){
				$logged_in = user_logindevice_info::where('access_token_id',request()->bearerToken())->first();
				Auth::loginUsingId($logged_in->appuser_id);
			} */

			//print_r($logged_in->appuser_id);exit;
            $user = Auth::user();

            $all = $request->all();
			$cordinates = array('latitude'=> $all['latitude'],'longitude'=> $all['longitude']);
			//$gender_id = $all['gender_id'];
			if (isset($all['gender_id'])) {
				$gender_id = $all['gender_id'];
			} else {
				// handle the case where it's missing
				$genderId = ''; // or show a user-friendly error
			}
                /// category filter
                $groupedCategories = [];
                $gropItem          = [];
                $auth_user = Auth::user();
				if($auth_user){
					//echo "Logged in user";exit;
                if (isset($all['sort_by']) && $all['sort_by'] == 'category') {            
                    $parentCategory = category::with(['childCategories' => function ($q2) {
                        $q2->where('is_delete', 0)->where('is_active', 1);
                    }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();                
                    foreach ($parentCategory as $category) {
                        $catSubCatPost = posts::RemoveBlockUserPosts()->PrivateAccountPost($user)->GetNearestPost($user,true)
                        ->where([['posts.gender_id',$user->user_selected_gender],['posts.is_active',1],['posts.is_delete',0],['posts.is_sold',0],['posts.user_id','!=',Auth::user()->id],['posts.brand_id',$all['brand_id']]]);

                        if ($category->childCategories && count($category->childCategories) > 0) {
                            $subCategories = $category->childCategories()->pluck('id');
                            $catSubCatPost = $catSubCatPost->whereHas('categories', function ($q3) use ($subCategories) {
                                    $q3->whereIn('category_id', $subCategories->toArray());
                                })->get();

                            if (count($catSubCatPost) > 0) {
                                //grouping all subcategories 
                                $grouped = $catSubCatPost->groupBy(function($item, $key){
                                            $item_val = explode(",",$item->categories);
                                            return $item_val[1];
                                })->sortByDesc('categories');   

                                $grouped = $grouped->toArray();
                                ksort($grouped);

                                foreach ($grouped as $key => $items) {
                                    foreach ($items as $get_item => $item) {
                                        array_push($gropItem, $item['id']);
                                    }
                                }
                            }                        

                        } else {
                            $catSubCatPost = $catSubCatPost->whereHas('categories', function ($q3) use ($category) {
                                    $q3->where('category_id', $category->id);
                                })->get();
                            if (count($catSubCatPost) > 0) {
                                foreach ($catSubCatPost as $key => $item) {
                                        array_push($gropItem, $item->id);
                                }
                            }                    
                        }                    
                    }
                }
                /// category filter end  
			//if( isset($all['latitude']) && isset($all['longitude'])){				
            $posts = posts::RemoveBlockUserPosts()
						->PrivateAccountPost($user)
						->GetNearestPostDashboard($cordinates, true)
						->where([
							['posts.is_active', 1],
							['posts.is_delete', 0],
							['posts.is_sold', 0],
							['posts.user_id', '!=', Auth::user()->id],
							['posts.brand_id', $all['brand_id']]
						]);

				if (!empty($gender_id)) {
					$posts->where('posts.gender_id', $gender_id);
				}

				$posts = $posts->distinct('posts.id');

			/* }else{
				$posts = posts::RemoveBlockUserPosts()->PrivateAccountPost($user)
            ->where([['posts.gender_id',$user->user_selected_gender],['posts.is_active',1],['posts.is_delete',0],['posts.is_sold',0],['posts.user_id','!=',Auth::user()->id],['posts.brand_id',$all['brand_id']]])->distinct('posts.id');
			} */
				}else{
					 if (isset($all['sort_by']) && $all['sort_by'] == 'category') {            
                    $parentCategory = category::with(['childCategories' => function ($q2) {
                        $q2->where('is_delete', 0)->where('is_active', 1);
                    }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();                
                    foreach ($parentCategory as $category) {
                        $catSubCatPost = posts::where([['posts.is_active',1],['posts.is_delete',0],['posts.is_sold',0],['posts.brand_id',$all['brand_id']]]);

                        if ($category->childCategories && count($category->childCategories) > 0) {
                            $subCategories = $category->childCategories()->pluck('id');
                            $catSubCatPost = $catSubCatPost->whereHas('categories', function ($q3) use ($subCategories) {
                                    $q3->whereIn('category_id', $subCategories->toArray());
                                })->get();

                            if (count($catSubCatPost) > 0) {
                                //grouping all subcategories 
                                $grouped = $catSubCatPost->groupBy(function($item, $key){
                                            $item_val = explode(",",$item->categories);
                                            return $item_val[1];
                                })->sortByDesc('categories');   

                                $grouped = $grouped->toArray();
                                ksort($grouped);

                                foreach ($grouped as $key => $items) {
                                    foreach ($items as $get_item => $item) {
                                        array_push($gropItem, $item['id']);
                                    }
                                }
                            }                        

                        } else {
                            $catSubCatPost = $catSubCatPost->whereHas('categories', function ($q3) use ($category) {
                                    $q3->where('category_id', $category->id);
                                })->get();
                            if (count($catSubCatPost) > 0) {
                                foreach ($catSubCatPost as $key => $item) {
                                        array_push($gropItem, $item->id);
                                }
                            }                    
                        }                    
                    }
                }
                /// category filter end   
					//if( isset($all['latitude']) && isset($all['longitude'])){				
					$posts = posts::GetNearestPostDashboard($cordinates, true)
						->where([
							['posts.is_active', 1],
							['posts.is_delete', 0],
							['posts.is_sold', 0],
							['posts.brand_id', $all['brand_id']]
						]);

					if (!empty($gender_id)) {
						$posts->where('posts.gender_id', $gender_id);
					}

					$posts = $posts->distinct('posts.id');

					/* }else{
						$posts = posts::where([['posts.is_active',1],['posts.is_delete',0],['posts.is_sold',0],['posts.brand_id',$all['brand_id']]])->distinct('posts.id');
					} */
				}

            if(isset($all['sort_by']) && !empty($all['sort_by'])){
                switch($all['sort_by']){
                    case 'date':
                        $posts->orderby('posts.created_at',$all['sort_value']);
                        break;
                    case 'price':
                        $query = "CAST(posts.price AS INT) ".$all['sort_value'];
                        $posts->orderByRaw($query);
                        break;
                    case 'distance':
                        $posts->orderby('distance',$all['sort_value']);
                        break;
                    case 'category':
                        $gropItem = implode(",", $gropItem);
                        $posts->orderByRaw('FIELD(posts.id, ' . $gropItem . ') asc');                               
                    break;
                    case 'size':
                        $authUser = \Auth::user();
						if($authUser){
                        $userSizes = $authUser->userSizes()->pluck('size_id')->toArray();                        
                        $posts->whereIn('posts.sizes',$userSizes);
						}
                    break;                                                                                   
                    // case 'category':

                    //     $categories = explode(',',$all['sort_value']);
                    //     $categories = implode('|',$categories);

                    //     $posts->whereRaw('CONCAT(",", posts.categories, ",") REGEXP ",('.$categories.'),"');
                    //     break;
                    default:
                        $posts->orderby('distance','asc');
                        break;
                }
            }

            $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
            $postData['currentPage'] = $posts->currentPage();
            $postData['hasMorePages'] = $posts->hasMorePages();
			$postData['posts_count'] =count(posts::getAPIPostListData($posts->items()));
			
            $postData['posts'] = posts::getAPIPostListData($posts->items());
			
            
            
            return $this->SendResponse($postData, 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }        
    
    public function deleteUser(Request $request){
        try {
            // $validator = Validator::make($request->all(), [
            //     'user_id' => 'required|numeric|exists:users',
            // ]);

            // if ($validator->fails()) {
            //     return $this->SendResponse(null, $validator->messages()->first(), 400);
            // }
            $user = Auth::user();

//            $id = $request->user_id;
        //    $user = User::find($id);
            $user->posts()->delete();
            $user->userSizes()->delete();
            $user->userFavouriteBrands()->delete();
            $user->userFavouritePosts()->delete();
            $user->followings()->delete();        
            $user->followers()->delete();                
            
            User::destroy($user->id);


            return $this->SendResponse('User deleted successfully', 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
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
    

}
