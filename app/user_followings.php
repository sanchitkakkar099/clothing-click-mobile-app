<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_followings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','following_user_id','is_active','is_delete'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_active' => 'integer',
        'following_user_id' => 'integer',
        'is_delete' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo('App\User','following_user_id','id');
    }
}
