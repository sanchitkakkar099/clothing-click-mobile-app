<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_favourite_posts extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','post_id','is_active', 'is_delete'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_active' => 'integer',
        'post_id' => 'integer',
        'is_delete' => 'integer',
    ];
}
