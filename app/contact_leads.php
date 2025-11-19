<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class contact_leads extends Model
{
    protected $fillable = [
        'name','email','phone','subject','message'
    ];
}
