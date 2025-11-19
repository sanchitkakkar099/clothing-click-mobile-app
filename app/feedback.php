<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class feedback extends Model
{

    protected $fillable = [
        'message','photo','sender_user_id','is_read'
    ];
	public function sender()
    {
        return $this->belongsTo('App\User','sender_user_id','id')->where('is_active',1)->where('is_delete',0);
    }

    public function receiver()
    {
        return $this->belongsTo('App\User','receiver_user_id','id')->where('is_active',1)->where('is_delete',0);
    }

    

}
