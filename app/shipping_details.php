<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shipping_details extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'shipping_provider', 'tracking_number', 'address', 'status',
    ];

}
