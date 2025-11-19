<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_page_visit_logs extends Model
{
    use HasFactory;

    protected $fillable = [
        'views','type'
    ];
}
