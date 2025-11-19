<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class user_logindevice_info extends Model
{
    
    protected $table = 'user_logindevice_info';

    protected $fillable = [
        'appuser_id', 'devices_type', 'devices_token', 'devices_name', 'os', 'devices_id', 'app_version', 'access_token_id'
    ];

    protected $casts = [
        'appuser_id' => 'integer',
    ];

    public static function getDeviceData($id)
    {
        return user_logindevice_info::where('appuser_id', $id)->get();
    }

}
