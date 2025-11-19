<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class post_colors extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id', 'color_id', 'is_active', 'is_delete'
    ];

    protected $casts = [
        'color_id' => 'integer',
        'is_active' => 'integer',
        'is_delete' => 'integer',
        'post_id' => 'integer',
    ];
}
