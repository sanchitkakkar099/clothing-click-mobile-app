<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_reported_users extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'message', 'image','reported_user_id', 'is_active', 'is_delete'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'reported_user_id' => 'integer',
        'is_active' => 'integer',
        'is_delete' => 'integer',
    ];
	public function user()
    {
        return $this->belongsTo('App\User','reported_user_id','id');
    }
}
