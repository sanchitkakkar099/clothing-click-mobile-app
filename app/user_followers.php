<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_followers extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','follower_user_id','is_active','is_delete','is_accepted'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_active' => 'integer',
        'follower_user_id' => 'integer',
        'is_delete' => 'integer',
        'is_accepted' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo('App\User','follower_user_id','id');
    }
}
