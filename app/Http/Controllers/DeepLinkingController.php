<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class DeepLinkingController extends Controller
{
    public function share($type,$id)
    {
        
        $iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
        $iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
        $iPad    = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
        $Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");
        $webOS   = stripos($_SERVER['HTTP_USER_AGENT'],"webOS");

        $deviceType = 'android';
        
        switch($type){
            case 'post':

                $urlAndroid = "app://com.clothingclick.app/post/".$id;
                $urlIos = "Clothapp://post/".$id;
                if( $iPod || $iPhone || $iPad){
                    $deviceType = 'ios';
                }else{
                    $deviceType = 'android';
                }
                break;
            case 'profile':
                $urlAndroid = "app://com.clothingclick.app/user/".$id;
                $urlIos = "Clothapp://user/".$id;
                if( $iPod || $iPhone || $iPad){
                    $deviceType = 'ios';
                }else{
                    $deviceType = 'android';
                }
                break;
        }
        
        return view('deeplink',compact('deviceType','urlAndroid','urlIos'));
    }
    
}
