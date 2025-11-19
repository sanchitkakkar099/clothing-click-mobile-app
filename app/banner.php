<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class banner extends Model
{

    protected $fillable = [
        'photo','is_active','is_delete'
    ];

    public static function getAppBanners()
    {
        $banners = Self::where([['is_active',1],['is_delete',0]])->get();

        $res = [];
        if($banners->count() > 0){
            foreach($banners as $banner){
                array_push($res,[
                    'photo' => ResizeImage($banner->photo,500,500)
                ]);
            }
        }

        return $res;
    }

    
}
