<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class APIResponseController extends Controller
{
    public function SendResponse($result,$message,$errorCode)
    {
    	$response = [
            'status' => $errorCode,
			'message' => $message,
            'response' => $result,
        ];
		
        return response()->json($response,$errorCode);
    }
}
