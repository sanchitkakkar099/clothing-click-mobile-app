<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class color extends Model
{

    protected $fillable = [
        'name','colorcode','photo','is_active','is_delete'
    ];

    protected $casts = [
        'is_active' => 'integer',
        'is_delete' => 'integer'
    ];

}
