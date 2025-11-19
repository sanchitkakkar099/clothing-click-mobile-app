<?php
/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register admin routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "admin" middleware group. Now create something great!
  |
 */
Route::get('/login', 'Admin\Auth\LoginController@showLoginForm')->name('adminLogin');
Route::post('/login', 'Admin\Auth\LoginController@doLogin')->name('adminLogin');
//Routes check with login and permission
Route::group(['middleware' => ['IsAdmin', 'AdminPermission'], 'namespace' => 'Admin'], function() {
    //Dashboard
    Route::get('/dashboard', 'DashboardController@index')->name('adminHome');
    //Conatct Us Leads
    Route::get('/contact_leads', 'ContactLeadController@index')->name('adminContactLeads');
    Route::get('/contact_leads/delete/{id}', 'ContactLeadController@delete')->name('adminContactLeadsDelete');
    //Access Rights
    Route::get('/access_rights', 'AccessRightsController@index')->name('adminAccessRights');
    Route::post('/access_rights/update', 'AccessRightsController@update')->name('adminUpdateAccessRights');
    //General Settings
    Route::get('/general_settings', 'GeneralSettingController@index')->name('adminGeneralSettings');
    Route::post('/general_settings/update', 'GeneralSettingController@update')->name('adminUpdateGeneralSettings'); 
	Route::get('/mobileapp_settings', 'GeneralSettingController@mobile_settings')->name('adminMobileAppSettings');
    Route::post('/mobileapp_settings', 'GeneralSettingController@mobile_settings')->name('adminMobileAppSettings');
    //User Profile
    Route::get('/user_profile', 'UserProfileController@index')->name('adminUserProfile');
    Route::post('/user_profile/update', 'UserProfileController@update')->name('adminUserProfileUpdate');
    Route::get('/change_password', 'UserProfileController@changePassword')->name('adminChangePassword');
    Route::post('/change_password/update', 'UserProfileController@updatePassword')->name('adminUpdatePassword');
    //Admin Role Manager
    Route::get('/admin_roles', 'ManageRoleController@index')->name('adminRoleManager');
    Route::get('/admin_roles/create', 'ManageRoleController@create')->name('adminRoleManagerCreate');
    Route::post('/admin_roles/create', 'ManageRoleController@store')->name('adminRoleManagerStore');
    Route::get('/admin_roles/update/{id}', 'ManageRoleController@edit')->name('adminRoleManagerEdit');
    Route::post('/admin_roles/update', 'ManageRoleController@update')->name('adminRoleManagerUpdate');
    Route::get('/admin_roles/delete/{id}', 'ManageRoleController@delete')->name('adminRoleManagerDelete');
    Route::post('/admin_roles/publish', 'ManageRoleController@publish')->name('adminRoleManagerPublish');
    //Admin User Manager
    Route::get('/admin_users', 'ManageAdminUserController@index')->name('adminUserManager');
    Route::get('/admin_users/create', 'ManageAdminUserController@create')->name('adminUserManagerCreate');
    Route::post('/admin_users/create', 'ManageAdminUserController@store')->name('adminUserManagerStore');
    Route::get('/admin_users/update/{id}', 'ManageAdminUserController@edit')->name('adminUserManagerEdit');
    Route::post('/admin_users/update', 'ManageAdminUserController@update')->name('adminUserManagerUpdate');
    Route::get('/admin_users/delete/{id}', 'ManageAdminUserController@delete')->name('adminUserManagerDelete');
    Route::post('/admin_users/publish', 'ManageAdminUserController@publish')->name('adminUserManagerPublish');
    
   
    //Admin Location City
    Route::get('/locationcity', 'ManageAdminLocationCityController@index')->name('adminLocationCity');
    Route::get('/locationcity/create', 'ManageAdminLocationCityController@create')->name('adminLocationCityCreate');
    Route::post('/locationcity/create', 'ManageAdminLocationCityController@store')->name('adminLocationCityStore');
    Route::get('/locationcity/update/{id}', 'ManageAdminLocationCityController@edit')->name('adminLocationCityEdit');
    Route::post('/locationcity/update', 'ManageAdminLocationCityController@update')->name('adminLocationCityUpdate');
    Route::get('/locationcity/delete/{id}', 'ManageAdminLocationCityController@delete')->name('adminLocationCityDelete');
    Route::post('/locationcity/publish', 'ManageAdminLocationCityController@publish')->name('adminLocationCityPublish');
    //Admin Gender
    Route::get('/gender', 'ManageAdminGenderController@index')->name('adminGender');
    Route::get('/gender/create', 'ManageAdminGenderController@create')->name('adminGenderCreate');
    Route::post('/gender/create', 'ManageAdminGenderController@store')->name('adminGenderStore');
    Route::get('/gender/update/{id}', 'ManageAdminGenderController@edit')->name('adminGenderEdit');
    Route::post('/gender/update', 'ManageAdminGenderController@update')->name('adminGenderUpdate');
    Route::get('/gender/delete/{id}', 'ManageAdminGenderController@delete')->name('adminGenderDelete');
    Route::post('/gender/publish', 'ManageAdminGenderController@publish')->name('adminGenderPublish');
    //Admin Category
    Route::get('/category', 'ManageAdminCategoryController@index')->name('adminCategory');
    Route::get('/category/create', 'ManageAdminCategoryController@create')->name('adminCategoryCreate');
    Route::post('/category/create', 'ManageAdminCategoryController@store')->name('adminCategoryStore');
    Route::get('/category/update/{id}', 'ManageAdminCategoryController@edit')->name('adminCategoryEdit');
    Route::post('/category/update', 'ManageAdminCategoryController@update')->name('adminCategoryUpdate');
    Route::get('/category/delete/{id}', 'ManageAdminCategoryController@delete')->name('adminCategoryDelete');
    Route::post('/category/publish', 'ManageAdminCategoryController@publish')->name('adminCategoryPublish');

    //Admin Size
    Route::get('/size', 'ManageAdminSizeController@index')->name('adminSize');
    Route::get('/size/create', 'ManageAdminSizeController@create')->name('adminSizeCreate');
    Route::post('/size/create', 'ManageAdminSizeController@store')->name('adminSizeStore');
    Route::get('/size/update/{id}', 'ManageAdminSizeController@edit')->name('adminSizeEdit');
    Route::post('/size/update', 'ManageAdminSizeController@update')->name('adminSizeUpdate');
    Route::get('/size/delete/{id}', 'ManageAdminSizeController@delete')->name('adminSizeDelete');
    Route::post('/size/publish', 'ManageAdminSizeController@publish')->name('adminSizePublish');
    
    
     //Admin Color
    Route::get('/color', 'ManageAdminColorController@index')->name('adminColor');
    Route::get('/color/create', 'ManageAdminColorController@create')->name('adminColorCreate');
    Route::post('/color/create', 'ManageAdminColorController@store')->name('adminColorStore');
    Route::get('/color/update/{id}', 'ManageAdminColorController@edit')->name('adminColorEdit');
    Route::post('/color/update', 'ManageAdminColorController@update')->name('adminColorUpdate');
    Route::get('/color/delete/{id}', 'ManageAdminColorController@delete')->name('adminColorDelete');
    Route::post('/color/publish', 'ManageAdminColorController@publish')->name('adminColorPublish');
    
     //Admin Conditions
    Route::get('/condition', 'ManageAdminConditionController@index')->name('adminCondition');
    Route::get('/condition/create', 'ManageAdminConditionController@create')->name('adminConditionCreate');
    Route::post('/condition/create', 'ManageAdminConditionController@store')->name('adminConditionStore');
    Route::get('/condition/update/{id}', 'ManageAdminConditionController@edit')->name('adminConditionEdit');
    Route::post('/condition/update', 'ManageAdminConditionController@update')->name('adminConditionUpdate');
    Route::get('/condition/delete/{id}', 'ManageAdminConditionController@delete')->name('adminConditionDelete');
    Route::post('/condition/publish', 'ManageAdminConditionController@publish')->name('adminConditionPublish'); 
	
	//Admin Style
    Route::get('/style', 'ManageAdminStyleController@index')->name('adminStyle');
    Route::get('/style/create', 'ManageAdminStyleController@create')->name('adminStyleCreate');
    Route::post('/style/create', 'ManageAdminStyleController@store')->name('adminStyleStore');
    Route::get('/style/update/{id}', 'ManageAdminStyleController@edit')->name('adminStyleEdit');
    Route::post('/style/update', 'ManageAdminStyleController@update')->name('adminStyleUpdate');
    Route::get('/style/delete/{id}', 'ManageAdminStyleController@delete')->name('adminStyleDelete');
    Route::post('/style/publish', 'ManageAdminStyleController@publish')->name('adminStylePublish');
	
	////Admin FAQ
    Route::get('/faqs', 'ManageAdminFaqController@index')->name('adminFaq');
    Route::get('/faqs/create', 'ManageAdminFaqController@create')->name('adminFaqCreate');
    Route::post('/faqs/create', 'ManageAdminFaqController@store')->name('adminFaqStore');
    Route::get('/faqs/update/{id}', 'ManageAdminFaqController@edit')->name('adminFaqEdit');
    Route::post('/faqs/update', 'ManageAdminFaqController@update')->name('adminFaqUpdate');
    Route::get('/faqs/delete/{id}', 'ManageAdminFaqController@delete')->name('adminFAQDelete');
    Route::post('/faqs/publish', 'ManageAdminFaqController@publish')->name('adminFaqPublish');
    //App User
    Route::get('/registration', 'ManageRegistrationController@index')->name('appRegistation');
    Route::get('/registration/create', 'ManageRegistrationController@create')->name('appRegistationCreate');
    Route::post('/registration/create', 'ManageRegistrationController@store')->name('appRegistationStore');
    Route::get('/registration/update/{id}', 'ManageRegistrationController@edit')->name('appRegistationEdit');
    Route::post('/registration/update', 'ManageRegistrationController@update')->name('appRegistationUpdate');
    Route::get('/registration/delete/{id}', 'ManageRegistrationController@delete')->name('appRegistationDelete');
    Route::post('/registration/publish', 'ManageRegistrationController@publish')->name('appRegistationPublish');
    
    
      //Admin Banner
    Route::get('/banner', 'ManageAdminBannerController@index')->name('adminBanner');
    Route::get('/banner/create', 'ManageAdminBannerController@create')->name('adminBannerCreate');
    Route::post('/banner/create', 'ManageAdminBannerController@store')->name('adminBannerStore');
    Route::get('/banner/update/{id}', 'ManageAdminBannerController@edit')->name('adminBannerEdit');
    Route::post('/banner/update', 'ManageAdminBannerController@update')->name('adminBannerUpdate');
    Route::get('/banner/delete/{id}', 'ManageAdminBannerController@delete')->name('adminBannerDelete');
    Route::post('/banner/publish', 'ManageAdminBannerController@publish')->name('adminBannerPublish');
    
    
      //Admin Coins
    Route::get('/coins', 'ManageAdminCoinsController@index')->name('adminCoins');
    Route::get('/coins/create', 'ManageAdminCoinsController@create')->name('adminCoinsCreate');
    Route::post('/coins/create', 'ManageAdminCoinsController@store')->name('adminCoinsStore');
    Route::get('/coins/update/{id}', 'ManageAdminCoinsController@edit')->name('adminCoinsEdit');
    Route::post('/coins/update', 'ManageAdminCoinsController@update')->name('adminCoinsUpdate');
    Route::get('/coins/delete/{id}', 'ManageAdminCoinsController@delete')->name('adminCoinsDelete');
    Route::post('/coins/publish', 'ManageAdminCoinsController@publish')->name('adminCoinsPublish');
    
     //App Posts
    Route::get('/posts', 'ManagePostsController@index')->name('appPosts');
    Route::get('/posts/create', 'ManagePostsController@create')->name('appPostsCreate');
    Route::post('/posts/create', 'ManagePostsController@store')->name('appPostsStore');
    Route::get('/posts/update/{id}', 'ManagePostsController@edit')->name('appPostsEdit');
    Route::post('/posts/update', 'ManagePostsController@update')->name('appPostsUpdate');
    Route::get('/posts/delete/{id}', 'ManagePostsController@delete')->name('appPostsDelete');
    Route::post('/posts/publish', 'ManagePostsController@publish')->name('appPostsPublish');
	Route::get('/sold_analytics', 'ManagePostsController@sold_analytics')->name('adminSoldAnalytics');
    Route::post('/sold_analytics', 'ManagePostsController@sold_analytics')->name('adminSoldAnalytics');
	Route::get('/top_sellers', 'ManagePostsController@top_sellers')->name('adminSellersAnalytics');
	Route::get('/reported_users', 'ManageAdminUserController@reported_users')->name('adminReportedUsers');
	Route::get('/user_reporting/{id}', 'ManageAdminUserController@user_reporting')->name('adminUserReporting');
    Route::post('/top_sellers', 'ManagePostsController@top_sellers')->name('adminSellersAnalytics');
    
    //Admin Brand
    Route::get('/brand', 'ManageAdminBrandController@index')->name('adminBrand');
    Route::get('/brand/create', 'ManageAdminBrandController@create')->name('adminBrandCreate');
    Route::post('/brand/create', 'ManageAdminBrandController@store')->name('adminBrandStore');
    Route::get('/brand/update/{id}', 'ManageAdminBrandController@edit')->name('adminBrandEdit');
    Route::post('/brand/update', 'ManageAdminBrandController@update')->name('adminBrandUpdate');
    Route::get('/brand/delete/{id}', 'ManageAdminBrandController@delete')->name('adminBrandDelete');
    Route::post('/brand/publish', 'ManageAdminBrandController@publish')->name('adminBrandPublish');    
    
     //Admin Access Code
    Route::get('/code', 'ManageAdminCodeController@index')->name('adminCode');
    Route::get('/code/create', 'ManageAdminCodeController@create')->name('adminCodeCreate');
    Route::post('/code/create', 'ManageAdminCodeController@store')->name('adminCodeStore');
    Route::get('/code/update/{id}', 'ManageAdminCodeController@edit')->name('adminCodeEdit');
    Route::post('/code/update', 'ManageAdminCodeController@update')->name('adminCodeUpdate');
    Route::get('/code/delete/{id}', 'ManageAdminCodeController@delete')->name('adminCodeDelete');
    Route::post('/code/publish', 'ManageAdminCodeController@publish')->name('adminCodePublish');
    
    
     // Custom Notifications
    Route::get('/notifications','ManageNotificationsController@index')->name('Notifications');
    Route::get('/notifications/create','ManageNotificationsController@create')->name('NotificationsCreate');
    Route::post('/notifications/create','ManageNotificationsController@store')->name('NotificationsStore');
    Route::get('/notifications/delete/{id}','ManageNotificationsController@delete')->name('NotificationsDelete');
    Route::post('/notifications/publish','ManageNotificationsController@publish')->name('NotificationsPublish');    
});
//Routes check with login
Route::group(['middleware' => 'IsAdmin', 'namespace' => 'Admin'], function() {
    //Logout
    Route::post('/logout', 'Auth\LoginController@logout')->name('adminLogout');
    //Set records
    Route::get('/set_records/{record}', 'SystemAdminController@setRecords')->name('adminSetRecords');
    Route::get('/', function() {
        return redirect()->route('adminHome');
    });
});
