<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveSuggestion extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
//         Please send to support@clothingclick.online 
// And to matthewwintoniak@gmail.com 
        return $this->subject('New Suggestion from Clothing App')
                    ->view('emails.leave_suggestion');        
        // return $this->view('view.name');
    }
}
