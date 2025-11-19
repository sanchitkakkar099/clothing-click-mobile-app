<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\gender;
use App\category;

class size extends Model
{
    
    protected $fillable = [
        'category_id','gender_id','name','is_active','is_delete'
    ];

    protected $casts = [
        'category_id' => 'integer',
        'gender_id' => 'integer',
        'is_active' => 'integer',
        'is_delete' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo('App\category','category_id','id');
    }

    public static function getSizes()
    {
        $sizeRes = [];

        $genders = gender::where('is_active',1)->get();

        foreach($genders as $gender){

            array_push($sizeRes,[
                'gender_id' => $gender->id,
                'gender_name' => $gender->name,
                'categories' => $this->getGenderCategories($gender->id)
            ]);

        }

        return $sizeRes;
    }

    
    
}
