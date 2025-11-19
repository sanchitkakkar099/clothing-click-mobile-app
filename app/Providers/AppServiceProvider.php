<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\general_settings;
use Config;
use Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        
        //Set Constant
        $settings = Cache::get('generalSettings');
        if(empty($settings)){
            $settings = general_settings::all();
            Cache::forever('generalSettings',$settings);
        }
        
        foreach($settings as $setting){
            Config::set('constants.'.$setting->name_slug, $setting->value);
            if($setting->photo !=null){
                Config::set('constants_image.'.$setting->name_slug, ResizeImage($setting->photo,500,500));
            }            
        }
        
        Config::set('app.name',Config('constants.SITE_NAME'));
        Config::set('app.timezone',Config('constants.TIMEZONE'));

        Config::set('mail.driver',Config('constants.MAIL_DRIVER'));
        Config::set('mail.host',Config('constants.MAIL_HOST'));
        Config::set('mail.port',Config('constants.MAIL_PORT'));
        Config::set('mail.from.address',Config('constants.MAIL_FROM_EMAIL'));
        Config::set('mail.from.name',Config('constants.MAIL_FROM'));
        Config::set('mail.encryption',Config('constants.MAIL_ENCRYPTION'));
        Config::set('mail.username',Config('constants.MAIL_USERNAME'));
        Config::set('mail.password',Config('constants.MAIL_PASSWORD'));
    }
}
