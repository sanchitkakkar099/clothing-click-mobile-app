<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_suggestions extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'message'
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];
}
