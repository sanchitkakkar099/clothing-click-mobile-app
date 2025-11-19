<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\color;
use App\category;
use App\size;

class user_saved_searches extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'is_mysize', 'seller', 'gender_id', 'brand_id', 'condition_id', 'sizes', 'categories', 'colors', 'distance', 'price_type', 'price_from', 'price_to', 'notification_item_counter', 'is_active', 'is_delete','matched_product_count','style'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_mysize' => 'integer',
        'gender_id' => 'integer',
        'brand_id' => 'integer',
        'condition_id' => 'integer',
        'price_type' => 'integer',
        'notification_item_counter' => 'integer',
        'is_active' => 'integer',
        'is_delete' => 'integer',
    ];

    public function getSeller()
    {
        return $this->belongsTo('App\front_roles','seller','id');
    }

    public function gender()
    {
        return $this->belongsTo('App\gender','gender_id','id');
    }

    // public function brand()
    // {
    //     return $this->belongsTo('App\User','brand_id','id');
    // }

    //*** start update by mahesh
    public function brand()
    {
        return $this->belongsTo('App\brand','brand_id','id');
    }
    //*** end   

    public function condition()
    {
        return $this->belongsTo('App\condition','condition_id','id');
    }
    public function style_details()
    {
        return $this->belongsTo('App\style','style','id');
    }

    public function getPriceType()
    {
        return $this->belongsTo('App\price_types','price_type','id');
    }

    public function getSelectedColors()
    {
        if(!empty($this->colors)){
            return color::wherein('id',explode(',',$this->colors))->where([['is_active',1],['is_delete',0]])->select('id','name')->get();
        }
        return [];
    }

    public function getSelectedCategories()
    {
        if(!empty($this->categories)){
            return category::wherein('id',explode(',',$this->categories))->where([['is_active',1],['is_delete',0]])->select('id','name','parent_id')->get();
        }
        return [];
    }

    public function getSelectedSizes()
    {
        if(!empty($this->sizes)){
            return size::wherein('id',explode(',',$this->sizes))->where([['is_active',1],['is_delete',0]])->select('id','name')->get();
        }
        return [];
    }
    

    
}
