<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_transactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'amount', 'is_success', 'transaction_id', 'response', 'is_active', 'is_delete', 'card_brand', 'card_last_four', 'card_name', 'message', 'log_id', 'resource_id'
    ];

}
