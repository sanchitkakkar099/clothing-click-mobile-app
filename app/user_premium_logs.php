<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_premium_logs extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'is_paid', 'amount', 'start_at', 'end_at', 'is_active', 'is_delete'
    ];

}
