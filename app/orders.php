<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class orders extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id', 'buyer_id', 'product_id','currency', 'tax', 'total', 'payment_mode', 'transaction_id', 'status', 'shipping_address_id','billing_address_id','type_of_order','shopify_order_id','shopify_order_number','variant_id'
    ];

    // Sent to the app with every order: pickup progress worked out from the order time.
    protected $appends = ['pickup_status', 'processing_at', 'pickup_at'];

    // Pickup orders: "purchased" for the first 2 hours, then "processing", and "pickup"
    // (ready to collect) 3 hours after the order. Null for delivery orders.
    const PROCESSING_AFTER_HOURS = 2;
    const PICKUP_AFTER_HOURS = 3;

    public function getProcessingAtAttribute()
    {
        return ($this->type_of_order == 2 && $this->created_at)
            ? $this->created_at->copy()->addHours(self::PROCESSING_AFTER_HOURS)->toDateTimeString()
            : null;
    }

    public function getPickupAtAttribute()
    {
        return ($this->type_of_order == 2 && $this->created_at)
            ? $this->created_at->copy()->addHours(self::PICKUP_AFTER_HOURS)->toDateTimeString()
            : null;
    }

    public function getPickupStatusAttribute()
    {
        if ($this->type_of_order != 2 || !$this->created_at) {
            return null;
        }
        $hours = $this->created_at->diffInMinutes(now()) / 60;
        if ($hours >= self::PICKUP_AFTER_HOURS) {
            return 'pickup';
        }
        return $hours >= self::PROCESSING_AFTER_HOURS ? 'processing' : 'purchased';
    }

}
