<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\MySizeController;
use App\Http\Controllers\Controller;
use App\roles;
use App\User;
use App\Http\Requests\Admin\AddAdminConditionRequest;
use App\Http\Requests\Admin\AddAdminUserRegisterRequest;
use App\Http\Requests\Admin\UpdateAdminConditionRequest;

use Config;
use App\category;
use App\location;
use App\user_logindevice_info;
use App\user_notification_settings;
use App\user_sizes;
use Illuminate\Http\Request;
use Auth;

class ManageRegistrationController extends SystemAdminController {

    var $breadcum = ['icon' => 'mdi mdi-account-multiple', 'breadcum' => ['Manage Registrations']];

    public function __construct() {
        parent::__construct();
    }

    public function index(Request $request) {
        $filter = $request->all();
        $breadcum = $this->breadcum;
        $users = User::where(function($q)use($filter) {
                            if (!empty($filter['search'])) {
                                $q->where('username', 'LIKE', '%' . $filter['search'] . '%');
								$q->orwhere('email','LIKE','%'.$filter['search'].'%');

                            }
                            if (!empty($filter['code'])) {
                                $q->where('access_code', $filter['code']);
                            }                            
                        })
                        ->orderBy('id', 'desc')->paginate(Config::get('constants.pageRecords'));
        return view('admin.manage_registartion.list', compact('breadcum', 'users', 'filter'));
    }

   public function create(Request $request) {
       $breadcum = $this->breadcum;
        $categorydata = category::getList();
        $sizes = app(MySizeController::class);
        $sizes =$sizes->getSize($request);
        // dd($sizes);

       return view('admin.manage_registartion.create', compact('breadcum','categorydata','sizes'));
   }

     public function store(AddAdminUserRegisterRequest $request)
    {
        $all = $request->all();

        $country_code_prefix   = explode("_", $request->country_code_prefix);
        $all['country_code']   = $country_code_prefix[0];
        $all['country_prefix'] = '+' . $country_code_prefix[1];
        // $all['country_prefix'] =$country_code_prefix[0];

        try {
            
            $all['is_order'] = 1;    
            if (!isset($all['signup_type']) && empty($all['signup_type'])) {
                $all['signup_type'] = 1;
            }

            if ($all['signup_type'] == 1) {
                $all['password'] = bcrypt($all['password']);
            }
            $img = NULL;
            if($request->hasFile('photo')){
                $all['photo'] = UploadImage($request->file('photo'),'app_user_profile','registration');
            }
            $user = User::create($all);

            $all['appuser_id'] = $user->id;
            $all['user_id']    = $user->id;

            $token = $user->createToken(config('app.name'));

            // insert data to device info
            $all['devices_type']  = 'computer';
            $all['devices_token'] = base64_encode($all['phone'] . '_' . $user->id);
            $all['devices_id']    = base64_encode($all['phone'] . '_' . rand(1, 1000));
            $all['devices_name']  = 'windows';
            $all['app_version']   = 1;
            $all['os']            = 'windows';

            $all['access_token_id'] = $token->token->id;
            user_logindevice_info::create($all);

            $user['token'] = $token->accessToken;

            //Create location
            // $locations = json_decode($all['locations']);
            $locations = $all['locations'];

            if (!empty($locations)) {
                $i = 0;
                foreach ($all['city'] as $key => $city_name) {
                    if (array_key_exists($city_name, $all['locations'])) {
                        $location = $all['locations'][$city_name];

                        $isDefault = 0;
                        if ($i == 0) {
                            $isDefault = 1;
                        }

                        $isPaid        = 1;
                        $locationPrice = 0;

                        if ($all['role_id'] == 2 && $isDefault == 0) {
                            $isPaid        = 0;
                            $locationPrice = config('constants.Store_Monthly_Rate');
                        } elseif ($all['role_id'] == 3 && $isDefault == 0) {
                            $isPaid        = 0;
                            $locationPrice = config('constants.Brand_Monthly_Rate');
                        }

                        location::create([
                            'appuser_id'  => $user->id,
                            'address'     => $location['address'] ?? null,
                            'city'        => $location['city'] ?? null,
                            'area'        => $location['area'] ?? null,
                            'postal_code' => $location['postal_code'] ?? null,
                            'latitude'    => $location['latitude'] ?? null,
                            'longitude'   => $location['longitude'] ?? null,
                            'is_default'  => $isDefault,
                            'is_paid'     => $isPaid,
                            'price'       => $locationPrice,
                        ]);
                        $i++;
                    }
                }
            }

            user_notification_settings::create($all);

            $res          = User::getAPIUserDetails($user);
            $res['token'] = $user['token'];

            User::find($user->id)->update(['phone_verified_at' => date('Y-m-d H:i:s')]);

            // Save size
            if (isset($all['gender_id']) && $all['gender_id'] != null) {
                User::find($user->id)->update(['user_selected_gender' => $all['gender_id'], 'phone_verified_at' => date('Y-m-d H:i:s')]);
                if (isset($all['size_' . $all['gender_id']]) && !empty($all['size_' . $all['gender_id']])) {

                    $data = [];
                    foreach ($all['size_' . $all['gender_id']] as $key => $value) {
                        array_push($data, [
                            'user_id'    => $user->id,
                            'size_id'    => $value,
                            'is_active'  => 1,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }

                    if (!empty($data)) {
                        user_sizes::where('user_id', $user->id)->delete();
                        user_sizes::insert($data);
                    }
                }
            }
            return redirect()->route('appRegistation')->with('success', 'New Brand added successfully.');

        } catch (\Exception $e) {
            // dd($e);
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('appRegistation')->with('success', 'New Brand added successfully.');
    }
   
   
   
//    public function create() {
//        $breadcum = $this->breadcum;
//         $categorydata = category::getList();
//        return view('admin.manage_condition.create', compact('breadcum','categorydata'));
//    }
//
//    public function store(AddAdminConditionRequest $request) {
//        $all = $request->all();
//        $all['is_active'] = 1;
//        $role = condition::create($all);
//        return redirect()->route('adminCondition')->with('success', 'New admin condition added successfully.');
//    }
//
//    public function edit($id) {
//        $user = condition::find($id);
//        $breadcum = $this->breadcum;
//         $categorydata = category::getList();
//        return view('admin.manage_condition.update', compact('breadcum', 'user','categorydata'));
//    }
//
//    public function update(UpdateAdminConditionRequest $request) {
//        $all = $request->all();
//        condition::find($all['uid'])->update($all);
//        return redirect()->route('adminCondition')->with('success', 'Condition updated successfully.');
//    }

    public function publish(Request $request) {
        try {
            $record = User::find($request->id);
            if (!empty($record)) {
                if ($record->is_active == 1) {
                    User::find($record->id)->update(['is_active' => 0]);
                    $user = User::find($record->id);
                    $user->tokens()->delete();                                        
                } else {
                    User::find($record->id)->update(['is_active' => 1]);
                }
                return 'success';
            }
            return 'Record not found';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function delete($id) {
//        if($id == 1 || $id == 2){
//            return redirect()->back()->with('error','You can not delete admin location city');
//        }
        $user = User::find($id);
        $user->posts()->delete();
        $user->userSizes()->delete();
        $user->userFavouriteBrands()->delete();
        $user->userFavouritePosts()->delete();
        $user->followings()->delete();        
        $user->followers()->delete();                
        
        User::destroy($id);
        return redirect()->back()->with('success', 'App User deleted successfully.');
    }

}
