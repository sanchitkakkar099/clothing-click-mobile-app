<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class orders extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id', 'buyer_id', 'product_id','currency', 'tax', 'total', 'payment_mode', 'transaction_id', 'status', 'shipping_address_id','billing_address_id','type_of_order','shopify_order_id'
    ];

}
