<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\user_sizes;
use App\user_favourite_brands;
use App\posts;
use App\user_followers;
use DB;
use Auth;
use Carbon\Carbon;

class Store extends Authenticatable
{
    use HasApiTokens,Notifiable;

    protected $fillable = [
        'name', 'email','phone','is_active','user_id','address','website','message'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_active' => 'integer',
    ];

    
}
