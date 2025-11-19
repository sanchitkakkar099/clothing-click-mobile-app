<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return view('welcome');
});
Route::get('/profile/{user_id}','HomeController@profile')->name('profile');
Route::get('/fetchStores','HomeController@fetchStores')->name('stores');
Route::get('/shopify_posts','HomeController@getShopifyPost')->name('shopify_posts');
Route::get('/posts/{id}','HomeController@destroyByShopifyId')->name('delete_shopify_posts');

    //Route::get('user/shopify_posts', 'UserController@getShopifyPost');

Route::get('/post/{id}','HomeController@post')->name('post');
Route::get('send-mail', function () {
    $details = [
        'title' => 'Mail from ItSolutionStuff.com',
        'body' => 'This is for testing email using smtp'
    ];
    try {
    \Mail::to('snehal@jeeltechsoft.com')->send(new \App\Mail\MyTestMail($details));
    } catch (\Exception $e) {
            dd($e);
    }
    dd("Email is Sent.");

});

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/contact', 'HomeController@contact')->name('contact');

Route::get('/trouble_resetpassword/{id}', 'ResetPasswordController@resetpassword');

Route::post('/change_password/update', 'ResetPasswordController@updatePassword')->name('userUpdatePassword');
Route::get('/terms/', 'CMSController@terms');
Route::get('/privacy/', 'CMSController@privacy');
Route::get('/cookies/', 'CMSController@cookies');

//For Deep Linking
Route::get('api/share/{type}/{id}', 'DeepLinkingController@share');

//Verify email
Route::get('email/verify/{user_id}/{otp}', 'VerifyEmailController@verify')->name('verifyEmailLink');

Route::get('/update-serach-result', function() {
    $output = [];
    \Artisan::call('search:update', $output);
    dd($output);
});
Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('view:clear');
    return 'DONE'; //Return anything
});
/* Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('config:cache');
    return 'DONE'; //Return anything
}); */