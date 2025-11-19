<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_post_promotion_logs extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','post_id','promotion_type'
    ];
}
