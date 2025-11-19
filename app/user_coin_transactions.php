<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_coin_transactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','type','is_active','is_delete','is_credit','amount','opening_balance','closing_balance','resource_id','description'
    ];
}
