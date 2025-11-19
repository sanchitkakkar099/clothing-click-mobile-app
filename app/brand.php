<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class brand extends Model
{

    protected $fillable = [
        'name','photo','user_id','is_active','is_delete'
    ];

    protected $appends = ['image'];
    public static function getAppBrands()
    {
        $brands = Self::where([['is_active',1],['is_delete',0]])->get();

        $res = [];
        if($brands->count() > 0){
            foreach($brands as $brand){
                array_push($res,[
                    'photo' => ResizeImage($brand->photo,500,500)
                ]);
            }
        }

        return $res;
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function getImageAttribute(){
        return ResizeImage($this->photo,500,500);
    }

    public function posts(){
        return $this->hasMany('App\posts');        
    }

    public static function getAPIStoreListData($brands)
    {
        $res = [];

        foreach($brands as $brand){
            if($brand->posts->count() > 0){
                array_push($res,[
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'user_image' => ResizeImage($brand->photo,500,500),
                    'total_post' => $brand->posts->count(),
                ]);                
            }

        }
		// ✅ sort by total_post in descending order
		usort($res, function ($a, $b) {
			return $b['total_post'] <=> $a['total_post'];
		});

        return $res;
    }
}
