<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_reviews extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','rating','review','is_active','is_delete','review_by','review_images'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'rating' => 'double',
        'is_active' => 'integer',
        'is_delete' => 'integer',
        'review_by' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo('App\User','user_id','id');
    }

    public function reviewBy()
    {
        return $this->belongsTo('App\User','review_by','id');
    }

}
