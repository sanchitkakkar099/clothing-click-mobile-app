<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\user_sizes;
use App\user_favourite_brands;
use App\posts;
use App\User;
use App\user_followers;
use DB;
use Auth;
use Carbon\Carbon;

class store_details extends Authenticatable
{
    use HasApiTokens,Notifiable;

    protected $fillable = [
        'id', 'user_id', 'shopify_id', 'store_description', 'timings', 'address1', 'address2', 'city', 'country', 'cover_image', 'profile_image', 'total_products','postal_Code','latitude','longitude'
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    
}
