<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\category;

class post_categories extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id', 'category_id', 'is_active', 'is_delete'
    ];

    protected $casts = [
        'category_id' => 'integer',
        'is_active' => 'integer',
        'is_delete' => 'integer',
        'post_id' => 'integer',
    ];
    
    public function category(){
        return $this->belongsTo(category::class)->where('parent_id',0);
    }    
}
