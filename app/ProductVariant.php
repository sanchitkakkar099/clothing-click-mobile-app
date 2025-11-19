<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\posts;
use Auth;

class ProductVariant extends Model
{
    protected $fillable = [
        'post_id', 'shopify_variant_id', 'title', 'sku', 'price',
        'compare_at_price', 'inventory_quantity', 'barcode',
        'color', 'size', 'option3','image'
    ];

    // A ProductVariant belongs to a Post
    public function post()
    {
        return $this->belongsTo(posts::class, 'post_id');
    }

   
}

    

