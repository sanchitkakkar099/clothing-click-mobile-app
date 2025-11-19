<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class email_logs extends Model
{
    protected $fillable = [
        'subject','mail_to','mail_to_name','mail_body'
    ];
}
