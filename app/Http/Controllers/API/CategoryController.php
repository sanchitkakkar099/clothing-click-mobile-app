<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use DB;
use App\User;
use App\category;
use App\posts;
use App\gender;
use App\size;
use App\style;
use App\user_notifications;
use App\condition;
use App\color;
use App\location;
use App\price_types;

class CategoryController extends APIResponseController {

    public function index(Request $request)
    {
        try {

            $genders = gender::where('is_active',1)->get();

            $res = [];
            foreach($genders as $gender){
                array_push($res,[
                    'gender_id' => $gender->id,
                    'name' => $gender->name,
                    'categories' => $this->getGenderCategoriesbyGenderId($gender->id),
					'styles'=> style::select('id','name')->get()
					
                ]);
            }
			
			array_push($res,[
                    'gender_id' => '',
                    'name' => '',
                    'categories' => $this->getGenderCategoriesbyGenderId(''),
					'styles'=> style::select('id','name')->get()
					
                ]);
            
            return $this->SendResponse($res, 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse([], $e->getMessage(), 500);
        }
    }
	public function getAllCategories(Request $request)
    {
        try {
			$res = [];
			$res['categories'] = style::withCount('posts')
					->orderBy('posts_count', 'desc')
					->get();

			/* $user = user::select('user_selected_gender')->where('id',Auth::user()->id)->first();
			 $res = [];
			if(!empty($user->user_selected_gender)){
           
                array_push($res,[
                    'categories' => 
					category::select('id','name','photo')->where([['is_active',1],['is_delete',0],['gender_id', $user->user_selected_gender]])->get()
                ]);
            }else{
				array_push($res,[
                    'categories' => category::select('id','name','photo')->where([['is_active',1],['is_delete',0]])->get()
                ]);
			} */
            return $this->SendResponse($res, 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse([], $e->getMessage(), 500);
        }
    }

    public function getCategorySize(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($request->all(), [
            'category_ids' => 'nullable|string',
            'gender_id' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{

             $size= size::where(function($q) use($all) {
                if(isset($all['category_ids']) && !empty($all['category_ids'])){
                    $q->wherein('category_id',explode(',',$all['category_ids']));
                }
            })
            ->where([['is_active',1],['is_delete',0]]);
			if(isset($all['gender_id'])){
				$size= $size->wherein('gender_id',explode(',',$all['gender_id']));
			}
			$size= $size->select('id','name')->get();
			
			$res['size'] = $size;
            $categories_ids = null;
            if(isset($all['category_ids']) && !empty($all['category_ids'])){
                $categories_ids = $all['category_ids'];
            }            
            $res['group_size'] = $this->getGenderCategories($categories_ids);
            
            $res['conditions'] = condition::where([['is_active',1],['is_delete',0]])->select('id','name')->get();
             $getcolors = color::where([['is_active',1],['is_delete',0]])->select('id','name','colorcode','photo')->get();

        $cdata = [];
        foreach($getcolors as $color){
            if(isset($color->photo) && $color->photo != ''){
            array_push($cdata,[
                'id' => $color->id,
                'name' => $color->name,
                'colorcode' => $color->colorcode,
                'photo' => ResizeImage($color->photo,500,500)
            ]);
            }else{
              array_push($cdata,[
                'id' => $color->id,
                'name' => $color->name,
                'colorcode' => $color->colorcode,
                'photo' => ''
            ]);   
            }
        }
        $res['colors'] = $cdata;
          //   $res['colors']= color::where([['is_active',1],['is_delete',0]])->select('id','name','colorcode','photo')->get();
            $res['price_types'] = price_types::where([['is_active',1],['is_delete',0]])->select('id','name')->get();
            $res['locations'] = location::where([['appuser_id',Auth::user()->id],['is_active',1],['is_delete',0],['is_paid',1]])->select('id','address','postal_code','latitude','longitude','price','city','area','is_default','is_paid','is_active','expire_at')->get();
			$res['styles'] = style::select('id','name')->get();

            return $this->SendResponse($res, 'Success', 200);

        }catch(\Exception $e){
            return $this->SendResponse([], $e->getMessage(), 500);
        }
    }
    
    
    public function getCategorySizes()
    {

        try{

            
            $categories_ids = null;
                     
            $res['group_size'] = $this->getGenderCategories($categories_ids);
            
            $res['conditions'] = condition::where([['is_active',1],['is_delete',0]])->select('id','name')->get();
             $getcolors = color::where([['is_active',1],['is_delete',0]])->select('id','name','colorcode','photo')->get();

				$cdata = [];
				foreach($getcolors as $color){
					if(isset($color->photo) && $color->photo != ''){
					array_push($cdata,[
						'id' => $color->id,
						'name' => $color->name,
						'colorcode' => $color->colorcode,
						'photo' => ResizeImage($color->photo,500,500)
					]);
					}else{
					  array_push($cdata,[
						'id' => $color->id,
						'name' => $color->name,
						'colorcode' => $color->colorcode,
						'photo' => ''
					]);   
					}
				}
				$res['colors'] = $cdata;
          
			$res['styles'] = style::select('id','name')->get();
			return $this->SendResponse($res, 'Success', 200);

        }catch(\Exception $e){
            return $this->SendResponse([], $e->getMessage(), 500);
        }
    }
    
    
    public function getSize(Request $request, $categories_ids)
    {
        try {

            $sizeRes = [];
            $genders = gender::where('is_active',1)->get();
            foreach($genders as $gender){
                array_push($sizeRes,[
                    'gender_id' => $gender->id,
                    'gender_name' => $gender->name,
                    'categories' => $this->getGenderCategories($gender->id, $categories_ids)
                ]);
            }

            return $sizeRes;
        } catch (\Exception $e) {
            return null;
        }
    } 
	public function getCategoryStyles()
    {
        try {

            $sizeRes = [];
            $s['styles'] = style::all();
            return $this->SendResponse($s, 'Success', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        } 
    }

    public function getGenderCategories($categories_ids)
    {
        $data = [];
        $categories = category::where([['is_active',1],['is_delete',0]]);

        if($categories_ids !=null){
            $categories =  $categories->wherein('id',explode(',',$categories_ids));    
        }
        $categories = $categories->get();
        foreach($categories as $category){
            
            array_push($data,[
                'id' => $category->id,
                'name' => $category->name,
                'image' => ResizeImage($category->photo,50,50),
                'sizes' => $this->getGenderCategorySize($category->id)
            ]);

        }

        return $data;
    }

    public function getGenderCategorySize($catId)
    {
        $data = [];
        $sizes = size::where([['category_id',$catId],['is_active',1],['is_delete',0]])->get();
        
        foreach($sizes as $size){
            array_push($data,[
                'id' => $size->id,
                'name' => (string)$size->name
            ]);
        }

        return $data;
    }   
	
	public function getCategoryPosts(Request $request)
    {
		
		try{
			
			$all = $request->all();

            $validator = Validator::make($request->all(), [
                            'cat_id' => 'nullable|numeric',
							'latitude' => 'nullable|string',
							'longitude' => 'nullable|string',
							'gender' => 'nullable|string',
							'sizes' => 'nullable|string',
						]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
            //$us = User::find(Auth::user()->id)->first();
			//echo "<pre>";print_r(Auth::user()->id); exit;
			$res = [];
			
        $cordinates = array('latitude'=> (float) $all['latitude'],'longitude'=> (float)  $all['longitude']);

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }
		
            if($all){
				$cordinates = array('latitude'=> $all['latitude'],'longitude'=> $all['longitude']);

				if(isset($all['cat_id'])){
					
					$user =Auth::user();
					if($user){
						//echo "User authenticated";exit;
						$posts = posts::whereHas('user', function($q){
							$q->where('is_active',1);
							})->PrivateAccountPost($user)->GetNearestPostDashboard($cordinates ,true)->RemoveBlockUserPosts()
							->where([['posts.gender_id',$user->user_selected_gender],['posts.is_active',1],['posts.is_delete',0],['posts.is_sold', 0],['style_id', $all['cat_id']]])->distinct('posts.id')
							->paginate(config('constants.API_PER_PAGE_RECORDS'));
							$unread_notification = user_notifications::where([['user_id', Auth::user()->id],['is_read','0']])->count();
							//echo "<pre>"; print_r(posts::getAPIPostListData($posts->items()));exit;
							$res['currentPage'] = $posts->currentPage();
							$res['unread_notification'] = $unread_notification;
							$res['hasMorePages'] = $posts->hasMorePages();
							$res['posts'] = posts::getAPIPostListData($posts->items());
							return $this->SendResponse($res, 'Success', 200);
						}else{
							//echo "No User authenticated";exit;
							//echo "Don't have category";exit;
							$posts = posts::whereHas('user', function($q){
							$q->where('is_active',1);
						})->RemoveBlockUserPosts()->GetNearestPostDashboard($cordinates ,true)
						->where([['posts.is_active',1],['posts.is_delete',0],['posts.is_sold', 0],['style_id', $all['cat_id']]])->distinct('posts.id');
						 if(!empty($all['gender'])){
							$posts = $posts->where('posts.gender_id',$all['gender']);
						} 
						$posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
						$unread_notification = 0;
						//echo "<pre>"; print_r(posts::getAPIPostListData($posts->items()));exit;
							$postData['currentPage'] = $posts->currentPage();
						$postData['hasMorePages'] = $posts->hasMorePages();
						$postData['unread_notification'] = $unread_notification;
						$postData['posts'] = posts::getAPIPostListData($posts->items());
						return $this->SendResponse($postData, 'Success', 200);
					}
				}
				else{
				if(!isset($all['tab']) || empty($all['tab'])){
					$all['tab'] = 1;
				}
				$parentCategory = category::with(['childCategories' => function ($q2) {
					$q2->where('is_delete', 0)->where('is_active', 1);
				}])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();

				$groupedCategories = [];
				$gropItem          = [];
				//$auth_user = Auth::user();            
				if (isset($all['sort_by']) && $all['sort_by'] == 'category') {            
                foreach ($parentCategory as $category) {
                    $catSubCatPost = posts::where([['posts.is_active',1],['posts.is_delete',0]])
                    ->where(function($q) use($all) {
                            $q->where('posts.gender_id',$all['tab'])->where('posts.is_sold',0);
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
            

			$user =Auth::user();
			//$user =User::where('id',4380)->first();
			if($user){
				//echo "In if";exit;
			$posts = posts::whereHas('user', function($q){
                    $q->where('is_active',1);
                })->PrivateAccountPost($user)->GetNearestPostDashboard($cordinates ,true)
                    ->RemoveBlockUserPosts()
                    ->where([['posts.is_active',1],['posts.is_delete',0],['posts.is_sold', 0]]);
					if(($all['gender'])){
						$posts->where('posts.gender_id',$all['gender']);
					}else{
						$posts->where('posts.gender_id',$user->user_selected_gender);
					}
					$posts->distinct('posts.id');
			}else{
				//echo "In else";exit;
				$posts = posts::whereHas('user', function($q){
                    $q->where('is_active',1);
                })->GetNearestPostDashboard($cordinates ,true)->distinct('posts.id')
                    ->where([['posts.is_active',1],['posts.is_delete',0],['posts.is_sold', 0]]);
					if(!empty($all['gender'])){
						$posts->where('posts.gender_id',$all['gender']);
					}
			}

            if(isset($all['sort_by']) && !empty($all['sort_by'])){
                switch($all['sort_by']){
                    case 'date':
                        $posts->orderby('posts.created_at',$all['sort_value']);
                        break;
                    case 'price':
                        $posts->orderby('posts.price',$all['sort_value']);
                        break;
                    case 'category':
                        $gropItem = implode(",", $gropItem);
                        $posts->orderByRaw('FIELD(posts.id, ' . $gropItem . ') asc');
                        // $posts->with(['categories' => function ($query) use ($sortDirection) {
                        //     $query->orderBy('category_id', $sortDirection);
                        // }]);                                            
                        //$categories = explode(',',$all['sort_value']);
                        //$categories = implode('|',$categories);
                        //$posts->whereRaw('CONCAT(",", categories, ",") REGEXP ",('.$categories.'),"');
                        //$posts->orderby('search_category',$all['sort_value']);
                        break;
                    case 'size':
                        $authUser = \Auth::user();
						if($authUser->userSizes()){
                        $userSizes = $authUser->userSizes()->pluck('size_id')->toArray();                        
                        $posts->whereIn('posts.sizes',$userSizes);
						}
                        break;
						
                    default:
                        $posts->orderby('posts.created_at','desc');
                        break;
                }
				}else{
						$posts->orderby('posts.created_at','desc');               
				}
			  
					$posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
					if(Auth::user()){
						$unread_notification = user_notifications::where([['user_id', Auth::user()->id],['is_read','0']])->count();
					}else{
						$unread_notification = 0;
					}
					
					$postData['currentPage'] = $posts->currentPage();
					$postData['hasMorePages'] = $posts->hasMorePages();
					$postData['unread_notification'] = $unread_notification;
					$postData['posts'] = posts::getAPIPostListData($posts->items());
					return $this->SendResponse($postData, 'Success', 200);
				}
			}
			else{
				$posts = posts::paginate(config('constants.API_PER_PAGE_RECORDS'));
				//$posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
				if(Auth::user()){
					$unread_notification = user_notifications::where([['user_id', Auth::user()->id],['is_read','0']])->count();
					}else{
						$unread_notification = 0;
					}
				
                $res['currentPage'] = $posts->currentPage();
				$postData['unread_notification'] = $unread_notification;
                $res['hasMorePages'] = $posts->hasMorePages();
                $res['posts'] = posts::getAPIPostListData($posts->items());
			}
            return $this->SendResponse($res, 'Success', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        } 
    }   
	public function getGenderCategoriesbyGenderId($genderId = false)

    {

        $res = [];
		if($genderId){
			$categories =  category::where([['gender_id',$genderId],['parent_id',0],['is_active',1],['is_delete',0]])->orderby('id','asc')->get();
		}else{

			$categories = DB::select('select id, gender_id, parent_id, name, group_concat(distinct id order by id separator ",") combined_id, group_concat(distinct photo order by id separator ",") photo, group_concat(distinct icon order by id separator ", ") icon from categories where parent_id = 0 group BY name');
			//echo "<pre>";print_r($categories);exit;

			foreach($categories as $cat){
				$sub_categories = DB::select('select id, parent_id, name from categories where parent_id in('.$cat->id.') group BY name');
				$s_cat = [];
					array_push($s_cat,[
						'id' => (int)$cat->id,
						'name' => 'All '.$cat->name,
						'parent_id' => 0,
						'childCategories' => []
					]);
				
				foreach($sub_categories as $s_t){
					array_push($s_cat,[
						'id' => (int)$s_t->id,
						'name' => $s_t->name,
						'parent_id' => $s_t->parent_id,
						'childCategories' => []
					]);
				}
				array_push($res,[
					'category_id' => (int)$cat->id,
					'name' => $cat->name,
					'combined_id' => $cat->combined_id,
					'photo' => ResizeImage($cat->photo,100,100),
					'icon' => ResizeImage($cat->icon,500,500),
					'childCategories' => $s_cat
				]);
				
				
				
			}
			return $res;
		}
		
        foreach($categories as $category){
			 $children = $this->addAllToChildren($category); // existing childCategories

				// Prepend "All" to the children
				$allChild = [
					'id' => $category->id,
					'name' => 'All '.$category->name,
					'photo' => '', // or default image path
					'icon' => '',  // or default icon path
					'childCategories' => []
				];

				array_unshift($children, $allChild); // prepend "All" as first child
            array_push($res,[
                'category_id' => $category->id,
                'name' => $category->name,
                'photo' => ResizeImage($category->photo,100,100),
                'icon' => ResizeImage($category->icon,500,500),
                'childCategories' =>  $children


            ]);

        }

        return $res;

    }	
	private function addAllToChildren($category)
{
    $result = [];

    foreach ($category->childCategories as $child) {
        $childData = [
            'id' => $child->id,
            'name' => $child->name,
            'photo' => ResizeImage($child->photo, 100, 100),
            'icon' => ResizeImage($child->icon, 500, 500),
            'childCategories' => $this->addAllToChildren($child)
        ];
        array_push($result, $childData);
    }

    return $result;
}


}
