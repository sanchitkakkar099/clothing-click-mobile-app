<?php

namespace App;
use App\User;
use Illuminate\Database\Eloquent\Model;

class code extends Model
{

    protected $fillable = [
        'name','is_active','is_delete','limit'
    ];

    protected $casts = [
        'is_active' => 'integer',
        'is_delete' => 'integer',
        'limit' => 'integer'        
    ];
    
    public function users(){
        return $this->hasMany(User::class,'access_code','name');
    }     
    
}
