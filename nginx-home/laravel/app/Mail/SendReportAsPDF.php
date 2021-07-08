<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;

class SendReportAsPDF extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    private $pdf;
    
    public function __construct($pdf)
    {
        //
        $this->pdf = $pdf;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(Request $request)
    {   
        $from = env('MAIL_FROM_ADDRESS');
        return $this->from($from)->subject('Radiology Report Request')->view('emails.sendreport')->attachData($this->pdf, $request->input('mrn'), ['mime' => 'application/pdf'])->with("data",$request->all());
    
    }
}
