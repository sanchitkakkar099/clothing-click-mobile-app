<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class condition extends Model
{

    protected $fillable = [
        'name','is_active','is_delete'
    ];

    protected $casts = [
        'is_active' => 'integer',
        'is_delete' => 'integer'
    ];
    
}
