<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Log; // add this on top

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\User;
use App\size;
use App\gender;
use App\user_sizes;
use App\user_favourite_brands;
use App\category;

class MySizeController extends APIResponseController {

    public function getSize(Request $request)
    {
        try {

            $sizeRes = [];
            $genders = gender::where('is_active',1)->get();
            foreach($genders as $gender){
                array_push($sizeRes,[
                    'gender_id' => $gender->id,
                    'gender_name' => $gender->name,
                    'categories' => $this->getGenderCategories($gender->id)
                ]);
            }

            return $this->SendResponse($sizeRes, 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function getGenderCategories($genderId)
    {
        $data = [];

        $categories = category::where([['gender_id',$genderId],['is_active',1],['is_delete',0]])->get();

        foreach($categories as $category){
            
            array_push($data,[
                'name' => $category->name,
                'image' => ResizeImage($category->photo,50,50),
                'sizes' => $this->getCategorySize($category->id)
            ]);

        }

        return $data;
    }

    public function getCategorySize($catId)
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

    public function saveSize(Request $request) {
        
        try {
			        Log::info('saveSize() function triggered', [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);


            
            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'size' => 'nullable|string',
                'brand' => 'nullable|string',
                'gender_id' => 'required'
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
            User::find(Auth::user()->id)->update(['user_selected_gender' => (int) $all['gender_id']]);

            if(isset($all['size']) && !empty($all['size'])){

                $all['size'] = explode(',',$all['size']);

                $data = [];
                foreach($all['size'] as $key => $value){
                    array_push($data,[
                        'user_id' => Auth::user()->id,
                        'size_id' => $value,
                        'is_active' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

                if(!empty($data)){
                    user_sizes::where('user_id',Auth::user()->id)->delete();
                    user_sizes::insert($data);
                }
            }
			else{
				user_sizes::where('user_id',Auth::user()->id)->delete();
			}
            if(isset($all['brand']) && !empty($all['brand'])){

                $all['brand'] = explode(',',$all['brand']);

                $data = [];
                foreach($all['brand'] as $key => $value){
                    array_push($data,[
                        'user_id' => Auth::user()->id,
                        'brand_id' => $value,
                        'is_active' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                if(!empty($data)){
                    user_favourite_brands::where('user_id',Auth::user()->id)->delete();
                    user_favourite_brands::insert($data);
                }
            }else{
                    user_favourite_brands::where('user_id',Auth::user()->id)->delete();                    
                }

            $user = User::getAPIUserDetails(Auth::user());

            return $this->SendResponse($user, 'Size updated successfully.', 200);
        } catch (\Exception $e) {
            return $this->SendError(null, $e->getMessage(), 500);
        }
    }

}
