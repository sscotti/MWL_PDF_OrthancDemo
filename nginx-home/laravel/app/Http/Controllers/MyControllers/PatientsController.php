<?php

namespace App\Http\Controllers\MyControllers;
use Illuminate\Support\Facades\Auth;
use \DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Patients\Patients;
use App\Models\Patients\Contacts;
use App\Models\Patients\Employers;
use App\Models\Patients\Insurance;
use App\Models\Exams\Exams;
use Illuminate\Support\Facades\Log;
use DataTables;

class PatientsController extends Controller
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
    
    protected function showinsurance(Request $request) {
        $insurance = Insurance::where(["mrn" => $request->input('mrn'), "ins_id" => $request->input('ins_id') ])->first();
        return view('patients.showinsurance', ["data" => $insurance]);
    }
    
    
    protected function updateprofileitem(Request $request) {
    
        $value = $request->input('propertyvalue');
        if ($value == "true") $value = 1;
        if ($value == "false") $value = 0;
        $result = Patients::where($request->input('key'), $request->input('keyvalue'))->update([$request->input('propertyname') => $value]);
        echo '{"status":' . $result  . '}';
    }
    
    protected function showhistory(Request $request) {
    
        $patient = Patients::where("mrn", $request->input('mrn'))->first();
        if(empty($patient)) die('{"error":"No EMR Record for that MRN"}');
        return view('patients.history', ["patient" => $patient]);
    }
    
    protected function showcontacts(Request $request) {
    
        $contacts = Contacts::where("mrn", $request->input('mrn'))->first();
        if (!$contacts) return "No Contact on File.";
        return view('patientportal/showcontacts', ["contact" => $contacts]);

    }    
    
    protected function showemployer(Request $request) {
    
        $employer = Employers::where("mrn", $request->input('mrn'))->first();
        if (!$employer) return "No Employer on File.";
        return view('patientportal/showemployer', ["employer" => $employer]);
    }        
    
    protected function showpreferences(Request $request) {
    
        return "Not yet implemented";
    }    
    protected function editpreferences(Request $request) {
    
        $value = $request->input('propertyvalue');
        if ($value == "true") $value = 1;
        if ($value == "false") $value = 0;
        $result = Patients::where($request->input('key'), $request->input('keyvalue'))->update([$request->input('propertyname') => $value]);
        echo '{"status":' . $result  . '}';
    }          
    
    
    protected function patient_list(Request $request) {

        return view('patients/patients', ["patients" => Patients::paginate(3)]);
    }

    protected function patient_search (Request $request) {


        return view('patients/search', ["patients" => Patients::paginate(3)]);
    }

    protected function patient_history (Request $request) {

        $patient = Patients::where("mrn", $request->input('id'))->first();
        if(empty($patient)) die('{"error":"No EMR Record for that MRN"}');
        return view('patients.history', ["patient" => $patient]);
    }
    
    protected static function getExamLabel($requested_procedure_id) {
    
        $exam = Exams::where('requested_procedure_id', $requested_procedure_id)->first();
        return html_entity_decode($exam->exam_name) . ' - ' . $exam->linked_exams;
    
    }
    
    public function ordersdatatable(Request $request) {

            $query = 'SELECT * from orders o1 WHERE patientid = ? AND timestamp = (SELECT MAX(timestamp) FROM orders o2 WHERE o1.accession_number = o2.accession_number)';
            $params = [Auth::user()->patientid];
            $data = DB::connection('mysql2')->select($query,$params);

        if ($request->ajax()) {
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('patient_birth_date', function ($orderrequest) {
                   return $orderrequest->patient_birth_date;
                })
                ->editColumn('requested_procedure_id', function ($orderrequest) {
                   return self::getExamLabel($orderrequest->requested_procedure_id);
                })
                ->editColumn('timestamp', function ($orderrequest) {
                   return $orderrequest->timestamp;
                })
                ->editColumn('scheduled_procedure_step_start_date', function ($orderrequest) {
                    return substr($orderrequest->scheduled_procedure_step_start_date,0,10);
                    //return $orderrequest->scheduled_procedure_step_start_date->format('Y-m-d');
                })
//                 ->addColumn('action', function($row){
//                     $actionBtn = '<div data-id="' .$row->id . '"><button class="editpatient btn btn-success btn-sm">Details</button></div>';
//                     return $actionBtn;
//                 })
//                 ->rawColumns(['action'])
                ->make(true);
        }
    
    
    }

    public function datatable(Request $request)
    
    {
        if ($request->ajax()) {
            $data = Patients::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $actionBtn = '<div data-mrn="' .$row->mrn . '"><button class="editpatient btn btn-success btn-sm">Edit</button><button class="showpatient_studies btn btn-success btn-sm">Studies</button><button class="show_patient_orders btn btn-success btn-sm">Orders</button><button class="place_patient_order btn btn-success btn-sm">Place Order</button><button class="upload_patient_study btn btn-success btn-sm">Upload Study</button></div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    
}
