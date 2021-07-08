<?php

namespace App\Http\Controllers\MyControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Referrers\ReferringPhysician;
use App\Helpers\DatabaseFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Orders\OrdersRequests;
use App\Models\Orders\Orders;
use App\Models\Exams\Exams;
use App\Models\Referrers\ProviderLicenses;
use DataTables;
use \DB;
use App\Actions\Orthanc\OrthancAPI;
use App\Models\Studies\SharedStudies;

class ReferringPhysicianController extends Controller
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
    protected function updateprofileitem(Request $request) {
    
        $value = $request->input('propertyvalue');
        if ($value == "true") $value = 1;
        if ($value == "false") $value = 0;
        $result = ReferringPhysician::where($request->input('key'), $request->input('keyvalue'))->update([$request->input('propertyname') => $value]);
        echo '{"status":' . $result  . '}';
    }
    
    protected function updatelicense(Request $request) {
    
        $result = ProviderLicenses::where($request->input('key'), $request->input('keyvalue'))->update([$request->input('propertyname') => $request->input('propertyvalue')]);
        echo '{"status":' . $result  . '}';
    }
    
    protected function addlicense(Request $request) {
    
        $newlicense = new ProviderLicenses;
        $newlicense->license_provider_id = $request->input('license_provider_id');
        $newlicense->license_provider_identifier = $request->input('identifier');
        $result = $newlicense->save();
        echo '{"status":' . $result  . '}';
    }
    
    protected function deletelicense(Request $request) {
    
        $result = ProviderLicenses::destroy($request->input('license_id'));
        echo '{"status":' . $result  . '}';
    }
    
    protected function listlicenses(Request $request) {
    
        return view('referrers.referrers_licenses', ["doctor_id" => $request->input('identifier')]);
    }
    
    protected function submitorderrequest (Request $request) {
        
    
        $data = $request->all();
        $provider = ReferringPhysician::where('identifier', $request->input('referring_physician_id'))->first();
        $data['active'] = 1;
        $data['orderedbyuser_id'] = Auth::user()->id;
        $data['orderbyuser_name'] = Auth::user()->name;
        $data['referring_physician_fname'] = $provider->fname;
        $data['referring_physician_lname'] = $provider->lname;
        $data['referring_physician_phone_ctry'] = $provider->mobile_phone_country;
        $data['referring_physician_phone'] = $provider->mobile_phone;
        $data['referring_physician_email'] = $provider->email;
        $data['provider_type_text'] = $provider->provider_type;
        // DatabaseFactory::insertarray('orders_requests', $data);
        // updated_at and created_at are added by Eloquent
        $orderrequest = OrdersRequests::create(array(
        
            'priority' => $data['priority'],
            'active' => 1,
            'patient_fname' => $data['patient_fname'],
            'patient_lname' => $data['patient_lname'],
            'patient_mname' => $data['patient_mname'],
            'patient_birth_date' => $data['patient_birth_date'],
            'patient_sex' => $data['patient_sex'],
            'patientid' => $data['patientid'],
            'patient_email' => $data['patient_email'],
            'patient_phone_ctry' => isset($data['patient_phone_ctry'])?$data['patient_phone_ctry']:null,
            'patient_phone' => $data['patient_phone'],
            'referring_physician_id' => $data['referring_physician_id'],
            'referring_physician_fname' => $data['referring_physician_fname'],
            'referring_physician_lname' => $data['referring_physician_lname'],
            'referring_physician_phone_ctry' => $data['referring_physician_phone_ctry'],
            'referring_physician_phone' => $data['referring_physician_phone'],
            'referring_physician_email' => $data['referring_physician_email'],
            'provider_type_text' => $data['provider_type_text'],
            'indication' => $data['indication'],
            'orderedbyuser_id' => $data['orderedbyuser_id'],
            'orderbyuser_name' => $data['orderbyuser_name'],
            'scheduled_procedure_step_start_date' => $data['scheduled_procedure_step_start_date'],
            'scheduled_procedure_step_start_time' => $data['scheduled_procedure_step_start_time'],
            'requested_procedure_id' => $data['requested_procedure_id'],
            'related_employment' => isset($data['related_employment'])?$data['related_employment']:null,
            'related_auto' => isset($data['related_auto'])?$data['related_auto']:null,
            'related_otheraccident' => isset($data['related_otheraccident'])?$data['related_otheraccident']:null,
            'related_emergency' => isset($data['related_emergency'])?$data['related_emergency']:null,
            'related_drugs' => isset($data['related_drugs'])?$data['related_drugs']:null,
            'related_pregnancy' => isset($data['related_pregnancy'])?$data['related_pregnancy']:null,
            'employed' => isset($data['employed'])?$data['employed']:null,
            'employed_student' => isset($data['employed_student'])?$data['employed_student']:null,
            'employed_other' => isset($data['employed_other'])?$data['employed_other']:null,
            'illness_date' => isset($data['illness_date'])?$data['illness_date']:null
        ));
        $result = isset($orderrequest->id)?"Request Added":"error";
        echo '{"message":"'. $result . '"}';
    }

    protected function patient_history (Request $request) {

        $patient = Patients::where("mrn", $request->input('id'))->first();
        return view('patients.history', ["patient" => $patient]);
    }
    
    protected function requests_datatable(Request $request) {
    
        if ($request->ajax()) {
            $data = OrdersRequests::latest()->where('active', 1)->where('referring_physician_id', Auth::user()->doctor_id)->get();
            
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('patient_birth_date', function ($orderrequest) {
                   return $orderrequest->patient_birth_date->format('Y-m-d');
                })
                ->editColumn('requested_procedure_id', function ($orderrequest) {
                   return self::getExamLabel($orderrequest->requested_procedure_id);
                })
                ->editColumn('created_at', function ($orderrequest) {
                   return $orderrequest->created_at->format('Y-m-d');
                })
//                 ->addColumn('action', function($row){
//                     $actionBtn = '<div data-id="' .$row->id . '"><button class="editpatient btn btn-success btn-sm">Details</button></div>';
//                     return $actionBtn;
//                 })
                ->rawColumns(['action'])
                ->make(true);
        }
    
    }

    protected function share(Request $request) {
        
        // DoctorsModel::shareStudy ($user->doctor_id, $request->input('identifier'), $request->input('uuid'), $request->input('sharenote'));
        
        $checkuuid = SharedStudies::where('uuid',$request->input('uuid'))->where('shared_with', $request->input('identifier'))->where('shared_by', Auth::user()->doctor_id)->get();
        if (count($checkuuid) > 0 ) {
            die ('{"error":"Already Shared that UUID with Target User."}');
        }
        else {
        
            $pacs = new OrthancAPI();
            $server = $pacs->server;
            $study = $pacs->getStudyDetails($request->input('uuid'));
            
            $shared = SharedStudies::create([
            
                'shared_by' => Auth::user()->doctor_id,
                'shared_with' => $request->input('identifier'),
                'share_note' => $request->input('sharenote'),
                'uuid' => $request->input('uuid'),
                'server' => json_encode($server),
                'patient_name' => $study->patient_name,
                'study_description' => $study->study_description,
                'study_date' => $study->study_date,
                'study_json' => json_encode($study)
            ]);
            
            echo '{"status":"Record Inserted"}';
        }
    }
    
    protected function placedorders_datatable(Request $request) {
    
        if ($request->ajax()) {
            $query = 'SELECT * from orders o1 WHERE referring_physician_id = ? AND timestamp = (SELECT MAX(timestamp) FROM orders o2 WHERE o1.accession_number = o2.accession_number)';
            $params = [Auth::user()->doctor_id];
            $data = DB::connection('mysql2')->select($query,$params);
            //$data = Orders::latest()->where('referring_physician_id', Auth::user()->doctor_id)->get();
            
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('patient_birth_date', function ($orderrequest) {
                   return $orderrequest->patient_birth_date;
                })
                ->editColumn('requested_procedure_id', function ($orderrequest) {
                   return self::getExamLabel($orderrequest->requested_procedure_id);
                })
                ->editColumn('timestamp', function ($orderrequest) {
                   return substr($orderrequest->timestamp,0,10);
                })
                ->editColumn('scheduled_procedure_step_start_date', function ($orderrequest) {
                    return substr($orderrequest->scheduled_procedure_step_start_date,0,10);
                    //return $orderrequest->scheduled_procedure_step_start_date->format('Y-m-d');
                })
                ->editColumn('ourstatus', function ($orderrequest) {
                    return '<span class = "uibuttonsmallred">' . $orderrequest->ourstatus . '</span>';
                    //return $orderrequest->scheduled_procedure_step_start_date->format('Y-m-d');
                })
//                 ->addColumn('action', function($row){
//                     $actionBtn = '<div data-id="' .$row->id . '"><button class="editpatient btn btn-success btn-sm">Details</button></div>';
//                     return $actionBtn;
//                 })
                ->rawColumns(['action'])
                ->escapeColumns([])
                ->make(true);
        }
    
    }
    
    protected static function getExamLabel($requested_procedure_id) {
    
        $exam = Exams::where('requested_procedure_id', $requested_procedure_id)->first();
        return html_entity_decode($exam->exam_name) . ' - ' . $exam->linked_exams;
    
    }
    
    protected function shared_studies(Request $request) {
    
        $user = Auth::user();
        return view('referrers.shared_studies')->with('doctor_id', $user->doctor_id);
    
    }
    
    protected function sharedstudies_datatable(Request $request) {
        
        if ($request->ajax()) {
            $data = SharedStudies::latest()->where('shared_with', Auth::user()->doctor_id)->get();
            
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($study){
                     $actionBtn = '<a target = "_blank" href ="https://portal.medical.ky/stoneviewer?study=' . json_decode($study->study_json)->StudyInstanceUID . '&proxy=' . json_decode($study->server)->proxy_url  . '" >View</a>';
                     return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    
    }
    
}

//             {data: 'patient_birth_date', name: 'patient_birth_date'},
//             {data: 'patient_sex', name: 'patient_sex'},
//             {data: 'patientid', name: 'patientid'},
//             {data: 'requested_procedure_id', name: 'requested_procedure_id'},
//             {data: 'scheduled_procedure_step_start_date', name: 'scheduled_procedure_step_start_date'},

//     public function index(Request $request)
// 
//     {
// 
//         if ($request->ajax()) {
// 
//             $data = User::select('*');
// 
//             return Datatables::of($data)
// 
//                     ->addIndexColumn()
// 
//                     ->editColumn('created_at', function ($user) {
// 
//                        return [
// 
//                           'display' => e($user->created_at->format('m/d/Y')),
// 
//                           'timestamp' => $user->created_at->timestamp
// 
//                        ];
// 
//                     })
// 
//                     ->filterColumn('created_at', function ($query, $keyword) {
// 
//                        $query->whereRaw("DATE_FORMAT(created_at,'%m/%d/%Y') LIKE ?", ["%$keyword%"]);
// 
//                     })
// 
//                     ->make(true);
// 
//         }
// 
//         
// 
//         return view('users');
// 
//     }

// $users = User::where('name', '=', $request['name'])
//     ->where('surname', '=', $request['surname'])
//     ->where('address', '<>', $request['address'])
//     ...
//     ->get();
//     public function datatable(Request $request)
//     
//     {
//         if ($request->ajax()) {
//             $data = Patients::latest()->get();
//             return Datatables::of($data)
//                 ->addIndexColumn()
//                 ->addColumn('action', function($row){
//                     $actionBtn = '<div data-mrn="' .$row->mrn . '"><button class="editpatient btn btn-success btn-sm">Edit</button><button class="showpatient_studies btn btn-success btn-sm">Studies</button><button class="show_patient_orders btn btn-success btn-sm">Orders</button><button class="place_patient_order btn btn-success btn-sm">Place Order</button><button class="upload_patient_study btn btn-success btn-sm">Upload Study</button></div>';
//                     return $actionBtn;
//                 })
//                 ->rawColumns(['action'])
//                 ->make(true);
//         }
//     }