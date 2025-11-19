<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddAdminUserRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'role_id' => 'required|string|max:55',
            'name' => 'required|string|max:155',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users',            
            'phone' => 'required|string|max:10|unique:users',            
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|max:55|confirmed',
            'city' => 'required',
        ];
    }
}
