<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_notification_settings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'is_all', 'is_save_search', 'is_chat', 'is_follower', 'is_rating', 'is_watchlist', 'is_coin'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_all' => 'integer',
        'is_save_search' => 'integer',
        'is_chat' => 'integer',
        'is_follower' => 'integer',
        'is_rating' => 'integer',
        'is_watchlist' => 'integer',
        'is_coin' => 'integer',
    ];
}
