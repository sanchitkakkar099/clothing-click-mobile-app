<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class permissions extends Model
{
    public function module()
    {
        return $this->belongsTo('App\modules','id');
    }

    public function role_permission()
    {
        return $this->hasMany('App\role_permissions','permission_id');
    }
}
