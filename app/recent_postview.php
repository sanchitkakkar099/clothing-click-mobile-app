<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\size;
use App\store_details;
use App\location;
use App\images;
use App\messagedelete;
use App\color;
use App\User;
use App\category;use App\style;
use DB;
use Auth;

class recent_postview extends Model
{

    protected $fillable = [
        'user_id', 'post_id','created_at','updated_at'
    ];
	
}