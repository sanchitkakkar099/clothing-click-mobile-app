<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\user_saved_searches;
use App\User;
use App\colors;
use App\posts;
use App\category;


class SaveSearchController extends APIResponseController {

    public function index(Request $request)
    {
        try {

            $searches = user_saved_searches::where([['user_id',Auth::user()->id],['is_active',1],['is_delete',0]])->select('id','name','matched_product_count')->paginate(config('constants.API_PER_PAGE_RECORDS'));
            $data = [];
            if($searches->count() > 0){
                foreach($searches->items() as $search){
                    array_push($data,[
                        'id' => $search->id,
                        'name' => $search->name,
						'count' => $search->matched_product_count
                    ]);
                }
            }

            $res['currentPage'] = $searches->currentPage();
            $res['hasMorePages'] = $searches->hasMorePages();
            $res['save_searches'] = $data;

            return $this->SendResponse($res, 'Success', 200);

        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function details(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'save_search_id' => 'required|numeric',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $search = user_saved_searches::where([['user_id',Auth::user()->id],['id',$all['save_search_id']]])->first();

            if(empty($search)){
                return $this->SendResponse(null, 'Invalid save search id', 400);
            }

            $search['sizes'] = $search->getSelectedSizes();
            if(isset($all['m_test'])){
            $search['categories'] = $search->getNewSelectedCategories();                       
            }else{
            $search['categories'] = $search->getSelectedCategories();                
            }

            $search['colors'] = $search->getSelectedColors();
            $search['gender_name'] = $search->gender->name ?? null;
            $search['condition_name'] = $search->condition->name ?? null;
            $search['brand_name'] = $search->brand->name ?? null;
            $search['price_type_name'] = $search->getPriceType->name ?? null;
            $search['seller_name'] = $search->getSeller->name ?? null;
            $search['style_name'] = $search->style_details->name ??null;

            unset($search['updated_at'],$search['brand'],$search['condition'],$search['gender'],$search['is_delete'],$search['is_active'],$search['getPriceType'],$search['getSeller']);
            
            return $this->SendResponse($search, 'Success', 200);

        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function create(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'is_mysize' => 'required|numeric|max:1',
                'gender_id' => 'nullable|numeric|min:1|max:2',
                //'gender_id' => 'nullable|required_if:is_mysize,0|numeric|min:1|max:2',
    //            'sizes' => 'nullable|string',                
                // 'sizes' => 'nullable|required_if:is_mysize,0|string',
                'colors' => 'nullable|string',
                'condition_id' => 'nullable|numeric',
                'distance' => 'nullable|numeric',
                'seller' => 'nullable|numeric',
                'brand_id' => 'nullable|numeric',
                'notification_item_counter' => 'nullable|numeric',
                'name' => 'required|string|max:255',
                'price_type' => 'nullable|numeric',
                'price_from' => 'nullable|numeric',
                'price_to' => 'nullable|numeric',                
                'style' => 'nullable|numeric',                
//                'price_from' => 'nullable|required_if:price_type,1|numeric',
  //              'price_to' => 'nullable|required_if:price_type,1|numeric',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $all['user_id'] = Auth::user()->id;
            $all['gender_id'] = (isset($all['gender_id']) && !empty($all['gender_id'])) ? $all['gender_id'] : Auth::user()->user_selected_gender;
            
            if(!isset($all['price_from']) && empty($all['price_from'])){
                $all['price_from'] = 0;
            }

            if(!isset($all['price_to']) && empty($all['price_to'])){
                $all['price_to'] = 0;
            }
			
			$brands = '';
			if(isset($all['brand_id'])){
				$brands = explode(',',$all['brand_id']);
			}
			$posts = posts::where([['posts.is_active',1],['posts.is_delete',0]])
					->whereIn('posts.gender_id',explode(',',$all['gender_id']));
					//->where('posts.title', 'LIKE', '%'.$all['name'].'%');
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
						$posts->where('posts.sizes',$all['sizes']);
					}
					if(isset($all['style'])){
						$posts->where('posts.style_id',$all['style']);
					}
					if(!empty($all['categories'])){
						if(str_contains($all['categories'], ',')){
							$categories = explode(',',$all['categories']);
						}else{
							$categories = $all['categories'];
						}
					
                        $posts->whereRaw('CONCAT(",", posts.categories, ",") REGEXP ",('.$categories.'),"');
                    }
					if(!empty($all['colors'])){
                        $colors = explode(',',$all['colors']);
                        $posts->whereRaw('CONCAT(",", posts.colors, ",") REGEXP ",('.$colors.'),"');
                    }
					if(!empty($all['price_type'])){
						$posts->where('posts.price_type',$all['price_type']);
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
							$posts->orderby('posts.created_at',$all['sort_value']);
							break;
						case 'price':
							$posts->orderby('posts.price',$all['sort_value']);
							break;
						case 'distance':
							$posts->orderby('distance',$all['sort_value']);
							break;
						default:
							$posts->orderby('distance','asc');
							break;
					}
				}

            $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
			$all['matched_product_count'] = $posts->total() ;
            $create = user_saved_searches::create($all);

            return $this->SendResponse(null, 'Success', 200);

        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function update(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'is_mysize' => 'required|numeric|max:1',
            //    'gender_id' => 'nullable|required_if:is_mysize,0|numeric|min:1|max:2',
//                'sizes' => 'nullable|required_if:is_mysize,0|string',            
                'gender_id' => 'nullable|numeric|min:1|max:2',
                'sizes' => 'nullable|string',
                'colors' => 'nullable|string',
                'condition_id' => 'nullable|numeric',
                'distance' => 'nullable|numeric',
                'seller' => 'nullable|numeric',
                'brand_id' => 'nullable|numeric',
                'notification_item_counter' => 'nullable|numeric',
                'name' => 'required|string|max:255',
                'price_type' => 'nullable|numeric',
                'price_from' => 'nullable|numeric',
                'price_to' => 'nullable|numeric',
                'style' => 'nullable|string',
                'save_search_id' => 'required|numeric'
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $check = user_saved_searches::where([['user_id',Auth::user()->id],['id',$all['save_search_id']]])->count();

            if(empty($check)){
                return $this->SendResponse(null, 'Invalid save search id', 400);
            }

            $all['user_id'] = Auth::user()->id;
            $all['gender_id'] = (isset($all['gender_id']) && !empty($all['gender_id'])) ? $all['gender_id'] : Auth::user()->user_selected_gender;
            

            
            $getSearch = user_saved_searches::find($all['save_search_id']);


            if((isset($all['is_mysize']) && $getSearch->is_mysize != $all['is_mysize']) || (isset($all['gender_id']) && $getSearch->gender_id != $all['gender_id']) || (isset($all['categories']) && $getSearch->categories != $all['categories']) || (isset($all['sizes']) && $getSearch->sizes != $all['sizes']) || (isset($all['colors']) && $getSearch->colors != $all['colors']) || (isset($all['condition_id']) && $getSearch->condition_id != $all['condition_id']) || (isset($all['distance']) && $getSearch->distance != $all['distance']) || (isset($all['seller']) && $getSearch->seller != $all['seller']) || (isset($all['brand_id']) && $getSearch->brand_id != $all['brand_id']) || (isset($all['notification_item_counter']) && $getSearch->notification_item_counter != $all['notification_item_counter']) || (isset($all['price_type']) && $getSearch->price_type != $all['price_type']) || (isset($all['price_from']) && $getSearch->price_from != $all['price_from']) || (isset($all['price_to']) && $getSearch->price_to != $all['price_to']) ){
                $all['matched_product_count'] = 0;
            }
			$matched_products  = $this->posts($request);
			$brands = '';
			if(isset($all['brand_id'])){
				$brands = explode(',',$all['brand_id']);
			}
			$posts = posts::where([['posts.is_active',1],['posts.is_delete',0]]);
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
						$posts->where('posts.sizes',$all['sizes']);
					}
					if(isset($all['style'])){
						$posts->where('posts.style_id',$all['style']);
					}
					if(!empty($all['categories'])){
                        
                        $posts->whereIn('posts.categories',explode(',',$all['categories']));
                    }
					if(!empty($all['colors'])){
                        $posts->whereIn('posts.colors',explode(',',$all['colors']));

                    }
					if(!empty($all['price_type'])){
						$posts->where('posts.price_type',$all['price_type']);
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
							$posts->orderby('posts.created_at',$all['sort_value']);
							break;
						case 'price':
							$posts->orderby('posts.price',$all['sort_value']);
							break;
						case 'distance':
							$posts->orderby('distance',$all['sort_value']);
							break;
						default:
							$posts->orderby('distance','asc');
							break;
					}
				}

                $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
                $res['total_posts'] = $posts->total();
				
			$all['matched_product_count'] = $posts->total() ;
            
            $create = user_saved_searches::find($all['save_search_id'])->update($all);
            return $this->SendResponse(null, 'Search updated successfully.', 200);

        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function delete(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'save_search_id' => 'required|numeric'
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $check = user_saved_searches::where([['user_id',Auth::user()->id],['id',$all['save_search_id']]])->first();

            if(empty($check)){
                return $this->SendResponse(null, 'Invalid save search id', 400);
            }

            $check->is_delete = 1;
            $check->update();
            
            return $this->SendResponse(null, 'Search deleted successfully.', 200);

        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function posts(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'save_search_id' => 'required|numeric',
                'sort_by' => 'nullable|string|max:15',
                'sort_value' => 'nullable|required_with:sort_by|string',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $check = user_saved_searches::where([['user_id',Auth::user()->id],['id',$all['save_search_id']]])->first();

            if(empty($check)){
                return $this->SendResponse(null, 'Invalid save search id', 400);
            }

            $isDistanceCodition = true;
            $distance = 50;
            if(!empty($check->distance)){
                $isDistanceCodition = true;
                $distance = $check->distance;
            }

            $sizes = [];
            if($check->is_mysize == 1){
                $sizes = Auth::user()->userSizes()->where('is_active',1)->pluck('size_id')->toArray();
            }else{
                if(!empty($check->sizes)){
                    $sizes = explode(',',$check->sizes);
                }
            }

            $user = Auth::user();
            $finalArray = [];

            //// category filter are
            $parentCategory = category::with(['childCategories' => function ($q2) {
                $q2->where('is_delete', 0)->where('is_active', 1);
            }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();

            $groupedCategories = [];
            $gropItem          = [];
            $auth_user = Auth::user();            
            if (isset($all['sort_by']) && $all['sort_by'] == 'category') {            
                foreach ($parentCategory as $category) {
                    $catSubCatPost = posts::GetNearestPost($user,$isDistanceCodition,$distance)
                    ->RemoveBlockUserPosts()
                    ->RemoveSoldPost()
                    ->where([['posts.is_active',1],['posts.is_delete',0]])
                    ->where('posts.gender_id',$check->gender_id)
                    ->where('posts.user_id','!=',Auth::user()->id)
                    ->where(function($q) use($check,$finalArray,$sizes) {

                        if(!empty($check->brand_id)){
                            $q->where('posts.brand_id',$check->brand_id);
                        }

                        if(!empty($check->condition_id)){
                            $q->where('posts.condition_id',$check->condition_id);
                        }

                        if(!empty($check->categories) && count($finalArray) > 0){
                            // $categories = explode(',',$check->categories);
                            $categories = implode('|',$finalArray);
                            $q->whereRaw('CONCAT(",", posts.categories, ",") REGEXP ",('.$categories.'),"');
                        }

                        if(!empty($check->colors)){
                            $colors = explode(',',$check->colors);
                            $colors = implode('|',$colors);
                            $q->whereRaw('CONCAT(",", posts.colors, ",") REGEXP ",('.$colors.'),"');
                        }

                        if(!empty($check->price_type)){
                            $q->where('posts.price_type',$check->price_type);
                        }

                        if(!empty($check->price_from)){
                            $q->where('posts.price','>=',$check->price_from);
                        }

                        if(!empty($check->price_to)){
                            $q->where('posts.price','<=',$check->price_to);
                        }

                        if(count($sizes) > 0){
                            $sizes = implode('|',$sizes);
                            $q->whereRaw('CONCAT(",", posts.sizes, ",") REGEXP ",('.$sizes.'),"');
                        }

                        if(!empty($check->seller)){
                            $q->with('user')->whereHas('user',function($s) use($check) {
                                $s->where('role_id',$check->seller);
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
            }
            /// category filter ended
            if(!empty($check->categories)){
                 $categories = explode(',',$check->categories);
                $finalArray = category::whereDoesntHave('childCategories')->wherein('id',$categories)->where([['is_active',1],['is_delete',0]])->pluck('id')->toArray();
                
                // $categories = explode(',',$check->categories);
                // $childIds = [];
                // $parentIds = [];
                // foreach($categories as $c => $cat){
                //     $getChild = category::has('childCategories')->with('childCategories')->find($cat);
                //     if($getChild){
                //         $getCHilds = $getChild->childCategories()->pluck('id')->toArray();
                //         foreach($categories as $findval){
                //             if(in_array($findval, $getCHilds)){
                //                 array_push($childIds, $findval);
                //             }                            
                //         }                                
                //     }else{
                //         array_push($parentIds, $cat);
                //     }
                // }
                // $finalArray = array_unique(array_merge($childIds, $parentIds));
            }
            
			$brands = '';
			if(isset($all['brand_id'])){
				$brands = explode(',',$all['brand_id']);
			}
			$posts = posts::where([['posts.is_active',1],['posts.is_delete',0]]);
					if($check->gender_id){
						$posts->whereIn('posts.gender_id',explode(',',$check->gender_id));
					}
					if($check->brand_id){
						$posts->whereIn('posts.brand_id',explode(',',$check->brand_id));
					}
					if(isset($check->condition_id)){
						$posts->where('posts.condition_id',$check->condition_id);
					}
					if(isset($check->style)){
						$posts->whereIn('posts.style_id',explode(',',$check->style));
					}
					if(isset($check->price_type)){
						$posts->where('posts.price_type',$check->price_type);
					}
					if(isset($check->sizes)){
						$posts->where('posts.sizes',$check->sizes);
					}

					if(!empty($check->categories)){
                        
                        $posts->whereIn('posts.categories',explode(',',$check->categories));
                    }
					if(!empty($check->colors)){
                        $posts->whereIn('posts.colors',explode(',',$check->colors));

                    }
					if(!empty($check->price_type)){
						$posts->where('posts.price_type',$check->price_type);
					}
					if(!empty($check->price_from)){
						$posts->where('posts.price','>=',$check->price_from);
					}
					if(!empty($check->price_to)){
						$posts->where('posts.price','<=',$check->price_to);
					}
					

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
						default:
							$posts->orderby('distance','asc');
							break;
					}
				}

                $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
                $res['total_posts'] = $posts->total();
				
			
			
			
            /* $posts = posts::GetNearestPost($user,$isDistanceCodition,$distance)
                    ->RemoveBlockUserPosts()
                    ->RemoveSoldPost()
                    ->where([['posts.is_active',1],['posts.is_delete',0]])
                    ->where('posts.gender_id',$check->gender_id)
                    ->where(function($q) use($check,$finalArray,$sizes) {

                        if(!empty($check->brand_id)){
                            $q->where('posts.brand_id',$check->brand_id);
                        }

                        if(!empty($check->condition_id)){
                            $q->where('posts.condition_id',$check->condition_id);
                        }

                        if(!empty($check->categories) && count($finalArray) > 0){
                            // $categories = explode(',',$check->categories);
                            $categories = implode('|',$finalArray);
                            $q->whereRaw('CONCAT(",", posts.categories, ",") REGEXP ",('.$categories.'),"');
                        }

                        if(!empty($check->colors)){
                            $colors = explode(',',$check->colors);
                            $colors = implode('|',$colors);
                            $q->whereRaw('CONCAT(",", posts.colors, ",") REGEXP ",('.$colors.'),"');
                        }

                        if(!empty($check->price_type)){
                            $q->where('posts.price_type',$check->price_type);
                        }

                        if(!empty($check->price_from)){
                            $q->where('posts.price','>=',$check->price_from);
                        }

                        if(!empty($check->price_to)){
                            $q->where('posts.price','<=',$check->price_to);
                        }

                        if(count($sizes) > 0){
                            $sizes = implode('|',$sizes);
                            $q->whereRaw('CONCAT(",", posts.sizes, ",") REGEXP ",('.$sizes.'),"');
                        }

                        if(!empty($check->seller)){
                            $q->with('user')->whereHas('user',function($s) use($check) {
                                $s->where('role_id',$check->seller);
                            });
                        }

                    });
					 */

           /*  if(isset($all['sort_by']) && !empty($all['sort_by'])){
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
                    case 'size':
                        $userSizes = $user->userSizes()->pluck('size_id')->toArray();                        
                        $posts->whereIn('posts.sizes',$userSizes);
                        $posts->orderby('distance','asc');                        
                        break;     
                    case 'category':
                        $gropItem = implode(",", $gropItem);
                        $posts->orderByRaw('FIELD(posts.id, ' . $gropItem . ') asc');                                                                   
                        break;
                    default:
                        $posts->orderby('distance','asc');
                        break;
                }
            }
             */
            //$posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));
            $res['currentPage'] = $posts->currentPage();
            $res['hasMorePages'] = $posts->hasMorePages();
            $res['posts'] = posts::getAPIPostListData($posts->items());
            $res['total'] = $posts->total();
            
            return $this->SendResponse($res, 'Success', 200);

        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

}
