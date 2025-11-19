<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class coins extends Model
{
    
    protected $fillable = [
        'name','photo','price','coins','is_active','is_delete'
    ];

    protected $casts = [
        'is_active' => 'integer',
        'is_delete' => 'integer'
    ];

    public static function getList() {
        return Self::all();
    }
    
}
