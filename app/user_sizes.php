<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class user_sizes extends Model
{

    protected $fillable = [
        'user_id','size_id','is_active'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'size_id' => 'integer',
        'is_active' => 'integer',
    ];

    public function size()
    {
        return $this->belongsTo('App\size','size_id','id');
    }

}
