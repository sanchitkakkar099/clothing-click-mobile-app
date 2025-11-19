<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\reviews;
use App\posts;
use App\User;
use App\faq;
use App\user_notifications;

class FaqController extends APIResponseController {

    public function index(Request $request)
    {
		$res = [];		
		$faqs = faq::all();
		$res['faqs'] = $faqs;
        return $this->SendResponse($res, 'Success', 200);

        
    }

    
}
