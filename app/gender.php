<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class gender extends Model {

    use HasFactory;

    protected $fillable = [
        'name', 'is_active'
    ];

    public static function getList() {
        return Self::all();
    }
    
     public static function getGender($id)
    {
        return gender::find($id);
    }

}
