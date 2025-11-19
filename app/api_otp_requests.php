<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class api_otp_requests extends Model
{
    protected $fillable = [
        'user_id', 'otp','phone','country_code','country_prefix'
    ];
}
