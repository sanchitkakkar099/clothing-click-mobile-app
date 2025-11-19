<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;

class PromotController extends APIResponseController {

    public function index(Request $request)
    {
        try{

            $res = [
                // 'POST_TOP_PICK_COIN' => (int)config('constants.POST_TOP_PICK_COIN'),
                // 'POST_BUMP_COIN' => (int)config('constants.POST_BUMP_COIN'),
                'POST_HIGHTLIGHT_COIN' => (int)config('constants.POST_HIGHTLIGHT_COIN'),
                'POST_SALE_COIN' => (int)config('constants.POST_SALE_COIN'),
                // 'POST_PROFILE_PROMOTE_COIN' => (int)config('constants.POST_PROFILE_PROMOTE_COIN'),
                // 'POST_TOP_PICK_COIN_DESC' => config('constants.POST_TOP_PICK_COIN_DESC'),
                // 'POST_BUMP_COIN_DESC' => config('constants.POST_BUMP_COIN_DESC'),
                'POST_HIGHTLIGHT_COIN_DESC' => config('constants.POST_HIGHTLIGHT_COIN_DESC'),
                'POST_SALE_COIN_DESC' => config('constants.POST_SALE_COIN_DESC'),
                // 'POST_PROFILE_PROMOTE_COIN_DESC' => config('constants.POST_PROFILE_PROMOTE_COIN_DESC'),
                // 'POST_TOP_PICK_COIN_IMG' => config('constants_image.POST_TOP_PICK_COIN'),
                // 'POST_BUMP_COIN_IMG' => config('constants_image.POST_BUMP_COIN'),
                'POST_HIGHTLIGHT_COIN_IMG' => config('constants_image.POST_HIGHTLIGHT_COIN'),
                'POST_SALE_COIN_IMG' => config('constants_image.POST_SALE_COIN'),
                // 'POST_PROFILE_PROMOTE_COIN_IMG' => config('constants_image.POST_PROFILE_PROMOTE_COIN'),                
            ];

            return $this->SendResponse($res, 'Success', 200);

        }catch(\Exception $e){
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
}
