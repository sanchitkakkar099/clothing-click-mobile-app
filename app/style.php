<?php



namespace App;



use Illuminate\Database\Eloquent\Model;


use App\posts;

class style extends Model

{



    protected $fillable = [

        'name','is_active','is_delete'

    ];



    protected $casts = [

        'is_active' => 'integer',

        'is_delete' => 'integer'

    ];

    
public function posts()
{
    return $this->hasMany(posts::class, 'style_id');
}
}

