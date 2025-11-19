<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class general_settings extends Model
{
    protected $fillable = [
        'value','photo'
    ];

    public static function getList($cat = false)
    {
        if(!$cat){
            return Self::where('is_active',1)->orderBy('setting_group','ASC')->get();
        }
        return Self::where([['is_active',1],['setting_group',$cat->category]])->orderBy('setting_group','ASC')->get();
    }
    
     public static function getPrice($name_slug)
    {
        return Self::where([['is_active',1],['name_slug',$name_slug]])->orderBy('setting_group','ASC')->first();
    }
}
