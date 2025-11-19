<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\User;
use App\posts;
use App\user_search_logs;
use App\brand;
use App\category;

class SearchController extends APIResponseController {

    public function index(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'search_type' => 'required|numeric|min:0|max:1',
                'search_value' => 'required|string|max:255'
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            if($all['search_type'] == 0){
                $res = $this->getPostSearch($all);
            }else{
                $res = $this->getUserSearch($all);
            }
            
            return $this->SendResponse($res, 'Success', 200);

        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    private function getUserSearch($all)
    {
        $searchValue = removeSpecialCharacter($all['search_value']);
        $searchValue = explode(' ',$searchValue);
        $searchValue = array_filter($searchValue);
		if(Auth::user()){
        $users = User::RemoveBlockUser()->GetNearestUser(Auth::user())
                ->where([['users.is_active',1],['users.is_delete',0],['users.id','!=',Auth::user()->id]])
                ->where(function($q) use($all,$searchValue){
                    if(!empty($searchValue)){
                        foreach($searchValue as $key => $searchString){
                            if($key == 0){
                                $q->where('users.name','LIKE', $searchString.'%');
                            }else{
                                $q->orwhere('users.name','LIKE', $searchString.'%');
                            }
                            $q->orwhere('users.username','LIKE', $searchString.'%');
                        }
                    }
                })
                ->orderby('id','asc')
                ->paginate(config('constants.API_PER_PAGE_RECORDS'));
		}else{
			$users = User::where([['users.is_active',1],['users.is_delete',0]])
                ->where(function($q) use($all,$searchValue){
                    if(!empty($searchValue)){
                        foreach($searchValue as $key => $searchString){
                            if($key == 0){
                                $q->where('users.name','LIKE', $searchString.'%');
                            }else{
                                $q->orwhere('users.name','LIKE', $searchString.'%');
                            }
                            $q->orwhere('users.username','LIKE', $searchString.'%');
                        }
                    }
                })
                ->orderby('id','asc')
                ->paginate(config('constants.API_PER_PAGE_RECORDS'));
		}
        $data = [];
        foreach($users as $user){
			$user = User::find($user->id);
			$blockedUsers1 = $user->listUserBlocked()->pluck('user_id')->toArray();
			$blockedUsers2 = $user->getBlockedUsers()->pluck('blocked_user_id')->toArray();
			$blockedUsers = array_merge($blockedUsers1,$blockedUsers2); 
			$totalFollowers = $user->followers()->whereNotIn('follower_user_id', $blockedUsers)->has('user')->where([['is_accepted',1],['is_active',1],['is_delete',0]])->count();
			$totalPosts = $user->posts()->where([['is_active',1],['is_delete',0],['is_sold',0]])->count();
            array_push($data,[
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'followers' => $totalFollowers,
                'userimage' => ($user->photo !=null)?ResizeImage($user->photo,500,500):null,
				'total_posts'=> $totalPosts
            ]);
        }

        $res['currentPage'] = $users->currentPage();
        $res['hasMorePages'] = $users->hasMorePages();
        $res['data'] = $data;

        return $res;
    }

    private function getPostSearch($all)
    {
        $searchValue = removeSpecialCharacter($all['search_value']);
        $searchValue = explode(' ',$searchValue);
        $searchValue = array_filter($searchValue);
        $searchText = implode(" ",$searchValue);

        $getBrands = brand::where([['is_active',1],['is_delete',0]])
                    ->where(function($q)use($all,$searchValue, $searchText){
                        if(!empty($searchValue)){
                            $q->whereRaw("MATCH(name) AGAINST(?)", array($searchText));
                            // foreach($searchValue as $key => $searchString){
                            //     if($key == 0){
                            //         $q->where('name','LIKE','%'.$searchString.'%');
                            //     }else{
                            //         $q->orwhere('name','LIKE','%'.$searchString.'%');
                            //     }
                            // }
                        }
                    })->select('id')->pluck('id')->toArray();
        
        $getCategories = category::where([['is_active',1],['is_delete',0]])
                    ->where(function($q)use($all,$searchValue, $searchText){

                        if(!empty($searchValue)){
                        $q->whereRaw("MATCH(name) AGAINST(?)", array($searchText));                            
                            // foreach($searchValue as $key => $searchString){
                            //     if($key == 0){
                            //         $q->where('name','LIKE','%'.$searchString.'%');
                            //     }else{
                            //         $q->orwhere('name','LIKE','%'.$searchString.'%');
                            //     }
                            // }
                        }
                    })->select('id')->pluck('id')->toArray();
                    
             // dd($getCategories);       
        $user = Auth::user();
		if($user){
        $posts = posts::whereHas('user', function($q4){
                    $q4->where('is_active',1);
                })->RemoveBlockUserPosts()->PrivateAccountPost($user)->GetNearestPost($user, true)
                ->where([['posts.user_id','!=',Auth::user()->id],['posts.is_active',1],['posts.is_delete',0],['posts.is_sold',0]]);                
               
        $ids = $posts->pluck('id')->toArray();   
		}else{
			$posts = posts::whereHas('user', function($q4){
                    $q4->where('is_active',1);
                })->where([['posts.is_active',1],['posts.is_delete',0],['posts.is_sold',0]]);                
               
        $ids = $posts->pluck('id')->toArray(); 
		}
        if(isset($all['m__test'])){
//            dd($ids);
        }
        $newpost = \DB::table('posts')->select('posts.*','brands.name as brand_name','price_types.name as price_type_name', \DB::raw("MATCH (posts.title,posts.description,posts.search_gender,posts.search_brand,posts.search_category,posts.search_size,posts.search_condition,posts.search_color) AGAINST ('".$searchText."' IN NATURAL LANGUAGE MODE) AS score"))
        ->join('brands','posts.brand_id','brands.id')
        ->join('price_types','posts.price_type','price_types.id')
        ->whereIn('posts.id',$ids);
		
		
					if(isset($all['gender_id'])){
						$newpost->whereIn('posts.gender_id',explode(',',$all['gender_id']));
					}
					if(isset($brands)){
						$newpost->whereIn('posts.brand_id',explode(',',$all['brand_id']));
					}
					if(isset($all['condition_id'])){
						$newpost->where('posts.condition_id',$all['condition_id']);
					}
					if(isset($all['style'])){
						$newpost->whereIn('posts.style_id',explode(',',$all['style']));
					}
					if(isset($all['price_type'])){
						$newpost->where('posts.price_type',$all['price_type']);
					}
					if(isset($all['sizes'])){
						$newpost->whereIn('posts.sizes',explode(',',$all['sizes']));
					}
					if(!empty($all['categories'])){
						$newpost->whereIn('posts.categories',explode(',',$all['categories']));
                    }
					if(!empty($all['colors'])){
                        $newpost->whereIn('posts.colors',explode(',',$all['colors']));
                    }
					
					if(!empty($all['price_from'])){
						$newpost->where('posts.price','>=',$all['price_from']);
					}
					if(!empty($all['price_to'])){
						$newpost->where('posts.price','<=',$all['price_to']);
					}
					if(!empty($all['seller'])){
						$newpost->with('user')->whereHas('user',function($s) use($all) {
                                $s->where('role_id',$all['seller']);
                            });
					}; 
		
		$newpost->having('score', '>',0)->orderBy('score','desc');
        $newpost =$newpost->paginate(config('constants.API_PER_PAGE_RECORDS'));

        $filterPost = $newpost->groupBy('score');

    
        $data = [];

        if($filterPost->first() !=null){
            foreach($filterPost as $group_posts){            
                foreach($group_posts as $post){
                    array_push($data,[
                        'id' => intval($post->id),
                        'title' => $post->title,
                        'image' => posts::getImages($post->images,true),
                        'brand_name' => ($post->brand_name)?$post->brand_name:null,
                        'price_type_name' => ($post->price_type_name)?$post->price_type_name:null,
                        'is_sold' => $post->is_sold,
                        'price' => $post->price,
                        'price_type' => $post->price_type,                
                        'sale_price' => $post->sale_price,
                        'created_at' => date('Y-m-d H:i:s',strtotime($post->created_at)),                    
                        'size_name' => implode(',',posts::getSize($post->sizes,true)),                    
                    ]);
                }       
            }
        }

    
        $res['currentPage'] = $newpost->currentPage();
        $res['hasMorePages'] = $newpost->hasMorePages();
        $res['data'] = $data;
//        $res['data'] = posts::getAPIPostListData($newpost->items());

        return $res;
    }

}
