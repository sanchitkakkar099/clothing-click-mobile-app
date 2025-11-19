<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\email_logs;

class LogSentMessage
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        try{       
            $body = NULL;
            $data = [];
            if(isset($event->data)){
                $data = $event->data;
            }
            if(isset($event->data['message'])){
                $body = $event->data['message']->getBody();
            }
           
            email_logs::create([
                'subject' => (isset($data['mailSubject'])?$data['mailSubject']:NULL),
                'mail_to' => (isset($data['mailTo'])?$data['mailTo']:NULL),
                'mail_to_name' => (isset($data['mailToName'])?$data['mailToName']:NULL),
                'mail_body' => $body
            ]);
        }catch(\Exception $e){

        }
        
    }
}
