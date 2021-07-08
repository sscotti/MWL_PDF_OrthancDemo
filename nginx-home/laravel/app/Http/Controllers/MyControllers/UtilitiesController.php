<?php

namespace App\Http\Controllers\MyControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendReportAsPDF;

class UtilitiesController extends Controller

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
    protected function getPDFfromBody(Request $request) {
    
        // "markup":"", "extra":"report","filename":"RadiologyReport.pdf","disposition":"inline","_token":"UgMy16XElwjhBp5kZMvOGyPpB5pz27iQlKH1J1IA"}
        // Beginning of function, using the https://github.com/barryvdh/laravel-snappy package
        $extra = '';
        $header = Facility::letterHeader(env('DEFAULT_FACILITY_ID'), true);
        if ($request->input('extra') == 'report') $extra = self::reportCSS();
        $snappy = \App::make('snappy.pdf');
        header("Content-type:application/pdf");
        header("Content-Disposition:" . $_POST['disposition']  . ";filename='" . $request->input('filename') . "'");
        echo $snappy->getOutputFromHtml($header . $extra . $request->input('markup'));

    }
    
    protected static function reportCSS() {
    
        return '<style>#reportnoheader * {font-family: Tahoma, Geneva, sans-serif;}.htmlmarkup, #reportswrapper > div {padding:10px;margin:0px;background: white;color: #000;;font-size: 12px;font-weight:bold;}#markupform .htmlmarkup {background:black !important;color:white;}.htmlmarkup div, #reportswrapper > div div {display:block;padding:0px;line-height: initial;margin:5px 0px 5px 0px;}.htmlmarkup label, #reportswrapper > div label{font-size: 14px;color:#000;font-weight:bold;padding-right:10px;}.htmlmarkup section > header, #reportswrapper > div section > header{color: #000;font-size: 16px;font-weight: bold;margin-bottom: 0.0cm;margin-top: 0.3cm;}.htmlmarkup section > section > header, #reportswrapper > div section > section > header{color: #000;font-size: 12px;font-weight: bold;margin-bottom: 0.0cm;margin-top: 0.3cm;text-align: left;}.htmlmarkup section > section > section > header, #reportswrapper > div section > section > section > header{color: #000;font-size: 12px;font-weight: bold;margin-bottom: 0.0cm;margin-top: 0.3cm;text-align: left;}.htmlmarkup > section{}.htmlmarkup section > section, #reportswrapper > div section > section{padding-left: 0.8cm;}.htmlmarkup p, #reportswrapper > div p{margin-bottom: 0.0cm;margin-top: 0.0cm;padding-left: 0.8cm;}reportswrapper {width:100%;}#header_info {margin: 20px auto 10px auto;width:100%;;}#header_info, #header_info td {border: 1px solid black;border-collapse: collapse;background:#DDD;font-size: 12px;font-weight: bold;padding: 2px 5px 2px 5px;}#header_info tr:nth-child(even) td {background:#FFF !important;}#disclaimer {margin:20px 10px 0px 10px;text-align: justify;font-size: 8px;}#header_info > tbody > tr > td:first-child {width:350px;}#header_info > tbody > tr > td:nth-child(2){width:250px;}#header_info > tbody > tr > td:nth-child(3){width:190px;}.htmlmarkup, #reportswrapper {width:800px}#reportbody{font-size:12px;width: 90%;word-wrap: break-word;}#sigblock{margin-top:10px;}#apiresults {line-height: normal;font-size: 16px;color: black;background: #FFF;border-radius: 20px;padding: 20px 10px 20px 10px;border: 2px solid black;width:816px;}</style>';
    
    }
    
    protected static function emailReport(Request $request) {
    
        //
        $header = Facility::letterHeader(env('DEFAULT_FACILITY_ID'), true);
        $extra = self::reportCSS();
        $snappy = \App::make('snappy.pdf');
        $pdf = $snappy->getOutputFromHtml($header . $extra . $request->input('markup'));
        $bcc = env('MAIL_BCC_EMAIL');
        try {
            Mail::to($request->input('email'))->bcc($bcc)->send(new SendReportAsPDF($pdf));
            echo '{"error":false,"message":"Success"}';
        }
        catch (\Exception $e) {
            //var_dump($e);
            echo '{"error":true,"message":'.json_encode($e->getMessage()) .'}';
        }
    }
}