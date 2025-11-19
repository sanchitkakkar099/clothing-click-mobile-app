<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class UpdateUserProfileRequest extends FormRequest
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
            'name'  => 'required|string|max:100',
            'email' => 'required|email|string|max:85|unique:admins,email,'.Auth::guard('admin')->user()->id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ];
    }
}
