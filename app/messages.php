<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class messages extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_user_id','receiver_user_id','post_id','message','is_read','is_active','is_delete','photo','video','thumbnail'
    ];

    protected $casts = [
        'sender_user_id' => 'integer',
        'receiver_user_id' => 'double',
        'is_active' => 'integer',
        'is_delete' => 'integer',
        'post_id' => 'integer',
        'is_read' => 'integer',
    ];

    public function post()
    {
        return $this->belongsTo('App\posts','post_id','id');
    }

    public function sender()
    {
        return $this->belongsTo('App\User','sender_user_id','id')->where('is_active',1)->where('is_delete',0);
    }

    public function receiver()
    {
        return $this->belongsTo('App\User','receiver_user_id','id')->where('is_active',1)->where('is_delete',0);
    }

}
