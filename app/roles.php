<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class roles extends Model
{
    protected $fillable = [
        'name', 'is_active'
    ];

    public static function getList()
    {
        if(Auth::guard('admin')->user()->role_id != 1){
            return Self::where([['is_active',1],['id','!=',1],['id','!=',2]])->get();
        }
        return Self::all();
    }

    public function adminUser()
    {
        return $this->hasMany('App\admins','role_id');
    }

    public function adminPermission()
    {
        return $this->belongsTo('App\role_permissions','role_id');
    }
}
