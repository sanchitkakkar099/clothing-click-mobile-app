<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\User;
use App\user_blocked_users;

class BlockUserController extends APIResponseController {

    public function index(Request $request)
    {
        try {
            $users = user_blocked_users::has('user')->with('user')->where([['user_id',Auth::user()->id],['is_active',1],['is_delete',0]])->paginate(config('constants.API_PER_PAGE_RECORDS'));

            $data = [];
            foreach($users as $user){
                array_push($data,[
                    'user_id' => $user->blocked_user_id,
                    'name' => $user->user->name,
                    'username' => $user->user->username,
                    'image' => ResizeImage($user->user->photo,500,500)
                ]);
            }

            $res['currentPage'] = $users->currentPage();
            $res['hasMorePages'] = $users->hasMorePages();
            $res['blocked_users'] = $data;

            return $this->SendResponse($res, 'Success', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function create(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric|min:1'
            ]);
    
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
            
            $all = $request->all();

            $user = User::find($all['user_id']);

            if(empty($user)){
                return $this->SendResponse(null, 'Invalid user id.', 400);
            }

            $check = user_blocked_users::where([['user_id',Auth::user()->id],['blocked_user_id',$all['user_id']],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();

            if(!empty($check)){
                return $this->SendResponse(null, 'You have already blocked this user.', 400);
            }

            if(Auth::user()->id == $all['user_id']){
                return $this->SendResponse(null, 'Invalid user id.', 400);
            }

            user_blocked_users::create([
                'user_id' => Auth::user()->id,
                'blocked_user_id' => $all['user_id'],
                'is_active' => 1,
                'is_delete' => 0
            ]);

            return $this->SendResponse(null, 'User blocked successfully', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

    public function delete(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric|min:1'
            ]);
    
            if ($validator->fails()) {
                return $this->SendResponse(null, $validator->messages()->first(), 400);
            }
            
            $all = $request->all();

            $user = User::find($all['user_id']);

            if(empty($user)){
                return $this->SendResponse(null, 'Invalid user id.', 400);
            }

            $check = user_blocked_users::where([['user_id',Auth::user()->id],['blocked_user_id',$all['user_id']],['is_active',1],['is_delete',0]])->orderby('id','desc')->first();

            if(empty($check)){
                return $this->SendResponse(null, 'You have not blocked this user.', 400);
            }

            if(Auth::user()->id == $all['user_id']){
                return $this->SendResponse(null, 'Invalid user id.', 400);
            }

            user_blocked_users::find($check->id)->update(['is_active' => 0, 'is_delete' => 1]);

            return $this->SendResponse(null, 'User unblocked successfully', 200);
        } catch (\Exception $e) {
            return $this->SendResponse(null, $e->getMessage(), 500);
        }
    }

}
