<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class email_otp_requests extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','otp'
    ];
}
