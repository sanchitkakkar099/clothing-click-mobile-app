<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\SystemAdminController;
use App\User;
use App\category;
use App\gender;
use App\size;
use App\user_favourite_brands;
use App\user_sizes;
use Auth;
use Illuminate\Http\Request;
use Validator;

class MySizeController extends SystemAdminController
{
    public function getSize(Request $request)
    {
        try {
            $sizeRes = [];
            $genders = gender::where('is_active', 1)->get();
            foreach ($genders as $gender) {
                array_push($sizeRes, [
                    'gender_id'   => $gender->id,
                    'gender_name' => $gender->name,
                    'categories'  => $this->getGenderCategories($gender->id),
                ]);
            }

            return $sizeRes;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getGenderCategories($genderId)
    {
        $data = [];

        $categories = category::where([['gender_id', $genderId], ['is_active', 1], ['is_delete', 0]])->get();

        foreach ($categories as $category) {

            array_push($data, [
                'name'  => $category->name,
                'image' => ResizeImage($category->photo, 50, 50),
                'sizes' => $this->getCategorySize($category->id),
            ]);

        }

        return $data;
    }

    public function getCategorySize($catId)
    {
        $data  = [];
        $sizes = size::where([['category_id', $catId], ['is_active', 1], ['is_delete', 0]])->get();

        foreach ($sizes as $size) {
            array_push($data, [
                'id'   => $size->id,
                'name' => (string) $size->name,
            ]);
        }

        return $data;
    }
}
