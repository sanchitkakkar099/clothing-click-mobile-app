<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_location_payment_logs extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'location_id', 'amount', 'start_at', 'end_at', 'is_active', 'is_delete'
    ];

}
