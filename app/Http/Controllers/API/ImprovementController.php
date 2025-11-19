<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\User;
use App\user_problems;
use App\user_suggestions;
use App\user_reported_users;

class ImprovementController extends APIResponseController {

    public function report_problem(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:5000',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $all['user_id'] = Auth::user()->id;

            user_problems::create($all);

            $details = [
                'user' => Auth::user()->name,
                'message' => $all['message'],
            ];
            

            try {
            \Mail::to('support@clothingclick.online')->send(new \App\Mail\ReportProblem($details));
            \Mail::to('matthewwintoniak@gmail.com')->send(new \App\Mail\ReportProblem($details));            
            } catch (\Exception $e) {
//                    dd($e);
            }
            
            return $this->SendResponse(null, 'Thank you for contacting us, our team will get back to you soon.', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function leave_suggestion(Request $request)
    {
        try {

            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:5000',
            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }

            $all['user_id'] = Auth::user()->id;

            user_suggestions::create($all);

            $details = [
                'user' => Auth::user()->name,
                'message' => $all['message'],
            ];


            try {
            \Mail::to('support@clothingclick.online')->send(new \App\Mail\LeaveSuggestion($details));
            \Mail::to('matthewwintoniak@gmail.com')->send(new \App\Mail\LeaveSuggestion($details));            
            } catch (\Exception $e) {
//                    dd($e);
            }
            
            return $this->SendResponse(null, 'Thank you for your valuable feedback.', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function report_user(Request $request)
    {
        try {
            $all = $request->all();

            $validator = Validator::make($request->all(), [
                'reported_user_id' => 'required|numeric',
                'message' => 'required|string|max:5000',
				'image' => 'nullable',
				'image.*' => 'nullable|image|mimes:png,jpeg,jpg|max:5120',

            ]);
            
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
			$image = UploadImage($all['image'],'post_images','posts');

            $all['user_id'] = Auth::user()->id;
            $all['image'] = $image;

            user_reported_users::create($all);

            return $this->SendResponse(null, 'User reported successfully..', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }
}
