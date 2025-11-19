<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_addresses extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','address_type','order_id', 'first_name', 'last_name', 'country', 'company', 'address', 'address2', 'city', 'province', 'post_code', 'phone','created_at'
    ];

}
