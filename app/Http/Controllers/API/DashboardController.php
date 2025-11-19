<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
use App\User;
use App\posts;
use App\store_details;
use App\user_notifications;
use App\gender;
use App\user_saved_searches;
use App\banner;
use App\user_page_visit_logs;
use App\brand;
use App\category;

class DashboardController extends APIResponseController {

    public function index(Request $request)
    {
        try {
			 DB::enableQueryLog(); // Enable query logging

			
			//echo "<pre>"; print_r($group_id = Auth::user());exit;
            $user = Auth::user();
			//echo "<pre>";print_r($user);exit;
			//print_r($group_id = Auth::user()->id);exit;
			
            $res = [];
			$unread_notification =0;
			$user_sizes=[];
			$user_brands =[];
			$all = $request->all();
			$validator = Validator::make($request->all(), [
				'latitude' => 'nullable|string',
				'longitude' => 'nullable|string',
			]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }
		
			$cordinates = array('latitude'=> $all['latitude'],'longitude'=> $all['longitude']);
			//print_r($cordinates);exit;
			
			if($user){
				//echo "user exists";exit;
				$recommendedPost = posts::query();

				// Nearest post logic (if this is a scope or custom query)
				$recommendedPost = $recommendedPost->GetNearestPostDashboard($cordinates, true);

				// Private + remove sold
				$recommendedPost = $recommendedPost->PrivateAccountPost($user)->RemoveSoldPost();

				// Only posts from active users
				$recommendedPost = $recommendedPost->whereHas('user', function($q) {
					$q->where('is_active', 1);
				});

				// Gender filter
				if (isset($all['gender'])) {
					$recommendedPost = $recommendedPost->where('posts.gender_id', $all['gender'])
					   ->where('posts.user_id', '!=', $user->id)
					   ->where('posts.is_active', 1)
					   ->where('posts.is_delete', 0);
				}

				// Size + brand filter
				if (count($user_sizes) > 0 || count($user_brands) > 0) {
					$recommendedPost = $recommendedPost->where(function($q) use ($user_sizes, $user_brands) {
						
						if (count($user_brands) > 0) {
							$q->whereIn('posts.brand_id', $user_brands);
						}

						if (count($user_sizes) > 0) {
							$size_regex = implode('|', $user_sizes);
							$q->whereRaw('CONCAT(",", posts.sizes, ",") REGEXP ",(' . $size_regex . '),"')
							  ->orWhere(function($q2) use ($user_brands) {
								  $q2->where('posts.sizes', 0);
								  if (count($user_brands) > 0) {
									  $q2->whereIn('posts.brand_id', $user_brands);
								  }
							  });
						}

					});
				}

                /* if(count($user_brands) > 0){
					//echo "In brands";exit;
                    $user_brands = implode(",", $user_brands);
                    $recommendedPost = $recommendedPost->orderby('id','desc')->orderByRaw('FIELD(posts.brand_id, ' . $user_brands . ') asc')->orderby('sizes','desc')->take(25)->get();
                }else{
					//echo "In not brands";exit;
                    $recommendedPost = $recommendedPost->orderby('sizes','desc')->orderby('id','desc')->take(25)->get();
                }  */
				//$recommendedPost->get();
				//dd($recommendedPost->toSql());

			
			}else{
				$gender_id = '';
				if (isset($all['gender'])) {
					$gender_id = $all['gender'];
				}
				
				$recommendedPost = posts::whereHas('user', function($q){
                    $q->where('is_active',1);
                })->RemoveSoldPost()->GetNearestPostDashboard($cordinates ,true)
                ->where([['posts.is_active',1],['posts.is_delete',0]]);
				// apply gender condition only if 1 or 2
					if (in_array($gender_id, [1, 2])) {
						$recommendedPost->where('posts.gender_id', $gender_id);
					}


                
				$recommendedPost = $recommendedPost->orderby('id','desc');
				//echo "<pre>";print_r($recommendedPost);exit;
			}
			
            if(posts::getAPIPostListData($recommendedPost->get())){ 
				array_push($res,[
					'type_id' => 2,
					'name' => 'Recommended',
					'type' => 'posts',
					'list' => posts::getAPIPostListData($recommendedPost->get()),
					'unread_notification' => $unread_notification
					//'list' => posts::getAPIPostListData(posts::postDistance($user)->RemoveBlockUserPosts()->where([['gender_id',$user->user_selected_gender],['user_id','!=',$user->id],['is_active',1],['is_delete',0]])->orderby('created_at','desc')->take(10)->get())
				]);
			}
			
			if($user){
				$query = posts::whereHas('user', function($q){
							$q->where('is_active', 1);
						})
						->GetNearestPostDashboard($cordinates, true)
						->postFollowings($user)
						->RemoveBlockUserPosts()
						->RemoveSoldPost()
						->where([
							['posts.user_id', '!=', $user->id],
							['posts.is_active', 1],
							['posts.is_delete', 0]
						]);

					// apply gender filter only if 1 or 2
					if (in_array($user->user_selected_gender, [1, 2])) {
						$query->where('posts.gender_id', $user->user_selected_gender);
					}

					$flwng = posts::getAPIPostListData(
						$query->distinct('posts.id')
							  ->orderBy('id', 'desc')
							  ->take(25)
							  ->get()
					); 

			}else{
				$gender_id = '';
					if (isset($all['gender'])) {
						$gender_id = (int) $all['gender']; // cast to int to avoid string issues
					}

					$flwng = posts::whereHas('user', function($q){
							$q->where('is_active', 1);
						})
						->RemoveSoldPost()
						->GetNearestPostDashboard($cordinates, true)
						->where([
							['posts.is_active', 1],
							['posts.is_delete', 0]
						]);

					// apply gender filter only if gender_id is 1 or 2
					if (in_array($gender_id, [1, 2], true)) {
						$flwng->where('posts.gender_id', $gender_id);
					}

					$flwng = posts::getAPIPostListData(
						$flwng->distinct('posts.id')
							  ->orderBy('id', 'desc')
							  ->take(25)
							  ->get()
					);

			}
			if($flwng && $user){
				array_push($res,[
					'type_id' => 3,
					'name' => 'Following',
					'type' => 'posts',
					'list' => $flwng,
					'unread_notification' => $unread_notification
				]);
			}
					//	echo "<pre>";print_r($res);exit;

		$cordinates = [
    'latitude'  => $all['latitude'],
    'longitude' => $all['longitude']
];

$gender_id = isset($all['gender_id']) ? $all['gender_id'] : null;

if ($user) {

    // STEP 1: Filter brands using whereHas (just like the second code)
    $brandIds = brand::select('id')
        ->whereHas('posts', function ($query) use ($user, $gender_id) {

            $query->PrivateAccountPost($user)
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->where('is_sold', 0);

            if (in_array((int) $gender_id, [1,2], true)) {
                $query->where('gender_id', (int) $gender_id);
            }
        })
        ->orderBy('id','desc')
        ->pluck('id')
        ->toArray();

    // STEP 2: Load posts same as second code
    $posts = brand::with([
        'posts' => function ($query) use ($user, $cordinates, $gender_id) {

            $query->whereHas('user', function ($q) {
                $q->where('is_active', 1);
            })
            ->PrivateAccountPost($user)
            ->GetNearestPostDashboard($cordinates, true)
            ->where('posts.is_active', 1)
            ->where('posts.is_delete', 0)
            ->where('posts.is_sold', 0);

            if (in_array((int) $gender_id, [1,2], true)) {
                $query->where('posts.gender_id', (int) $gender_id);
            }

            $query->distinct('posts.id');
        }
    ])
    ->whereIn('id', $brandIds);

} else {

    // GUEST MODE: Apply same logic as second code
    $brandIds = brand::select('id')
        ->whereHas('posts', function ($query) use ($gender_id) {
            $query->where('is_active', 1)
                ->where('is_delete', 0)
                ->where('is_sold', 0);

            if (in_array((int) $gender_id, [1,2], true)) {
                $query->where('gender_id', (int) $gender_id);
            }
        })
        ->orderBy('id','desc')
        ->pluck('id')
        ->toArray();

    $posts = brand::with([
        'posts' => function ($query) use ($cordinates, $gender_id) {

            $query->whereHas('user', function ($q) {
                $q->where('is_active', 1);
            })
            ->GetNearestPostDashboard($cordinates, true)
            ->where('posts.is_active', 1)
            ->where('posts.is_delete', 0)
            ->where('posts.is_sold', 0);

            if (in_array((int) $gender_id, [1,2], true)) {
                $query->where('posts.gender_id', (int) $gender_id);
            }

            $query->distinct('posts.id');
        }
    ])
    ->whereIn('id', $brandIds);
}

// Sorting (same behavior)
if (isset($all['sort_by']) && !empty($all['sort_by'])) {

    switch ($all['sort_by']) {

        case 'date':
            $posts->orderBy('id', $all['sort_value']);
            break;

        case 'distance':
            $posts->orderBy('posts_count', $all['sort_value']);
            break;

        default:
            // do nothing
            break;
    }

} else {
    $posts->orderBy('id','desc');
}

$posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));


         //  echo "<pre>";print_r(brand::getAPIStoreListData($brand_posts));exit;
            //*** start update by mahesh
			if(brand::getAPIStoreListData($posts)){
            array_push($res,[
                'type_id' => 4,
                'name' => 'Popular Brands',
                'type' => 'brands',
                'list' => brand::getAPIStoreListData($posts),
                'unread_notification' => $unread_notification               
               
            ]);            
            //*** end       
			}
			
			
			$user = Auth::user();

			// echo "<pre>";print_r($user);exit;

           if(in_array((int)$gender_id, [1, 2], true)){
				$gender_name = gender::find($gender_id);
				$name_gender = $gender_name->name;
				$gender_id = $gender_id;
		   }else{
			   $gender_name = 0;
			   $name_gender = '';
			   $gender_id = '';
		   }
		   //echo $gender_name;exit;
			//if($user->user_selected_gender){
				if($gender_name !==0){
					array_push($res,[
						'type_id' => 5,
						'name' => 'Shop '.$name_gender,
						'type' => 'categories',
						'list' => $this->getGenderCategoriesbyGenderId($gender_id),
						'unread_notification' => $unread_notification
					]);
				}else{
					
					array_push($res,[
						'type_id' => 5,
						'name' => 'Shop Menswear',
						'type' => 'categories',
						'list' => $this->getGenderCategoriesbyGenderId(1),
						'unread_notification' => $unread_notification
					]); 
					array_push($res,[
						'type_id' => 8,
						'name' => 'Shop Womenswear',
						'type' => 'categories',
						'list' => $this->getGenderCategoriesbyGenderId(2),
						'unread_notification' => $unread_notification
					]);
				}
				//
			//}
			//echo "<pre>";print_r($res);exit;
			$local_stores = User::where('role_id','8')->orderby('id','desc')->take(25)->get();
			$store_details = $this->getStoredetails($local_stores);
			//echo "<pre>";print_r($local_stores);exit;
			if($store_details){
				// Stores lists commented
			 array_push($res,[
                'type_id' => 6,
                'name' => 'Shop From Stores',
                'type' => 'stores',
                'list' => $store_details,
                'unread_notification' => $unread_notification
            ]); 
			}
			if(Auth::user()){
			 array_push($res,[
                'type_id' => 7,
                'name' => 'Saved Searches',
                'type' => 'saved_search',
                'list' => user_saved_searches::where([['user_id',Auth::user()->id],['is_active',1],['is_delete',0]])->orderby('id','desc')->select('id','name')->take(5)->get(),
                'unread_notification' => $unread_notification
            ]);
			}
			
            return $this->SendResponse($res, 'Success', 200);
            
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
	public function getGenderCategoriesbyGenderId($genderId = '')

    {

        $res = [];
		if($genderId){
        $categories =  category::where([['gender_id',$genderId],['parent_id',0],['is_active',1],['is_delete',0]])->orderby('id','asc')->get();
		}else{
				$categories =  category::where([['parent_id',0],['is_active',1],['is_delete',0]])->orderby('id','asc')->get();
		}

        foreach($categories as $category){

            array_push($res,[

                'category_id' => $category->id,

                'name' => $category->name,
                'photo' => ResizeImage($category->photo,50,50),
                'icon' => ResizeImage($category->icon,100,100),

                //'childCategories' => $category->childCategories

            ]);

        }

        return $res;

    }	
    public function post_list(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($request->all(), [
            'list_type' => 'required|numeric|min:2|max:9',
            'sort_by' => 'nullable|string|max:15',
            'sort_value' => 'nullable|required_with:sort_by|string|max:5',
			'gender_id' => 'nullable|string',
            'sizes' => 'nullable|string',
            'colors' => 'nullable|string',
            'condition_id' => 'nullable|numeric',
            'distance' => 'nullable|numeric',
            'style' => 'nullable|string',
            'brand_id' => 'nullable|string',
            'price_type' => 'nullable|numeric',
            'cat_id' => 'nullable|string',
            'price_from' => 'nullable|required_if:price_type,1|numeric',
            'price_to' => 'nullable|required_if:price_type,1|numeric',
			'latitude' => 'nullable|string',
			'longitude' => 'nullable|string',			
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{

            $user = Auth::user();
            $res = [];
            $pagetype = null;
			$user_sizes =[];
			$user_brands =[];
			
			$cordinates = array('latitude'=> $all['latitude'],'longitude'=> $all['longitude']);
			if($user){
				$user_sizes = $user->userSizes()->where('is_active',1)->pluck('size_id')->toArray();
				$user_brands = $user->userFavouriteBrands()->where('is_active',1)->pluck('brand_id')->toArray();
			}
            
            switch($all['list_type']){

                case 2: 
                /// category filter
                $groupedCategories = [];
                $gropItem          = [];
                $auth_user = Auth::user();            
                if (isset($all['sort_by']) && $all['sort_by'] == 'category'  && $auth_user) {            
                    $parentCategory = category::with(['childCategories' => function ($q2) {
                        $q2->where('is_delete', 0)->where('is_active', 1);
                    }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();                
                    foreach ($parentCategory as $category) {
                        $catSubCatPost = posts::whereHas('user', function($q){
                        $q->where('is_active',1);
                    })->GetNearestPostDashboard($cordinates ,true)->PrivateAccountPost($user)->RemoveBlockUserPosts()->RemoveSoldPost()
                        ->where([['posts.gender_id',$user->user_selected_gender],['posts.user_id','!=',$user->id],['posts.is_active',1],['posts.is_delete',0]])
                        ->where(function($q) use($user_sizes,$user_brands) {
                            if(count($user_brands) > 0){
                                $q->whereIn('posts.brand_id',$user_brands);
                            }       
                            if(count($user_sizes) > 0){
                                $user_sizes = implode('|',$user_sizes);
                                $q->whereRaw('CONCAT(",", posts.sizes, ",") REGEXP ",('.$user_sizes.'),"')->orWhere('posts.sizes', 0)->where(function($q2) use($user_brands){
                                        $q2->whereIn('posts.brand_id',$user_brands);
                                    });
                            }else{
                                $q->where('posts.sizes', 0)->where(function($q2) use($user_brands){
                                    $q2->whereIn('posts.brand_id',$user_brands);
                                    });
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
					$posts = posts::whereHas('user', function($q){
                    $q->where('is_active',1);
                })->PrivateAccountPost($user)->GetNearestPostDashboard($cordinates ,true)->RemoveBlockUserPosts()->RemoveSoldPost()
                    ->where([['posts.gender_id',$user->user_selected_gender],['posts.user_id','!=',$user->id],['posts.is_active',1],['posts.is_delete',0]])
                    ->where(function($q) use($user_sizes,$user_brands) {
                        if(count($user_brands) > 0){
                            $q->whereIn('posts.brand_id',$user_brands);
                        }       
                        if(count($user_sizes) > 0){
                            $user_sizes = implode('|',$user_sizes);
                            $q->whereRaw('CONCAT(",", posts.sizes, ",") REGEXP ",('.$user_sizes.'),"')->orWhere('posts.sizes', 0)->where(function($q2) use($user_brands){
                                $q2->whereIn('posts.brand_id',$user_brands);
                            });
                        }else{
                            $q->where('posts.sizes', 0)->where(function($q2) use($user_brands){
                                $q2->whereIn('posts.brand_id',$user_brands);
                            });         
                        }                                     
                    });
                }else{
					$parentCategory = category::with(['childCategories' => function ($q2) {
                        $q2->where('is_delete', 0)->where('is_active', 1);
                    }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();                
                    foreach ($parentCategory as $category) {
                        $catSubCatPost = posts::whereHas('user', function($q){
                        $q->where('is_active',1);
                    })->RemoveSoldPost()
                        ->where([['posts.is_active',1],['posts.is_delete',0]]);
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
					$posts = posts::whereHas('user', function($q){
							$q->where('is_active',1);
						})->RemoveSoldPost()->GetNearestPostDashboard($cordinates ,true)
							->where([['posts.is_active',1],['posts.is_delete',0]]);
					
				}
                /// category filter end

                    

					$brands = '';
					if(isset($all['brand_id'])){
						$brands = explode(',',$all['brand_id']);
					}
					if($all['gender_id']){
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
						$posts->whereIn('posts.sizes',explode(',',$all['sizes']));
					}
					if(!empty($all['categories'])){
						$posts->whereIn('posts.categories',explode(',',$all['categories']));
                    }
					if(!empty($all['colors'])){
                        $posts->whereIn('posts.colors',explode(',',$all['colors']));
                    }
					
					if(!empty($all['price_from'])){
						$posts->where('posts.price','>=',$all['price_from']);
					}
					if(!empty($all['price_to'])){
						$posts->where('posts.price','<=',$all['price_to']);
					}
					if(!empty($all['seller'])){
						$posts->with('user')->whereHas('user',function($s) use($all) {
                                $s->where('role_id',$all['seller']);
                            });
					};




                    if(isset($all['sort_by']) && !empty($all['sort_by'])){
                        switch($all['sort_by']){
                            case 'date':
                                $posts->orderby('posts.sizes','desc')->orderby('posts.id',$all['sort_value']);
                                break;
                            case 'price':
                                $posts->orderby('posts.price',$all['sort_value']);
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
                            $userSizes = $authUser->userSizes()->pluck('size_id')->toArray();                        
                            $posts->whereIn('posts.sizes',$userSizes);
                            break;                                                    
                            default:
                                if(count($user_brands) > 0){
                                    $user_brands = implode(",", $user_brands);
                                    $posts = $posts->orderby('posts.id','desc')->orderby('posts.sizes','desc')->orderByRaw('FIELD(posts.brand_id, ' . $user_brands . ') asc')->orderby('distance','asc');
                                }else{
                                    $posts = $posts->orderby('posts.id','desc')->orderby('posts.sizes','desc')->orderby('distance','asc');
                                }                                
                                //$posts->orderby('distance','asc');
                                break;
                        }
                    }else{
                       $posts = $posts->orderby('posts.id','desc');                        
                    }

                    $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));

                    $res['currentPage'] = $posts->currentPage();
                    $res['hasMorePages'] = $posts->hasMorePages();
                    $res['posts'] = posts::getAPIPostListData($posts->items());

                    $pagetype = 'RECOMMANDED';

                    break;
                
                case 3:
                /// category filter
                $groupedCategories = [];
                $gropItem          = [];
                $auth_user = Auth::user();            
                if ($auth_user) {   
                    $parentCategory = category::with(['childCategories' => function ($q2) {
                        $q2->where('is_delete', 0)->where('is_active', 1);
                    }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();                
                    foreach ($parentCategory as $category) {
                        $catSubCatPost = posts::whereHas('user', function($q){
                            $q->where('is_active',1);
                            })->PrivateAccountPost($user)->GetNearestPost($user,false)->RemoveBlockUserPosts()->RemoveSoldPost()->postFollowings($user)->where([['posts.gender_id',$user->user_selected_gender],['posts.user_id','!=',$user->id],['posts.is_active',1],['posts.is_delete',0]]);

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
					$posts = posts::whereHas('user', function($q){
                    $q->where('is_active',1);
                })->PrivateAccountPost($user)->RemoveBlockUserPosts()->RemoveSoldPost()->postFollowings($user)->where([['posts.gender_id',$user->user_selected_gender],['posts.user_id','!=',$user->id],['posts.is_active',1],['posts.is_delete',0]]);
                }else{
					$parentCategory = category::with(['childCategories' => function ($q2) {
                        $q2->where('is_delete', 0)->where('is_active', 1);
                    }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();                
                    foreach ($parentCategory as $category) {
                        $catSubCatPost = posts::whereHas('user', function($q){
                            $q->where('is_active',1);
                            })->RemoveSoldPost()->where([['posts.is_active',1],['posts.is_delete',0]]);

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
					$posts = posts::whereHas('user', function($q){
                    $q->where('is_active',1);
                })->RemoveSoldPost()->GetNearestPostDashboard($cordinates ,true)->where([['posts.is_active',1],['posts.is_delete',0]]);
				}
                
				
				$brands = '';
					if(isset($all['brand_id'])){
						$brands = explode(',',$all['brand_id']);
					}
					if($all['gender_id']){
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
						$posts->whereIn('posts.sizes',explode(',',$all['sizes']));
					}
					if(!empty($all['categories'])){
						$posts->whereIn('posts.categories',explode(',',$all['categories']));
                    }
					if(!empty($all['colors'])){
                        $posts->whereIn('posts.colors',explode(',',$all['colors']));
                    }
					
					if(!empty($all['price_from'])){
						$posts->where('posts.price','>=',$all['price_from']);
					}
					if(!empty($all['price_to'])){
						$posts->where('posts.price','<=',$all['price_to']);
					}
					if(!empty($all['seller'])){
						$posts->with('user')->whereHas('user',function($s) use($all) {
                                $s->where('role_id',$all['seller']);
                            });
					};


				

                    if(isset($all['sort_by']) && !empty($all['sort_by'])){
                        switch($all['sort_by']){
                            case 'date':
                                $posts->orderby('posts.id',$all['sort_value']);
                                break;
                            case 'price':
                                $posts->orderby('posts.price',$all['sort_value']);
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
								$userSizes = $authUser->userSizes()->pluck('size_id')->toArray();                        
								$posts->whereIn('posts.sizes',$userSizes);
								break;                                                                          
                            default:
                                $posts->orderby('distance','asc');
                                break;
                        }
                    }else{
                       $posts = $posts->orderby('posts.id','desc');                        
                    }

                    $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));

                    $res['currentPage'] = $posts->currentPage();
                    $res['hasMorePages'] = $posts->hasMorePages();
                    $res['posts'] = posts::getAPIPostListData($posts->items());
                    $pagetype = 'FOLLOWING';
                    break;

                case 4:
				$cordinates = array('latitude'=> $all['latitude'],'longitude'=> $all['longitude']);
				$gender_id = $all['gender_id'];
				

				if($user){
									
					
					 $brand_posts =brand::select('id')->whereHas('posts', function($query) use ($user) {
									$query->PrivateAccountPost($user)->where('is_active', 1)->where('is_delete', 0)->where('is_sold', 0);
								})
								->orderBy('id', 'desc') // Assuming the brand has a 'name' column
								->get()->toArray();
							//echo "<pre>";	print_r($brand_posts);exit;
					$brandIds = array_column($brand_posts, 'id');

                   $posts = brand::with(['posts' => function($query) use ($user, $cordinates, 			$gender_id) {
						$query->whereHas('user', function($query) {
							$query->where('is_active', 1); // Ensure the user is active
						})
						->GetNearestPostDashboard($cordinates, true)
						->where('posts.is_active', 1)
						->where('posts.is_delete', 0)
						->where('posts.is_sold', 0);

						if (!empty($gender_id)) {
							$query->where('posts.gender_id', $gender_id);
						}

						$query->distinct('posts.id');
					}])
					->whereIn('id', $brandIds);
					
				}else{
					/* $brand_posts = brand::with(['posts' => function($q2) use($user){
                    $q2->where([['posts.is_active',1],['posts.is_delete',0],['is_sold',0]]);
                        }])->where([['is_active',1],['is_delete',0]])->get()->sortByDesc(function($brand_posts)
                        {
                            return $brand_posts->posts->count();
                        });
                            $brandId = [];
                            foreach($brand_posts as $brand){
                                if($brand->posts->count() > 0){
                                    array_push($brandId, $brand->id);
                                }
                            }
                    $ids_ordered = implode(',', $brandId);
					 */
					 $brand_posts =brand::select('id')->whereHas('posts', function($query) {
									$query->where('is_active', 1)
										  ->where('is_delete', 0)
										  ->where('is_sold', 0);
								})
								->orderBy('id', 'desc') // Assuming the brand has a 'name' column
								->get()->toArray();
							//echo "<pre>";	print_r($brand_posts);exit;
					$brandIds = array_column($brand_posts, 'id');

					$posts = brand::with(['posts' => function($query) use ($user, $cordinates, $gender_id) {
						$query->whereHas('user', function($query) {
							$query->where('is_active', 1); // Ensure the user is active
						})
						->GetNearestPostDashboard($cordinates, true)
						->where('posts.is_active', 1)
						->where('posts.is_delete', 0)
						->where('posts.is_sold', 0);

						if (!empty($gender_id)) {
							$query->where('posts.gender_id', $gender_id);
						}

						$query->distinct('posts.id');
					}])
					->whereIn('id', $brandIds);
				}
					/* $brands = '';
					if(isset($all['brand_id'])){
						$brands = explode(',',$all['brand_id']);
					}
					if($all['gender_id']){
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
						$sizes = explode(',', $all['sizes']);
						$posts->whereIn('posts.sizes',$sizes);
					}
					if(!empty($all['categories'])){
						$posts->whereIn('posts.categories',explode(',',$all['categories']));
                    }
					if(!empty($all['colors'])){
                        $posts->whereIn('posts.colors',explode(',',$all['colors']));
                    }
					
					if(!empty($all['price_from'])){
						$posts->where('posts.price','>=',$all['price_from']);
					}
					if(!empty($all['price_to'])){
						$posts->where('posts.price','<=',$all['price_to']);
					}
					if(!empty($all['seller'])){
						$posts->with('user')->whereHas('user',function($s) use($all) {
                                $s->where('role_id',$all['seller']);
                            });
					};
  */

                    
                    if(isset($all['sort_by']) && !empty($all['sort_by'])){
                        switch($all['sort_by']){
                            case 'date':
                                $posts->orderby('id',$all['sort_value']);
                                break;
                            case 'distance':
                                $posts->orderby('posts_count',$all['sort_value']);
                                break;
                            default:
                            //    $posts->orderby('posts_count','desc');
                                break;
                        }
                    }else{
                       $posts = $posts->orderby('id','desc');                        
                    }
					//$posts->orderByRaw("FIELD(id, '".$ids_ordered."'");
                        // $posts->get()->sortByDesc(function($posts)
                        // {
                        //     return $posts->posts->count();
                        // });    
                        
                    $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
                    //$posts = $posts->get();
                    // dd($posts->items);
                     $res['currentPage'] = $posts->currentPage();
                    $res['hasMorePages'] = $posts->hasMorePages();
                    //$res['currentPage'] = 1;
                    //$res['hasMorePages'] = 1;                    
                    $res['posts'] = brand::getAPIStoreListData($posts->items());
                    $pagetype = 'POPULARBRANDS';
                    break;                    

               /*  /// category filter
                $groupedCategories = [];
                $gropItem          = [];
                $auth_user = Auth::user();            
                if (isset($all['sort_by']) && $all['sort_by'] == 'category') {            
                    $parentCategory = category::with(['childCategories' => function ($q2) {
                        $q2->where('is_delete', 0)->where('is_active', 1);
                    }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();                
                    foreach ($parentCategory as $category) {
                        $catSubCatPost = posts::whereHas('user', function($q){
                            $q->where('is_active',1);
                        })->PrivateAccountPost($user)->GetNearestPost($user,true)->RemoveBlockUserPosts()->RemoveSoldPost()->postTopPicks($user)->where([['posts.user_id','!=',$user->id],['posts.is_active',1],['posts.is_delete',0]]);

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
                    $posts = posts::whereHas('user', function($q){
                    $q->where('is_active',1);
                })->PrivateAccountPost($user)->GetNearestPost($user,true)->RemoveBlockUserPosts()->RemoveSoldPost()->postTopPicks($user)->where([['posts.user_id','!=',$user->id],['posts.is_active',1],['posts.is_delete',0]]);

                    if(isset($all['sort_by']) && !empty($all['sort_by'])){
                        switch($all['sort_by']){
                            case 'date':
                                $posts->orderby('posts.created_at',$all['sort_value']);
                                break;
                            case 'price':
                                $posts->orderby('posts.price',$all['sort_value']);
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
                            $userSizes = $authUser->userSizes()->pluck('size_id')->toArray();                        
                            $posts->whereIn('posts.sizes',$userSizes);
                            break;                                                                                         
                            default:
                                $posts->orderby('distance','asc');
                                break;
                        }
                    }

                    $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));

                    $res['currentPage'] = $posts->currentPage();
                    $res['hasMorePages'] = $posts->hasMorePages();
                    $res['posts'] = posts::getAPIPostListData($posts->items());
                    $pagetype = 'TOPPICK';
                    break; */

                case 5:

                /// category filter
                $groupedCategories = [];
                $gropItem          = [];
                $auth_user = Auth::user();            
                if (isset($all['sort_by']) && $all['sort_by'] == 'category' && $auth_user) {            
                    $parentCategory = category::with(['childCategories' => function ($q2) {
                        $q2->where('is_delete', 0)->where('is_active', 1);
                    }])->where('parent_id', 0)->where('id',$all['cat_id'])->where('is_delete', 0)->where('is_active', 1)->get();                
                    foreach ($parentCategory as $category) {
                        $catSubCatPost = posts::whereHas('user', function($q){
                    $q->where('is_active',1);
                })->PrivateAccountPost($user)->RemoveBlockUserPosts()->RemoveSoldPost()->where([['posts.gender_id',$user->user_selected_gender],['posts.user_id','!=',$user->id],['posts.is_active',1],['posts.is_delete',0]]);

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
					$posts = posts::whereHas('user', function($q){
                    $q->where('is_active',1);
                })->PrivateAccountPost($user)->GetNearestPostDashboard($cordinates ,true)->RemoveBlockUserPosts()->RemoveSoldPost()->where([['posts.gender_id',$user->user_selected_gender],['posts.user_id','!=',$user->id],['posts.is_active',1],['posts.is_delete',0],])->whereRaw('FIND_IN_SET(?, posts.categories)', [$all['cat_id']])->distinct('posts.id');
                }else{
					 $parentCategory = category::with(['childCategories' => function ($q2) {
                        $q2->where('is_delete', 0)->where('is_active', 1);
                    }])->where('parent_id', 0)->where('id',$all['cat_id'])->where('is_delete', 0)->where('is_active', 1)->get();                
                    foreach ($parentCategory as $category) {
                        $catSubCatPost = posts::whereHas('user', function($q){
                    $q->where('is_active',1);
                })->RemoveSoldPost()->where([['posts.is_active',1],['posts.is_delete',0]]);

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
					$posts = Posts:: where([['posts.is_active',1],['posts.is_delete',0]])
    ->whereRaw('FIND_IN_SET(?, REPLACE(posts.categories, " ", ""))', [$all['cat_id']])
	->GetNearestPostDashboard($cordinates, true)
    ->RemoveSoldPost()
    ->distinct('posts.id');
				}
                /// category filter end

                    
				
				$brands = '';
					if(isset($all['brand_id'])){
						$brands = explode(',',$all['brand_id']);
					}
					if($all['gender_id']){
						$posts->where('posts.gender_id',$all['gender_id']);
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
						$posts->whereIn('posts.sizes',explode(',',$all['sizes']));
					}
					if(!empty($all['categories'])){
						$posts->whereIn('posts.categories',explode(',',$all['categories']));
                    }
					if(!empty($all['colors'])){
                        $posts->whereIn('posts.colors',explode(',',$all['colors']));
                    }
					
					if(!empty($all['price_from'])){
						$posts->where('posts.price','>=',$all['price_from']);
					}
					if(!empty($all['price_to'])){
						$posts->where('posts.price','<=',$all['price_to']);
					}
					if(!empty($all['seller'])){
						$posts->with('user')->whereHas('user',function($s) use($all) {
                                $s->where('role_id',$all['seller']);
                            });
					};



                    if(isset($all['sort_by']) && !empty($all['sort_by'])){
                        switch($all['sort_by']){
                            case 'date':
                                $posts->orderby('posts.id',$all['sort_value']);
                                break;
                            case 'price':
                                $posts->orderby('posts.price',$all['sort_value']);
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
                                $userSizes = $authUser->userSizes()->pluck('size_id')->toArray();                        
                                $posts->whereIn('posts.sizes',$userSizes);
                            break;                                                                                          
                            default:
                                $posts->orderby('posts.id','desc');
                                break;
                        }
                    }else{
                       $posts = $posts->orderby('posts.id','desc');                        
                    }

                    $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));

                    $res['currentPage'] = $posts->currentPage();
                    $res['hasMorePages'] = $posts->hasMorePages();
                    $res['posts'] = posts::getAPIPostListData($posts->items());
                    $pagetype = 'CATEGORYPOST';
                    break;

                case 6:
					if($user){
                    $posts = User::where([['id','!=',$user->id],['role_id',8],['is_active',1],['is_delete',0]])->orderby('name','asc')->paginate(config('constants.API_PER_PAGE_RECORDS'));
					}else{
						$posts = User::where([['role_id',8],['is_active',1],['is_delete',0]])->orderby('name','asc')->paginate(config('constants.API_PER_PAGE_RECORDS'));
					}
                    $res['currentPage'] = $posts->currentPage();
                    $res['hasMorePages'] = $posts->hasMorePages();
                    $res['posts'] = User::getAPIStoreListData($posts->items());
                    $pagetype = 'LOCALSTORE';
                    break;

                case 7:

                /// category filter
                $groupedCategories = [];
                $gropItem          = [];
                $auth_user = Auth::user();            
                if (isset($all['sort_by']) && $all['sort_by'] == 'category' && $auth_user) {            
                    $parentCategory = category::with(['childCategories' => function ($q2) {
                        $q2->where('is_delete', 0)->where('is_active', 1);
                    }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();                
                    foreach ($parentCategory as $category) {
                        $catSubCatPost = posts::whereHas('user', function($q){
                                $q->where('is_active',1);
                            })->GetNearestPostDashboard($cordinates ,true)->PrivateAccountPost($user)->RemoveBlockUserPosts()->RemoveSoldPost()->where([['posts.gender_id',$user->user_selected_gender],['posts.user_id','!=',$user->id],['posts.is_active',1],['posts.is_delete',0],['posts.price_type',3],['posts.price','==',0]]);

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
					 $posts = posts::whereHas('user', function($q){
                $q->where('is_active',1);
                })->GetNearestPostDashboard($cordinates ,true)->PrivateAccountPost($user)->RemoveBlockUserPosts()->RemoveSoldPost()->where([['posts.gender_id',$user->user_selected_gender],['posts.user_id','!=',$user->id],['posts.is_active',1],['posts.is_delete',0],['posts.price_type',3],['posts.price','==',0]]);
                }else{
					 $parentCategory = category::with(['childCategories' => function ($q2) {
                        $q2->where('is_delete', 0)->where('is_active', 1);
                    }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();                
                    foreach ($parentCategory as $category) {
                        $catSubCatPost = posts::whereHas('user', function($q){
                                $q->where('is_active',1);
                            })->RemoveSoldPost()->where([['posts.is_active',1],['posts.is_delete',0],['posts.price_type',3],['posts.price','==',0]]);

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
					 $posts = posts::whereHas('user', function($q){
                $q->where('is_active',1);
                })->GetNearestPostDashboard($cordinates ,true)->RemoveSoldPost()->where([['posts.is_active',1],['posts.is_delete',0],['posts.price_type',3],['posts.price','==',0]]);
					
				}
                /// category filter end

              
				
				
				$brands = '';
					if(isset($all['brand_id'])){
						$brands = explode(',',$all['brand_id']);
					}
					if($all['gender_id']){
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
						$posts->whereIn('posts.sizes',explode(',',$all['sizes']));
					}
					if(!empty($all['categories'])){
						$posts->whereIn('posts.categories',explode(',',$all['categories']));
                    }
					if(!empty($all['colors'])){
                        $posts->whereIn('posts.colors',explode(',',$all['colors']));
                    }
					
					if(!empty($all['price_from'])){
						$posts->where('posts.price','>=',$all['price_from']);
					}
					if(!empty($all['price_to'])){
						$posts->where('posts.price','<=',$all['price_to']);
					}
					if(!empty($all['seller'])){
						$posts->with('user')->whereHas('user',function($s) use($all) {
                                $s->where('role_id',$all['seller']);
                            });
					};



                    if(isset($all['sort_by']) && !empty($all['sort_by'])){
                        switch($all['sort_by']){
                            case 'date':
                                $posts->orderby('posts.id',$all['sort_value']);
                                break;
                            case 'price':
                                $posts->orderby('posts.price',$all['sort_value']);
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
                            default:
                                $posts->orderby('distance','asc');
                                break;
                        }
                    }else{
                       $posts = $posts->orderby('posts.id','desc');                        
                    }

                    $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));

                    $res['currentPage'] = $posts->currentPage();
                    $res['hasMorePages'] = $posts->hasMorePages();
                    $res['posts'] = posts::getAPIPostListData($posts->items());
                    $pagetype = 'UNDER50';
                    break;

                case 8:

                /// category filter
                $groupedCategories = [];
                $gropItem          = [];
                $auth_user = Auth::user();    
				if($auth_user){
                if (isset($all['sort_by']) && $all['sort_by'] == 'category') {            
                    $parentCategory = category::with(['childCategories' => function ($q2) {
                        $q2->where('is_delete', 0)->where('is_active', 1);
                    }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();                
                    foreach ($parentCategory as $category) {
                        $catSubCatPost = posts::whereHas('user', function($q){
                                $q->where('is_active',1);
                            })->GetNearestPostDashboard($cordinates ,true)->PrivateAccountPost($user)->RemoveBlockUserPosts()->RemoveSoldPost()->where([['posts.gender_id',$user->user_selected_gender],['posts.user_id','!=',$user->id],['posts.is_active',1],['posts.is_delete',0]]);
                        
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
                  $posts = posts::whereHas('user', function($q){
                    $q->where('is_active',1);
                })->RemoveSoldPost()->where([['posts.is_active',1],['posts.is_delete',0]])->whereRaw("FIND_IN_SET(?, posts.categories)", [$all['cat_id']]);
				}else{
					if (isset($all['sort_by']) && $all['sort_by'] == 'category') {            
                    $parentCategory = category::with(['childCategories' => function ($q2) {
                        $q2->where('is_delete', 0)->where('is_active', 1);
                    }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();                
                    foreach ($parentCategory as $category) {
                        $catSubCatPost = posts::whereHas('user', function($q){
                                $q->where('is_active',1);
                            })->RemoveSoldPost()->where([['posts.is_active',1],['posts.is_delete',0]]);
                        
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
                    $posts = posts::whereHas('user', function($q){
                    $q->where('is_active',1);
                })->RemoveSoldPost()->where([['posts.is_active',1],['posts.is_delete',0]])->whereRaw("FIND_IN_SET(?, posts.categories)", [$all['cat_id']]);
				}
				
				$brands = '';
					if(isset($all['brand_id'])){
						$brands = explode(',',$all['brand_id']);
					}
					if($all['gender_id']){
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
						$posts->whereIn('posts.sizes',explode(',',$all['sizes']));
					}
					if(!empty($all['categories'])){
						$posts->whereIn('posts.categories',explode(',',$all['categories']));
                    }
					if(!empty($all['colors'])){
                        $posts->whereIn('posts.colors',explode(',',$all['colors']));
                    }
					
					if(!empty($all['price_from'])){
						$posts->where('posts.price','>=',$all['price_from']);
					}
					if(!empty($all['price_to'])){
						$posts->where('posts.price','<=',$all['price_to']);
					}
					if(!empty($all['seller'])){
						$posts->with('user')->whereHas('user',function($s) use($all) {
                                $s->where('role_id',$all['seller']);
                            });
					};



                    if(isset($all['sort_by']) && !empty($all['sort_by'])){
                        switch($all['sort_by']){
                            case 'date':
                                $posts->orderby('posts.id',$all['sort_value']);
                                break;
                            case 'price':
                                $posts->orderby('posts.price',$all['sort_value']);
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
                                $userSizes = $authUser->userSizes()->pluck('size_id')->toArray();
                                $posts->whereIn('posts.sizes',$userSizes);
                            break;                                                                                          
                            default:
                                $posts->orderby('distance','asc');
                                break;
                        }
                    }else{
                       $posts = $posts->orderby('posts.id','desc');                        
                    }
                    
                    $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
                    
                    $res['currentPage'] = $posts->currentPage();
                    $res['hasMorePages'] = $posts->hasMorePages();
                    $res['posts'] = posts::getAPIPostListData($posts->items());
                    $pagetype = 'ADSNEARBY';
                    break;
                
                case 9:
                    
                    // $posts = User::GetNearestUser($user,false)->getPopularBrands($user)->where([['users.is_active',1],['users.is_delete',0],['users.id','!=',$user->id]])->where('users.role_id',3);
                    
                    // if(isset($all['sort_by']) && !empty($all['sort_by'])){
                    //     switch($all['sort_by']){
                    //         case 'date':
                    //             $posts->orderby('users.created_at',$all['sort_value']);
                    //             break;
                    //         case 'distance':
                    //             $posts->orderby('distance',$all['sort_value']);
                    //             break;
                    //         default:
                    //             $posts->orderby('distance','asc');
                    //             break;
                    //     }
                    // }else{
                    //     $posts->orderby('distance','asc');
                    // }
                    
                    // $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
                    
                    // $res['currentPage'] = $posts->currentPage();
                    // $res['hasMorePages'] = $posts->hasMorePages();
                    // $res['posts'] = User::getAPIStoreListData($posts->items());
                    // $pagetype = 'POPULARBRANDS';
                    // break;
                    
                    //** update start by mahesh
					$auth_user = Auth::user();            

					if($auth_user){
                    $brand_posts = brand::with(['posts' => function($q2) use($user){
                    $q2->GetNearestPostDashboard($cordinates ,true)->PrivateAccountPost($user)->where([['posts.gender_id',$user->user_selected_gender],['posts.is_active',1],['posts.is_delete',0],['is_sold',0],['user_id','!=',Auth::user()->id]]);
                        }])->where([['is_active',1],['is_delete',0]])->get()->sortByDesc(function($brand_posts)
                        {
                            return $brand_posts->posts->count();
                        });
                            $brandId = [];
                            foreach($brand_posts as $brand){
                                if($brand->posts->count() > 0){
                                    array_push($brandId, $brand->id);
                                }
                            }
                    $ids_ordered = implode(',', $brandId);
                    $posts =brand::with(['posts' => function($q) use($user){
                        $q->whereHas('user', function($q4){
                    $q4->where('is_active',1);
                })->PrivateAccountPost($user)->GetNearestPostDashboard($cordinates ,true)->GetNearestPost($user,true)->where([['posts.gender_id',$user->user_selected_gender],['posts.is_active',1],['posts.is_delete',0],['is_sold',0],['user_id','!=',Auth::user()->id]]);
                    }])->whereIn('id',$brandId)->orderByRaw("FIELD(id, $ids_ordered)")->withCount(['posts as posts_count' => function ($q) {
    $q->whereHas('user', function ($q4) {
        $q4->where('is_active', 1);
    })->where([
        ['posts.is_active', 1],
        ['posts.is_delete', 0],
        ['is_sold', 0]
    ]);
}])
->orderByDesc('posts_count');
					}else{
						$brand_posts = brand::with(['posts' => function($q2) use($user){
                    $q2->where([['posts.is_active',1],['posts.is_delete',0],['is_sold',0]]);
                        }])->where([['is_active',1],['is_delete',0]])->get()->sortByDesc(function($brand_posts)
                        {
                            return $brand_posts->posts->count();
                        });
                            $brandId = [];
                            foreach($brand_posts as $brand){
                                if($brand->posts->count() > 0){
                                    array_push($brandId, $brand->id);
                                }
                            }
                    $ids_ordered = implode(',', $brandId);
                    $posts =brand::with(['posts' => function($q) use($user){
                        $q->whereHas('user', function($q4){
                    $q4->where('is_active',1);
                })->where([['posts.is_active',1],['posts.is_delete',0],['is_sold',0]]);
                    }])->orderByRaw("FIELD(id, $ids_ordered)")
					->withCount(['posts as posts_count' => function ($q) {
    $q->whereHas('user', function ($q4) {
        $q4->where('is_active', 1);
    })->where([
        ['posts.is_active', 1],
        ['posts.is_delete', 0],
        ['is_sold', 0]
    ]);
}])
->whereIn('id', $brandId)
->orderByDesc('posts_count');
					}
                    if(isset($all['sort_by']) && !empty($all['sort_by'])){
                        switch($all['sort_by']){
                            case 'date':
                                $posts->orderby('brands.id',$all['sort_value']);
                                break;
                            case 'distance':
                                $posts->orderby('posts_count',$all['sort_value']);
                                break;
                            default:
                            //    $posts->orderby('posts_count','desc');
                                break;
                        }
                    }else{
                       $posts = $posts->orderby('id','desc');                        
                    
                           //     $posts->orderby('posts_count','desc');                                            
                        // $posts->get()->sortByDesc(function($posts)
                        // {
                        //     return $posts->posts->count();
                        // });
                       // $posts->orderby('posts_count','desc');
                    }
                        // $posts->get()->sortByDesc(function($posts)
                        // {
                        //     return $posts->posts->count();
                        // });    
                        
                    //$posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
                    //$posts = $posts->get();
                    // dd($posts->items);
                    // $res['currentPage'] = $posts->currentPage();
                    // $res['hasMorePages'] = $posts->hasMorePages();
                    $res['currentPage'] = 1;
                    $res['hasMorePages'] = 1;                    
                    $res['posts'] = brand::getAPIStoreListData($posts->get());
                    $pagetype = 'POPULARBRANDS';
                    break;                    

            }

            if(!empty($pagetype)){
                $check = user_page_visit_logs::where('type',$pagetype)->orderby('id','desc')->first();
                if(!empty($check)){
                    user_page_visit_logs::find($check->id)->update(['views' => $check->views + 1]);
                }else{
                    user_page_visit_logs::create([
                        'type' => $pagetype,
                        'views' => 1
                    ]);
                }
            }
            return $this->SendResponse($res, 'Success', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
	
	public function getStoredetails($stores){
		$data = [];
		foreach($stores as $st){
			$store_data = [];
			$store_data['id']= $st->id;
			$store_data['name']= $st->name;
			
			$store_details  = store_details::where('user_id',$st->id)->first();
			$store_data['total_products']= $store_details->total_products;
			$store_data['profile_image']= $store_details->profile_image;
			$data[] = $store_data;
		}
		return $data;
	}

}
