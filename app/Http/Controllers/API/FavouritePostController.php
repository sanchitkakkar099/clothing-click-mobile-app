<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\User;
use App\posts;
use App\user_favourite_posts;
use App\category;

class FavouritePostController extends APIResponseController {

    public function index(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($request->all(), [
            'sort_by' => 'nullable|string|max:15',
            'sort_value' => 'nullable|required_with:sort_by|string',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{

            $user = Auth::user();

            $userFavouritePosts = $user->userFavouritePosts()->pluck('post_id')->toArray();

            $parentCategory = category::with(['childCategories' => function ($q2) {
                $q2->where('is_delete', 0)->where('is_active', 1);
            }])->where('parent_id', 0)->where('is_delete', 0)->where('is_active', 1)->get();

            $groupedCategories = [];
            $gropItem          = [];
            $auth_user = Auth::user();            
            if (isset($all['sort_by']) && $all['sort_by'] == 'category') {            
                foreach ($parentCategory as $category) {
                    $catSubCatPost = posts::GetNearestPost($user,false)
                    ->where([['posts.is_active',1],['posts.is_delete',0],['posts.is_sold',0]])
                    ->where(function($q)use($userFavouritePosts) {
                        if(!empty($userFavouritePosts)){
                            $q->whereIn('posts.id',$userFavouritePosts);
                        }else{
                            $q->where('posts.id',0);
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
            
            $posts = posts::RemoveBlockUserPosts()->GetNearestPost($user,false)
                    ->where([['posts.is_active',1],['posts.is_delete',0],['posts.is_sold',0]])
                    ->where(function($q)use($userFavouritePosts) {
                        if(!empty($userFavouritePosts)){
                            $q->whereIn('posts.id',$userFavouritePosts);
                        }else{
                            $q->where('posts.id',0);
                        }
                    });
					
					
					if(isset($all['gender_id'])){
						$posts->whereIn('posts.gender_id',explode(',',$all['gender_id']));
					}
					if(isset($all['brand_id'])){
						$brands = '';
						$brands = explode(',',$all['brand_id']);
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
                        // ->orderby('title','desc');
                        //$posts->whereRaw('CONCAT(",", posts.categories, ",") REGEXP ",('.$categories.'),"');
                        break;
                    case 'size':
                        $userSizes = $user->userSizes()->pluck('size_id')->toArray();                        
                        $posts->whereIn('sizes',$userSizes);
                        break;                        
                    default:
                        $posts->orderby('posts.id','desc');
                        break;
                }
            }
            
            $posts = $posts->paginate(config('constants.API_PER_PAGE_RECORDS'));

            $res['currentPage'] = $posts->currentPage();
            $res['hasMorePages'] = $posts->hasMorePages();
            $res['posts'] = posts::getAPIPostListData($posts->items());
            
            return $this->SendResponse($res, 'Success', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function manage(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($request->all(), [
            'post_id' => 'required|numeric',
            'action_type' => 'required|numeric|min:0|max:1',
        ]);
        
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->messages()->first(), 400);
        }

        try{

            $post = posts::where([['id',$all['post_id']],['is_active',1],['is_delete',0]])->first();
            if(empty($post)){
                return $this->SendResponse(null, 'Invalid post id', 400); 
            }

            if($all['action_type'] == 0){
                user_favourite_posts::where([['post_id',$all['post_id']],['user_id',Auth::user()->id]])->delete();
            }else{
                user_favourite_posts::create([
                    'user_id' => Auth::user()->id,
                    'post_id' => $all['post_id']
                ]);
            }
            
            return $this->SendResponse(null, 'Success', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

}
