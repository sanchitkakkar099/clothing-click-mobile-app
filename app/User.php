<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\user_sizes;
use App\size;
use App\user_favourite_brands;
use App\posts;
use App\store_details;
use App\user_reviews;
use App\user_blocked_users;
use App\user_followers;
use DB;
use Auth;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable;

    protected $fillable = [
        'name', 'email','facebook_id','instagram_id','google_id','twitter_id','apple_id', 'password','phone','is_active','phone_verified_at','email_verified_at', 'role_id','location_id','username','photo','is_active','is_private','is_delete','user_selected_gender','country_code','country_prefix','website','bio','is_findlocation_on','coins','is_chat','is_order', 'stripe_customer_id', 'is_premium_user', 'is_premium_activated', 'premium_end_at','is_phone_show','is_email_show','access_code','is_first_login', 'last_login'
    ];

    protected $casts = [
        'role_id' => 'integer',
        'location_id' => 'integer',
        'is_active' => 'integer',
        'is_private' => 'integer',
        'is_delete' => 'integer',
        'user_selected_gender' => 'integer',
        'is_findlocation_on' => 'integer',
        'coins' => 'double',
        'is_chat' => 'integer',
        'is_premium_user' => 'integer',
        'is_premium_activated' => 'integer',
        'is_order' => 'integer',
    ];

    public static function getAPIUserDetails($user, $isOtherUser = false)
{
    // Always fetch a fresh user instance
    if (is_object($user)) {
        $user = Self::find($user->id);
    } else {
        $user = Self::find($user);
    }

    if (!$user) {
        return null;
    }

    // Combine blocked user lists
    $blockedUsers1 = $user->listUserBlocked()->pluck('user_id')->toArray();
    $blockedUsers2 = $user->getBlockedUsers()->pluck('blocked_user_id')->toArray();
    $blockedUsers  = array_merge($blockedUsers1, $blockedUsers2);

    // Gender name
    $user['gender_name'] = $user->gender->name ?? null;

    // USER TYPE HANDLING
    if ($user->role_id !== 8) {
        // Normal user
        $user['type'] = 'user';
        $user['photo'] = !empty($user['photo']) ? ResizeImage($user['photo'], 1000, 1000, 'user_image') : null;
        $user['cover_image'] = '';
        $user['avg_rating'] = round($user->reviews()->has('user')->has('reviewBy')->avg('rating'), 2);
        $user['total_reviews'] = $user->reviews()->has('user')->has('reviewBy')->count();
    } else {
        // Store user
        $user['type'] = 'store';
        $store_data = store_details::where('user_id', $user->id)->first();
        if ($store_data) {
            $user['photo'] = $store_data->profile_image;
            $user['cover_image'] = $store_data->cover_image;
            $user['store_details'] = $store_data;
            $user['store_details']['timings'] = unserialize($store_data['timings']);
        }
        $user['avg_rating'] = round(user_reviews::where('user_id', $user->id)->avg('rating'), 2);
        $user['total_reviews'] = user_reviews::where('user_id', $user->id)->count();
    }

    // Follow counts
    $user['totalFollowings'] = $user->followings()
        ->whereNotIn('following_user_id', $blockedUsers)
        ->has('user')
        ->where([['is_active', 1], ['is_delete', 0]])
        ->count();

    $user['totalFollowers'] = $user->followers()
        ->whereNotIn('follower_user_id', $blockedUsers)
        ->has('user')
        ->where([['is_accepted', 1], ['is_active', 1], ['is_delete', 0]])
        ->count();

    // Posts
    $user['totalPosts'] = $user->posts()->where([['is_active', 1], ['is_delete', 0], ['is_sold', 0]])->count();
    $user['totalSoldPosts'] = $user->posts()->where([['is_active', 1], ['is_delete', 0], ['is_sold', 1]])->count();

    // Dates
    $user['created_at'] = date('Y-m-d H:i:s', strtotime($user['created_at']));
    $user['joined_at'] = Carbon::parse($user->created_at)->format('Y-m-d H:i:s');

    // Notifications
    $user['total_unread_notifications'] = $user->notifications()
        ->where([['is_read', 0], ['is_active', 1], ['is_delete', 0]])
        ->count();

    // Default fields
    $user['is_requested'] = 0;
    $user['is_following'] = 0;
    $user['follow_request_id'] = null;

    if ($user->role_id == 2) {
        $user['is_order'] = 0;
    }

    // Locations
    $locationQuery = $user->locations()->where('is_delete', 0);
    if ($user->role_id == 1) {
        $locationQuery->where('is_default', 1);
    }
    $user['locations'] = $locationQuery
        ->select('id', 'address', 'postal_code', 'latitude', 'longitude', 'price', 'city', 'area', 'is_default', 'is_paid', 'is_active', 'expire_at')
        ->latest()
        ->take(1)
        ->get();

    // ✅ Handle follow / follow request status
    if ($isOtherUser && Auth::check()) {

        $authId = Auth::id();

        // Check if there's a pending follow request (not accepted yet)
        $checkFollowRequest = user_followers::where([
            ['is_active', 1],
            ['is_delete', 0],
            ['is_accepted', 0],
            ['user_id', $user->id],           // profile being viewed
            ['follower_user_id', $authId]     // current user sent request
        ])->latest('id')->first();

        if ($checkFollowRequest) {
            $user['is_requested'] = 1;
            $user['follow_request_id'] = $checkFollowRequest->id;
        }

        // ✅ FIXED: Correct direction for follow check
        // Means -> does auth user (24) follow viewed user (104)
        $isFollowing = user_followers::where([
            ['is_active', 1],
            ['is_delete', 0],
            ['is_accepted', 1],
            ['user_id', $user->id],           // viewed profile
            ['follower_user_id', $authId]     // current logged-in follower
        ])->exists();

        $user['is_following'] = $isFollowing ? 1 : 0;

        // Restrict sold posts if private and not followed
        if (!$isFollowing && $user->is_private == 1) {
            $user['totalSoldPosts'] = 0;
        }

    } elseif ($isOtherUser && !Auth::check()) {
        // Guest user
        $checkFollowRequest = user_followers::where([
            ['is_active', 1],
            ['is_delete', 0],
            ['is_accepted', 0],
            ['user_id', $user->id],
        ])->latest('id')->first();

        if ($checkFollowRequest) {
            $user['is_requested'] = 1;
            $user['follow_request_id'] = $checkFollowRequest->id;
        }
    }

    // Additional info for self (non-other-user)
    if (!$isOtherUser) {
        $user['total_positive_review'] = $user->reviews()->where('rating', '>', 2)->has('reviewBy')->count();
        $user['coins'] = $user->coins;
        $user['user_size'] = user_sizes::where('user_id', $user->id)->pluck('size_id')->toArray();
        $user['user_sizes_details'] = $user->getUserSizeDetails($user->id);
        $user['user_favourite_brand'] = user_favourite_brands::getUserBrand($user->id);
        $user['notificationSetting'] = $user->notificationSetting()
            ->select('is_all', 'is_save_search', 'is_chat', 'is_follower', 'is_rating', 'is_watchlist', 'is_coin')
            ->first();
    }

    // Tabs
    $selling_count = ($user['totalSoldPosts'] > 0) ? '(' . $user['totalSoldPosts'] . ')' : null;
    $user['tabs'] = [
        ['type' => 1, 'name' => 'Selling'],
        ['type' => 2, 'name' => 'Sold' . $selling_count],
    ];

    // Clean up
    unset($user['is_delete'], $user['updated_at'], $user['gender']);

    return $user;
}


    public function gender()
    {
        return $this->belongsTo('App\gender','user_selected_gender','id');
    }

    public function locations()
    {
        return $this->hasMany('App\location','appuser_id','id');
    }

    public function notificationSetting()
    {
        return $this->hasOne('App\user_notification_settings','user_id','id');
    }

    public function notifications()
    {
        return $this->hasOne('App\user_notifications','user_id','id');
    }

    public function followings()
    {
        return $this->hasMany('App\user_followings','user_id','id');
    }

    public function followers()
    {
        return $this->hasMany('App\user_followers','user_id','id');
    }

    public function posts()
    {
        return $this->hasMany('App\posts','user_id','id');
    }

    public function userSizes()
    {
        return $this->hasMany('App\user_sizes','user_id','id');
    }

    public function userFavouriteBrands()
    {
        return $this->hasMany('App\user_favourite_brands','user_id','id');
    }

    public function userFavouritePosts()
    {
        return $this->hasMany('App\user_favourite_posts','user_id','id');
    }

    public function reviews()
    {
        return $this->hasMany('App\reviews','user_id','id');
    }

    public function getBlockedUsers()
    {
        return $this->hasMany('App\user_blocked_users','user_id','id')->where([['is_active',1],['is_delete',0]]);
    }
	



    public function checkUserFavoutitePostById($post)
    {
        $check = Auth::user()->userFavouritePosts()->where('post_id',$post->id)->count();
        if($check > 0){
            return true;
        }
        return false;
    }

    public static function getAPIStoreListData($users)
    {
        $res = [];

        foreach($users as $user){
			$store_details  = store_details::where('user_id',$user->id)->first();
			$total_posts = $user->posts()->where([['is_active',1],['is_delete',0],['is_sold',0]])->count();
            array_push($res,[
                'id' => $user->id,
                'name' => $user->name,
                'user_image' => $store_details->profile_image,
				'total_posts'=> (string) $total_posts
            ]);
        }
		usort($res, function($a, $b) {
			return (int)$b['total_posts'] - (int)$a['total_posts']; // descending order
		});


        return $res;
    }
    public function getUserSizeDetails($user_id)
    {
		$sizes = user_sizes::where('user_id',$user_id)->pluck('size_id')->toArray();

        $res = [];

        foreach($sizes as $size){
			//echo "<pre>";print_r($size);exit;
			$name = size::where('id',$size)->pluck('name')->first();
            array_push($res,
                $name
            );
        }

        return $res;
    }
    public static function getOrderAPIsellerData($userId)
    {
        $res = [];
		$user = Self::select('role_id','name','photo')->where([['id',$userId]])->first();
		$res['name']=$user->name;
		$res['username']=$user->username;
		if($user->role_id == 8){
			$res['image']=store_details::select('profile_image')->where([['user_id',$userId]])->first();
			$res['type']='store';
		}else{
			$res['image']=ResizeImage($user->photo,1000,1000);
			$res['type']='user';
		}
        

        return $res;
    }

    public function scopeGetNearestStore($query,$user, $distance = 50)
    {
        $userSelectedLocation = $user->locations()->where([['is_default',1],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();
        
        if(!empty($userSelectedLocation)){
            
            $getNearestLocationIds = location::where([['is_active',1],['is_delete',0],['appuser_id','!=',$user->id]])
                                ->select('id', DB::raw("6371 * acos(cos(radians(" . $userSelectedLocation->latitude . "))
                                * cos(radians(latitude)) * cos(radians(longitude) - radians(" . $userSelectedLocation->longitude . "))
                                + sin(radians(" .$userSelectedLocation->latitude. ")) * sin(radians(latitude))) AS distance"))
                                ->having('distance', '<=', $distance)
                                ->pluck('id')->toArray();
            
            //if(!empty($getNearestLocationIds)){
                $query->whereHas('locations',function($q)use($getNearestLocationIds){
                    $q->whereIn('id',$getNearestLocationIds);
                });
            //}

        }

        return $query;
    }

    public function scopegetPopularBrands($query,$user)
    {
        $userBrands = $user->userFavouriteBrands()->where('is_active',1)->orderby('id','desc')->first();
        
        if(!empty($userBrands)){
            $query->whereHas('userFavouriteBrands',function($q) use($userBrands) {
                $q->whereIn('id',$userBrands);
            });
        }

        return $query;
    }

    public function scopeGetNearestUser($query,$user,$isDistanceCondition = false, $distance = 50)
    {
        $userSelectedLocation = $user->locations()->where([['is_default',1],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();

        if(empty($userSelectedLocation)){
            $userSelectedLocation = $user->locations()->where([['is_active',1],['is_delete',0]])->orderby('id','asc')->first();
        }
        
        if(!empty($userSelectedLocation)){
            
            $query->select('users.*', DB::raw("6371 * acos(cos(radians(" . $userSelectedLocation->latitude . ")) * cos(radians(location.latitude)) * cos(radians(location.longitude) - radians(" . $userSelectedLocation->longitude . ")) + sin(radians(" .$userSelectedLocation->latitude. ")) * sin(radians(location.latitude))) AS distance"))
                ->join('location','users.id','=','location.appuser_id')
                ->where([['location.is_active',1],['location.is_delete',0]])
                ->groupby('users.id');

            if($isDistanceCondition){
                $query->having('distance','<=',$distance);
            }

        }

        return $query;
    }
    
    public function addFaccebook($input)
    {
        $check = static::where('facebook_id',$input['facebook_id'])->first();
        if(is_null($check)){
            return static::create($input);
        }
        return $check;
    }

    public function scopeRemoveBlockUser($query)
    {
        if(isset(Auth::user()->id) && !empty(Auth::user()->id)){
            $blockedUsers1 = Auth::user()->listUserBlocked()->pluck('user_id')->toArray();
            $blockedUsers2 = Auth::user()->getBlockedUsers()->pluck('blocked_user_id')->toArray();
            $blockedUsers = array_merge($blockedUsers1,$blockedUsers2);
            if(!empty($blockedUsers)){
                $query->whereNotIn('users.id',$blockedUsers);
            }
        }

        return $query;
    }

    public function listUserBlocked()
    {
        return $this->hasMany('App\user_blocked_users','blocked_user_id','id')->where([['is_active',1],['is_delete',0]]);
    }    
    
    protected $hidden = [
        'password', 'remember_token'
    ];
}
