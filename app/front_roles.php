<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class front_roles extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];
}
