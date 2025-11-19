<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_cards extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'stripe_customer_id', 'stripe_card_id', 'name', 'brand', 'last_four', 'is_active', 'is_delete', 'exp_year', 'exp_month'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_active' => 'integer',
        'is_delete' => 'integer',
    ];

    public static function getCardDetail($card)
    {
        return [
            'id' => $card->id,
            'user_id' => $card->user_id,
            'name' => $card->name,
            'brand' => $card->brand,
            'last_four' =>$card->last_four,
            'exp_year' => $card->exp_year,
            'exp_month' => $card->exp_month,
            'created_at' => date("Y-m-d H:i:s",strtotime($card->created_at))
        ];
    }
}
