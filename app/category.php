<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class category extends Model
{
    
    protected $fillable = [
        'gender_id','parent_id','name','photo','icon','is_active','is_delete'
    ];

    protected $casts = [
        'gender_id' => 'integer',
        'parent_id' => 'integer',
        'is_active' => 'integer',
        'is_delete' => 'integer'
    ];

    public static function getList() {
        return Self::all();
    }
    
    public static function getCategory($id)
    {
        return category::find($id);
    }
    
    public static function getParentList()
    {
        return Self::where('parent_id',0)->get();
    }

    public static function getParentCategory($id)
    {
        return Self::where('id',$id)->first();
    }

    public function gender()
    {
        return $this->belongsTo('App\gender','gender_id','id');
    }

    public function childCategories()
    {
        return $this->hasMany('App\category', 'parent_id', 'id')->where([['is_active',1],['is_delete',0]])->select('id','name','parent_id')->with('childCategories');
    }
    
    public static function getGenderCategories($genderId)
    {
        $res = [];
        $categories =  Self::where([['gender_id',IN($genderId)],['parent_id',0],['is_active',1],['is_delete',0]])->orderby('id','asc')->get();
        foreach($categories as $category){
            array_push($res,[
                'category_id' => $category->id,
                'name' => $category->name,                
				'photo' => ResizeImage($category->photo,50,50),
                'childCategories' => $category->childCategories
            ]);
        }
        return $res;
    }
    
    public function parentCategories()
    {
        return $this->hasMany('App\category', 'id', 'parent_id')->where([['is_active',1],['is_delete',0]])->select('id','name','parent_id')->with('parentCategories');
    }        

}
