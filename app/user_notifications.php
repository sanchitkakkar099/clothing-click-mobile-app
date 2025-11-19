<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_notifications extends Model
{
    use HasFactory;

    protected $fillable = [
        'type','user_id','resource_id','title','message','is_active','is_delete','resource_id_two','is_read'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'resource_id' => 'integer',
        'is_active' => 'integer',
        'is_delete' => 'integer',
        'resource_id_two' => 'integer',
        'is_read' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo('App\User','user_id','id');
    }
    
    public function user_resource()
    {
        return $this->belongsTo('App\User','resource_id','id');
    }    

}
