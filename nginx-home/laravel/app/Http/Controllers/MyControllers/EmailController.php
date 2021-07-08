<?php

namespace App\Http\Controllers\MyControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\DatabaseFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactUsAll;

/*
https://github.com/google/recaptcha/blob/master/README.md
<script src="https://www.google.com/recaptcha/api.js"></script>
'recaptcha' => [
    'sitekey' => env('RECAPTCHA_SITEKEY'),
    'secret' => env('RECAPTCHA_SECRET'),
    'siteverify' => env('RECAPTCHA_VERIFY'),
],
siteverify=https://www.google.com/recaptcha/api/siteverify
These are in the .env file

*/

class EmailController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
    }
    protected function checkcaptcha(Request $request) {
    
        $scorecheck = 0.3;
        $url = config('services.recaptcha.siteverify');
        $remoteip = $_SERVER['REMOTE_ADDR'];
        $data = [
                'secret' => config('services.recaptcha.secret'),
                'response' => $request->get('recaptcha'),
                'remoteip' => $remoteip
              ];
        $options = [
                'http' => [
                  'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                  'method' => 'POST',
                  'content' => http_build_query($data)
                ]
            ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $resultJson = json_decode($result);
        Log::info($result);

        if ($resultJson->success != true) {
        
            // return back()->withErrors(['captcha' => 'ReCaptcha Error']);
            echo '{"message":' . json_encode($resultJson) . '}';
        }
        else if ($resultJson->score >= $scorecheck) {
        
            //Validation was successful, add your form submission logic here
            // return back()->with('message', 'Thanks for your message!');
            Log::info($scorecheck);
            // echo '{"error":false,"message","ReCaptcha Score > ' . $scorecheck . '"}';
            try {
                $bcc = env('MAIL_BCC_EMAIL');
                Mail::to($request->input('email'))->bcc($bcc)->send(new ContactUsAll());
                echo '{"error":false,"message":"Success"}';
            }
            catch (\Exception $e) {
                //var_dump($e);
                echo '{"error":true,"message":'.json_encode($e->getMessage()) .'}';
            }
        } 
        
        else {
            // return back()->withErrors(['captcha' => 'ReCaptcha Error']);
            echo '{"error":true,"message","ReCaptcha Error, Failed Score"}';
        }
    }
}