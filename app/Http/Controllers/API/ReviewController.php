<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\reviews;
use App\user_reviews;
use App\posts;
use App\User;
use App\user_notifications;
use App\images;


class ReviewController extends APIResponseController {

    public function index(Request $request)
    {
        try{
            
            $all = $request->all();

            $validator = Validator::make($all, [
                'user_id' => 'required|numeric',
                'type' => 'required|numeric',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $reviews = reviews::has('user')->has('reviewBy')->with('post')
                        ->where('user_id',$all['user_id'])
                        ->whereHas('post',function($q) use($all) {
                            if($all['type'] == 1){
                                $q->where('user_id','!=',$all['user_id']);
                            }else{
                                $q->where('user_id',$all['user_id']);
                            }
                        })->orderBy('created_at', 'desc')
                        ->paginate(config('constants.API_PER_PAGE_RECORDS'));
            
            $data = [];
			//echo "<pre>";print_r($reviews);exit;
            foreach($reviews as $review){
				$post = posts::find($review->post_id);
                array_push($data,[
                    'id' => $review->id,
                    'review_by' => $review->review_by,
                    'review_by_name' => ($review->reviewBy)?$review->reviewBy->name:null,
                    'review_by_photo' => ($review->reviewBy && $review->reviewBy->photo !=null)?ResizeImage($review->reviewBy->photo,500,500):'',
                    'rating' => $review->rating,
					'photo' => posts::getImages($post->images,true),

                    'review' => $review->review ?? null,
                    'created_at' => date('Y-m-d H:i:s',strtotime($review->created_at))
                ]);
            }

            $res['total_buyer_review'] = reviews::has('user')->has('reviewBy')->with('post')->where('user_id',$all['user_id'])
                                        ->whereHas('post',function($q) use($all) {
                                            $q->where('user_id','!=',$all['user_id']);
                                        })->count();

            $res['total_seller_review'] = reviews::has('user')->has('reviewBy')->with('post')->where('user_id',$all['user_id'])
                                        ->whereHas('post',function($q) use($all) {
                                            $q->where('user_id',$all['user_id']);
                                        })->count();

            $res['currentPage'] = $reviews->currentPage();
            $res['hasMorePages'] = $reviews->hasMorePages();
            $res['reviews'] = $data;

            return $this->SendResponse($res, 'Success', 200);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function create(Request $request)
    {
        try{
            $all = $request->all();

            $validator = Validator::make($all, [
                'user_id' => 'required|numeric',
                'post_id' => 'required|numeric',
                'rating' => 'required|numeric|min:0|max:5',
                'review' => 'nullable|string',
                'type' => 'required|numeric'
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            if($all['type'] == 2){

                posts::find($all['post_id'])->update(['sold_user_id' => $all['user_id']]);
                
                user_notifications::create([
                    'type' => 'RATE_SELLER',
                    'user_id' => $all['user_id'],
                    'resource_id' => Auth::id(),
                    'resource_id_two' => $all['post_id'],
                    'title' => 'Rate Seller'
                ]);
                $_getUser = User::with('notificationSetting')->find($all['user_id']);
                if($_getUser && $_getUser->notificationSetting && $_getUser->notificationSetting->is_rating == 1){
                    SendFirebaseNotification('Rate Seller','Please share your feedback for your recent purchase.',getUsersFCMTokens($all['user_id']),['seller_id' => Auth::id(), 'post_id' => $all['post_id'], 'type' => 'RATE_SELLER','u_id'=>$all['user_id']]);
                }
                
            }else{
                user_notifications::where([['type','RATE_SELLER'],['user_id',Auth::user()->id],['resource_id',$all['user_id']],['resource_id_two',$all['post_id']]])->update(['is_delete' => 1]);
            }
    
            reviews::create([
                'user_id' => $all['user_id'],
                'post_id' => $all['post_id'],
                'rating' => $all['rating'],
                'review' => $all['review'] ?? null,
                'review_by' => Auth::user()->id
            ]);
    
            return $this->SendResponse(null, 'Review added successfully.', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function userPostDetails(Request $request)
    {
        try{
            $all = $request->all();

            $validator = Validator::make($all, [
                'user_id' => 'required|numeric',
                'post_id' => 'required|numeric'
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $user = User::find($all['user_id']);
            $post = posts::find($all['post_id']);
            $userTypeName = 'User';
            if($user->role_id ==2){
            $userTypeName = 'Store';
            }elseif($user->role_id ==3){
            $userTypeName = 'Brand';
            }
            
            $res['user'] = [
                'id' => $user->id,
                'user_type' => $user->role_id,
                'user_type_name' => $userTypeName,                
                'name' => $user->name,
                'username' => $user->username,
                    'photo' => ($user->photo !=null)?ResizeImage($user->photo,500,500):null,
                'avg_review' => round($user->reviews()->avg('rating'),2),
                'total_reviews' => $user->reviews()->count(),
                'total_posts' => $user->posts()->where([['is_active',1],['is_delete',0]])->count(),
                'location' => $user->locations()->where([['is_active',1],['is_delete',0],['is_default',1]])->select('id','address','city','area','postal_code','latitude','longitude')->orderby('id','desc')->first()
            ];

            $res['post'] =  [
                'id' => $post->id,
                'name' => $post->title,
                'brand_name' => $post->brand->name ?? null,
                'photo' => posts::getImages($post->images,true),
                'size' => posts::getSize($post->sizes,true),
                'price' => $post->price,
                'price_type' => $post->price_type,
                'price_type_name' => ($post->priceType)?$post->priceType->name:null,
            ];
    
            return $this->SendResponse($res, 'Success.', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function listStoreReviews(Request $request)
    {
        try{
            
            $all = $request->all();

            $validator = Validator::make($all, [
                'user_id' => 'required|numeric',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $reviews = user_reviews::has('user')->has('reviewBy')
                        ->where('user_id',$all['user_id'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(config('constants.API_PER_PAGE_RECORDS'));
            
            $data = [];
			
            foreach($reviews as $review){
                array_push($data,[
                    'id' => $review->id,
                    'review_by' => $review->review_by,
                    'review_by_name' => ($review->reviewBy)?$review->reviewBy->name:null,
                    'review_by_photo' => ($review->reviewBy && $review->reviewBy->photo !=null)?ResizeImage($review->reviewBy->photo,500,500):'',
                    'rating' => $review->rating,
                    'review' => $review->review ?? null,
					'review_images'=> $this->getreviewimages($review->review_images),
                    'created_at' => date('Y-m-d H:i:s',strtotime($review->created_at))
                ]);
            }
			$count = user_reviews::where('review_by',Auth::user()->id)->where('user_id',$all['user_id'])->count();
			$already_reviewed = 0;
            if($count > 0){
				$already_reviewed = 1;
			}   

            $res['currentPage'] = $reviews->currentPage();
            $res['hasMorePages'] = $reviews->hasMorePages();
            $res['already_reviewed'] = $already_reviewed;
            $res['reviews'] = $data;

            return $this->SendResponse($res, 'Success', 200);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
	public function getreviewimages($images,$isSingle = false){
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

    public function postReview(Request $request)
    {
        try{
            $all = $request->all();

            $validator = Validator::make($all, [
                'user_id' => 'required|numeric',
                'rating' => 'required|numeric|min:0|max:5',
                'review' => 'nullable|string',
				'image' => 'nullable|array|max:9',
                'image.*' => 'nullable|image|mimes:png,jpeg,jpg|max:5120',

            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
			  $images = [];
            if(isset($all['image']) && !empty($all['image'])){
                foreach($all['image'] as $key => $value){
                    array_push($images,UploadImage($value,'post_images','posts'));
                }
            }

    
            user_reviews::create([
                'user_id' => $all['user_id'],
                'rating' => $all['rating'],
                'review' => $all['review'] ?? null,
                'review_by' => Auth::user()->id,
                'review_images' => implode(',',$images),
            ]);
    
            return $this->SendResponse(null, 'Review added successfully.', 200);
        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
}
