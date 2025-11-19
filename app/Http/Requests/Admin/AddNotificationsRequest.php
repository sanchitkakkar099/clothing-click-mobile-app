<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddNotificationsRequest extends FormRequest
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
            'header' => 'required|string|max:155',
            'show_notificatoin_log' => 'required',            
            'send_notification_to' => 'required',
            'custom_user_ids' => 'required_if:send_notification_to,other',
        ];
    }
}
