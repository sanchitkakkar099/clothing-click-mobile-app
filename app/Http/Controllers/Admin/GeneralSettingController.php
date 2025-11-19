<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\general_settings;
use App\mobile_settings;
use Cache;
use Artisan;
use Validator;

class GeneralSettingController extends SystemAdminController
{
    var $breadcum = ['icon'=>'mdi mdi-settings','breadcum'=>['General Settings']];

    public function __construct()
    {
        parent::__construct();
    }
    
    public function index(Request $request)
    {
        $settings = general_settings::getList($request);
        $breadcum = $this->breadcum;
        $categories = general_settings::groupby('setting_group')->get();
        $imageOption = ['POST_TOP_PICK_COIN', 'POST_BUMP_COIN', 'POST_HIGHTLIGHT_COIN', 'POST_SALE_COIN', 'POST_PROFILE_PROMOTE_COIN','LANDINGPAGE_IMAGES',];        
        return view('admin.general_settings.general_settings',compact('settings','breadcum','categories', 'imageOption'));
    }

    public function update(Request $request)
    {
        try{
            $settings = $request->all();
            unset($settings['_token']);

            foreach($settings['general_settings'] as $key => $value){
                general_settings::find($key)->update(['value'=>$value]);
            }
            
            if (isset($settings['images']) && count($settings['images']) > 0) {
                foreach ($settings['images'] as $key => $image) {
                    $img    = null;
                    $newVal = UploadImage($image, '', '', '');
                    general_settings::find($key)->update(['photo' => $newVal]);
                }
            }            

            if(isset($settings['cached'])){
                foreach($settings['cached'] as $key => $value){
                    switch($value){
                        case 'cache':
                            Artisan::call('cache:clear');
                        break;
                        case 'view':
                            Artisan::call('view:clear');
                        break;
                        case 'route':
                            Artisan::call('route:clear');
                        break;
                        case 'config':
                            Artisan::call('config:clear');
                        break;
                    }
                }
            }
        
            Cache::forget('generalSettings');

            return redirect()->back()->with('success','Settings updated successfully.');
            
        }catch(\Exception $e){
            return redirect()->back()->with('error',$e->getMessage());
        }   
    }
    public function mobile_settings(Request $request)
    {
        $settings = mobile_settings::find(1);
        $breadcum = 'Mobile app settings';
		if(isset($_POST['update'])){
			$all = $request->all();
			$validator = Validator::make($request->all(), [
				'type' => 'required',
                'file' => 'nullable|mimetypes:video/mp4, video/x-flv,video/3gpp,video/quicktime,video/x-msvideo,application/x-mpegURL,image/png,image/jpg,image/jpeg|max:512000000',
				
            ]);
			if(isset($all['file']) && ($all['file']->getMimeType() == 'video/mp4' || $all['file']->getMimeType() == 'video/quicktime'|| $all['file']->getMimeType() == 'video/x-flv'|| $all['file']->getMimeType() == 'video/3gpp'|| $all['file']->getMimeType() == 'x-msvideo'))
			{
				$fileId = UploadVideo($all['file'],'mobile','landingPage');
				$type='video';
			}else{ 
                $fileId = UploadImage($all['file'],'mobile','landingPage');
				$type='image';
            }
			mobile_settings::find(1)->update(['url' => $fileId,'type'=>$type]);
			return redirect()->back()->with('success','Settings updated successfully.');
		}
		if($settings->type == "video"){
			$file  = getVideoUrl($settings->url);
			//print_r($file);exit;
		}else{
			 $file = ResizeImage($settings->url,500,500);
		}

        return view('admin.general_settings.mobile_settings',compact('settings','file', 'breadcum'));
    }

    
}
