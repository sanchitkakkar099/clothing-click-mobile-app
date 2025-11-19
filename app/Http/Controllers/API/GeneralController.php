<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;

class GeneralController extends APIResponseController {

    public function get_general_data(Request $request)
    {
        try {

            $res['additional_store_location_rate'] = config('constants.Store_Monthly_Rate');
            $res['additional_brand_location_rate'] = config('constants.Brand_Monthly_Rate');
            $res['is_show_access_code_screen'] = 0;
            
            return $this->SendResponse($res, 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

}
