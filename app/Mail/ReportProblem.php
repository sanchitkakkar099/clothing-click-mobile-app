<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportProblem extends Mailable
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
        return $this->subject('Problem Report from Clothing App')
                    ->view('emails.report_problem');        
        // return $this->view('view.name');
    }
}
