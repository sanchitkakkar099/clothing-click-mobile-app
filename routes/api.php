<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API!
  |
 */
 $middlewares = \Request::header('Authorization') ? ['auth:api'] : [];

Route::group(['namespace' => 'API', 'prefix' => 'v1'], function() {

    Route::get('get_general_data', 'GeneralController@get_general_data');
    Route::post('signup', 'AuthController@signup');
    Route::post('login', 'AuthController@login');
    Route::post('forgot_password', 'AuthController@forgot_password');
    Route::post('logout_device_users', 'AuthController@logoutDeviceUsers');
    Route::post('check_access_code', 'AuthController@checkAccessCode');     
    Route::get('sizes', 'MySizeController@getSize');
	Route::get('categories_list', 'CategoryController@getAllCategories');
    Route::post('categories/posts', 'CategoryController@getCategoryPosts');
 //Dashboard
    Route::post('dashboard', 'DashboardController@index');
    Route::post('dashboard/post_list', 'DashboardController@post_list');
	Route::post('brand/posts', 'UserController@getBrandPost');
	Route::post('other_user_detail', 'UserController@getOtherUserDetails');
    Route::post('user/posts', 'UserController@getUserPost');
	 //Search
    Route::post('search', 'SearchController@index');

}); 

Route::group(['middleware' => $middlewares, 'namespace' => 'API', 'prefix' => 'v1'], function() {

    //Auth
    Route::post('signup/step2', 'AuthController@signupSteptwo');
    Route::post('signup/verify_user_phone', 'AuthController@verify_user_phone');
    Route::post('logout', 'AuthController@logout');
    Route::post('change_password', 'AuthController@change_password');
    Route::post('check_username', 'AuthController@CheckUsername');
    Route::get('user/details', 'AuthController@userDetail');
    Route::post('update_profile', 'AuthController@updateProfile');
    Route::post('convert_to_store', 'AuthController@ConvertToStore');
    Route::post('user/change_profile/status', 'AuthController@changeProfileStatus');
    Route::post('user/change_profile/findlocation', 'AuthController@changeLocationStatus');
    Route::post('user/email/show', 'AuthController@changeEmailStatus');
    Route::post('user/phone/show', 'AuthController@changePhoneStatus');
    Route::post('user/update/social_account_id', 'AuthController@updateSocialAccountId');
    Route::post('user/resend/email/verification/link', 'AuthController@resendEmailVerificationLink');

   

    //User
   Route::post('categories/posts', 'CategoryController@getCategoryPosts');
	    Route::post('dashboard', 'DashboardController@index');
    Route::post('dashboard/post_list', 'DashboardController@post_list');

    //Settings
    Route::post('notification/setting', 'SettingController@updateNotification');
    Route::post('update/chat_order_settings', 'SettingController@chatOrderSetting');

    //My Size
    Route::post('sizes/save', 'MySizeController@saveSize');

    //Brand
    Route::post('brand/search', 'BrandController@search');
    Route::get('brand/list', 'BrandController@list');

    //Report a problem
    Route::post('report_problem', 'ImprovementController@report_problem');

    //Leave Suggestion
    Route::post('leave_suggestion', 'ImprovementController@leave_suggestion');

    //Report User
    Route::post('report_user', 'ImprovementController@report_user');

    //User follow
    Route::post('user/followings', 'UserFollowerController@following');
    Route::post('user/followers', 'UserFollowerController@followers');
    Route::post('user/follow/requests', 'UserFollowerController@followRequests');
    Route::post('user/follow/requests/status', 'UserFollowerController@followRequestStatus');
    Route::post('follow/user', 'UserFollowerController@followUser');
    Route::post('unfollow/user', 'UserFollowerController@unfollowUser');

    //Categories
    Route::get('categories', 'CategoryController@index');
    Route::post('categories/size', 'CategoryController@getCategorySize');
    Route::get('categories/sizes', 'CategoryController@getCategorySizes');
    Route::get('styles', 'CategoryController@getCategoryStyles');

    //Make default Location
    Route::post('location/select', 'LocationController@selectLocation');
    Route::post('location/pay', 'LocationController@payLocation');
    Route::post('location/add', 'LocationController@addLocation');
    Route::post('location/delete', 'LocationController@deleteLocation');

    //Post
    Route::post('post/create', 'PostController@create');
    Route::post('post/update', 'PostController@update');
    Route::post('post/delete', 'PostController@delete');
    Route::post('post/sold', 'PostController@sold');
    Route::post('post/promote', 'PostController@changeType');
    Route::post('post/details', 'PostController@details');
    Route::post('post/sold/user_list', 'PostController@soldUserList');
    Route::post('post/delete_image', 'PostController@DeleteImage');
    //Route::post('post/sold/user', 'PostController@soldPostToUser');

    //Post Promote
    Route::get('promot', 'PromotController@index');

    //Save search
    Route::post('save_search', 'SaveSearchController@index');
    Route::post('save_search/create', 'SaveSearchController@create');
    Route::post('save_search/update', 'SaveSearchController@update');
    Route::post('save_search/details', 'SaveSearchController@details');
    Route::post('save_search/delete', 'SaveSearchController@delete');
    Route::post('save_search/posts', 'SaveSearchController@posts');

    //Get filtered post data
    Route::post('filter/post', 'FilterPostController@index');

    //User favourite post
    Route::post('favourite/post', 'FavouritePostController@index');
    Route::post('favourite/post/manage', 'FavouritePostController@manage');

   

    //Block user
    Route::get('block_users', 'BlockUserController@index');
    Route::post('block_users/create', 'BlockUserController@create');
    Route::post('unblock_user', 'BlockUserController@delete');

    //Chat
    Route::post('messages', 'ChatController@index');
    Route::post('message/details', 'ChatController@details');
    Route::post('message/send', 'ChatController@sendMessage');
    Route::post('message/delete', 'ChatController@deleteMessage');    
    
    //Coin
    Route::post('coins', 'CoinController@index');
    Route::post('coins/buy', 'CoinController@buy');

    //Review
    Route::post('reviews', 'ReviewController@index');
    Route::post('review/add', 'ReviewController@create');
    Route::post('review/user_post/details', 'ReviewController@userPostDetails');

    //Notifications
    Route::post('notifications', 'NotificationController@index');

    //Payment method
    Route::get('payment_method', 'PaymentMethodController@index');
    Route::post('payment_method/add', 'PaymentMethodController@create');
    Route::post('payment_method/delete', 'PaymentMethodController@delete');

    //Premium user
    Route::post('premium/buy', 'PremiumController@buy');
    //Brand
    
    //Create new brand
    Route::post('brand_add', 'BrandController@saveBrand');        
    Route::post('delete_account', 'UserController@deleteUser');        
    //Create new brand
    
    //send custom Notifications
    Route::post('send/notification', 'NotificationController@sendNotification');        
    
	// FAQs
	Route::get('faqs', 'FaqController@index');
	
	// Support and Feedback
	Route::get('support_feedback', 'FeedbackController@index');
	Route::post('post_feedback', 'FeedbackController@add');
	
	
	// Payments
	Route::post('add_card', 'PostController@addCard');
	Route::post('create_payment_intent', 'PostController@createPaymentIntent');
	Route::post('get_payment_intent', 'PostController@getPaymentIntent');
	
	//Orders
	Route::post('save_order', 'FilterPostController@saveOrder');
	Route::post('view_order', 'FilterPostController@getSingleOrder');
	Route::post('list_orders', 'FilterPostController@getAllOrder');
	
	//Store Reviews
	Route::post('list_all_reviews', 'ReviewController@listStoreReviews');
	Route::post('post_review', 'ReviewController@postReview');
	
Route::get('/clear-cache', function () {
   Artisan::call('cache:clear');
   Artisan::call('route:clear');

   return "Cache cleared successfully";
});
	//Route::post('list_orders', function(){echo "II am here";exit;});
});
