<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class modules extends Model
{
    public static function getList()
    {
        return Self::where('is_active',1)->get();
    }

    public function permissions()
    {
        return $this->hasMany('App\permissions','module_id');
    }
}
