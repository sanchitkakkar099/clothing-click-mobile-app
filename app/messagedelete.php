<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class messagedelete extends Model
{
    use HasFactory;
    protected $table = 'message_delete';
    protected $fillable = [
        'post_id','deleted_by','deleted_for','type'
    ];

    protected $casts = [
        'post_id' => 'integer',
        'deleted_by' => 'integer',
    ];

    public function post()
    {
        return $this->belongsTo('App\posts','post_id','id');
    }

    public function deletedby()
    {
        return $this->belongsTo('App\User','deleted_by','id');
    }

}
