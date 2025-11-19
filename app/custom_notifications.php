<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class custom_notifications extends Model
{

    protected $fillable = [
        'user_id','show_notificatoin_log','display_on_screen','send_notification_to','custom_user_ids','photo','header','subtext','is_active','is_delete'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'show_notificatoin_log' => 'boolean',
        'display_on_screen' => 'boolean',        
        'is_active' => 'integer',
        'is_delete' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo('App\User','user_id','id');
    }

    public function getImageAttribute(){
        if($this->photo !=null){
        return ResizeImage($this->photo,500,500);            
        }
        return false;

    }
}
