<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class images extends Model
{
    protected $fillable = [
        'name', 'module', 'directory', 'is_admin'
    ];
}
