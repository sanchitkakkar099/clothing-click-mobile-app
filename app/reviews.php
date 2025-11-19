<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reviews extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','rating','post_id','review','is_active','is_delete','review_by'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'rating' => 'double',
        'post_id' => 'integer',
        'is_active' => 'integer',
        'is_delete' => 'integer',
        'review_by' => 'integer',
    ];

    public function post()
    {
        return $this->belongsTo('App\posts','post_id','id');
    }

    public function user()
    {
        return $this->belongsTo('App\User','user_id','id');
    }

    public function reviewBy()
    {
        return $this->belongsTo('App\User','review_by','id');
    }

}
