<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class user_favourite_brands extends Model
{

    protected $fillable = [
        'user_id','brand_id','is_active'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_active' => 'integer',
        'brand_id' => 'integer',
    ];

    // public function brand()
    // {
    //     return $this->belongsTo('App\User','brand_id','id');
    // }

    //** start update by mahesh
    public function brand()
    {
        return $this->belongsTo('App\brand','brand_id','id');
    }    
    //** end

    public static function getUserBrand($id)
    {
        $data = [];
        $brands = Self::with('brand')->where('user_id',$id)->get();
        
        foreach($brands as $brand){
            array_push($data,[
                'brand_id' => $brand->brand_id,
                'name' => $brand->brand->name ?? null
            ]);
        }

        return $data;
    }

}
