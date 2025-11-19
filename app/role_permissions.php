<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class role_permissions extends Model
{
    protected $fillable = [
        'role_id', 'permission_id'
    ];

    public function adminRole()
    {
        return $this->hasOne('App\roles','id');
    }

    public function adminPermission()
    {
        return $this->belongsTo('App\permissions','id');
    }
}
