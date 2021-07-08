<?php

namespace App\Http\Controllers\MyControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Orders\Orders;
use App\Models\Patients\Patients;
use Illuminate\Support\Facades\Log;

class OrdersController extends Controller
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
    protected function orderform(Request $request) {

        return view('orders/orderform', ["patient" => Patients::where('mrn', $request->input('mrn'))->get()[0], "order" => new Orders(), "mrn" => $request->input('mrn'),  "ordertype" => $request->input('ordertype'), "fromcalendar" => false]);
    }

//     protected function patient_search (Request $request) {
// 
// 
//         return view('patients/search', ["patients" => Patients::paginate(2)]);
//     }
// 
//     protected function patient_history (Request $request) {
// 
//         $patient = Patients::where("mrn", $request->input('id'))->first();
//         return view('patients.history', ["patient" => $patient]);
//     }
}
