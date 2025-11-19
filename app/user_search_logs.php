<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_search_logs extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','log_type','resource_id'
    ];
}
