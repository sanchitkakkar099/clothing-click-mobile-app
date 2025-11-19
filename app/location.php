<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class location extends Model
{

    protected $table = 'location';
    protected $fillable = [
        'appuser_id','address','postal_code','latitude','longitude','location_ids','price','created_at','updated_at','is_delete','is_active','is_default','city','area','is_paid','expire_at'
    ];

    protected $casts = [
        'appuser_id' => 'integer',
        'price' => 'double',
        'is_active' => 'integer',
        'is_delete' => 'integer',
        'is_default' => 'integer',
        'is_paid' => 'integer',
        'expire_at' => 'datetime'
    ];

    
    public static function getLocationData($id)
    {
        return location::where([['appuser_id', $id],['is_delete',0],['is_active',1]])->get();
    }
}
