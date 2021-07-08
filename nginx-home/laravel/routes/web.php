<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Actions\Orthanc\OrthancAPI;  // could change this for other PACS Interface or add others and capture that below
use App\Actions\Orthanc\PACSUploadStudies;

use App\Providers\RouteServiceProvider;
use App\Http\Controllers\MyControllers\HL7Controller;
use App\Http\Controllers\MyControllers\ReportsController;
use App\Http\Controllers\MyControllers\PatientsController;
use App\Http\Controllers\MyControllers\OrdersController;
use App\Http\Controllers\MyControllers\ReferringPhysicianController;
use App\Http\Controllers\MyControllers\EmailController;
use App\Http\Controllers\MyControllers\UtilitiesController;

use Illuminate\Support\Facades\Storage;


use Laravel\Jetstream\Http\Controllers\Livewire\PrivacyPolicyController;
use Laravel\Jetstream\Http\Controllers\Livewire\TermsOfServiceController;


use App\Models\Referrers\ReferringPhysician;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
SPATIE CSP (https://github.com/spatie/laravel-csp):  Handled in the Kernel, but can also be handled in Routes:
namespace App\Policies\SpatieCSPPolicy is where the Policies are defined.
// in a routes file
Route::get('my-page', 'MyController')->middleware(Spatie\Csp\AddCspHeaders::class);
or
Route::get('my-page', 'MyController')->middleware(Spatie\Csp\AddCspHeaders::class . ':' . MyPolicy::class);


Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show'); // these are handled by the framework.
Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');
*/


// LANDING PAGE FOR USERS NOT LOGGED IN AND ALSO JUST "HOME" FOR LOGGED IN USERS
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Verify e-mail notice.  Renders if email_verified_at in users is  not set.
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');


// WRAPPER FOR ROUTE GROUP PROTECTED, REQUIRES 2-FACTOR ENABLED, MIDDLEWARE REDIRECTS TO PROFILE PAGE IF NOT ENABLED, APPLY TO ALL USERS

Route::group(['middleware' => ['has2FaEnabled']], function () {


    // DEFAULT PAGE AFTER USER LOGGED IN
    Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // THING to FETCH STUDIES from PACS SERVER BASED ON POST DATA, ORTHANC for NOW, Authorization is now handled by a user profile passed to the Orthanc Python script

    // USERPROFILEJWT, passed as userprofilejwt = json.loads(request['headers']['userprofilejwt']); in OrthancAPI:

    // 		self::$userprofileJWT = array (
    // 
    // 		'id' => Auth::user()->id,
    //         'name' => Auth::user()->name,
    //         'email' => Auth::user()->email,
    //         'patientid' => Auth::user()->patientid,
    //         'doctor_id' => Auth::user()->doctor_id,
    //         'reader_id' => Auth::user()->reader_id,
    //         'user_roles' => Auth::user()->user_roles,
    //         'ip' => $_SERVER['REMOTE_ADDR']
    // 		);
    // 

    // ProxyPass Server is the default server in nginx-conf, managed there.


    Route::middleware(['auth:sanctum', 'verified'])->post('studies/page', function (Request $request) {
        // $PACS is set to "Orthanc" in that model.  For future use with alternate PACS.
        if (RouteServiceProvider::$PACS) {

        $user = Auth::user();
        $query = json_decode($request->getContent());
    
        if (empty($user->patientid) && empty($user->doctor_id) &&  empty($user->reader_id)  && count(array_intersect(json_decode($user->user_roles), [4,5,6,7,8])) == 0) {
            die('{"error":"You have no privileges to view studies"}');
        }
        // Thing to set the patientid, doctorid if the request come from the patient or doctor study list page, otherwise leave unchanged.
        $referer = request()->headers->get('referer');
        if (strpos($referer, 'referrers_studies') !== false) $query->Query->ReferringPhysicianName = $user->doctor_id .':*';
        if (strpos($referer, 'patientportal/studies') !== false) $query->Query->PatientID = $user->patientid;
        $orthanc = new OrthancAPI();
        return $orthanc->getStudiesArray ($query);

        }
    })->name('studies/page');
    
    


});



// GET THE LAST HL7 REPORT, Validation is in the Controller, gets just the last report

Route::middleware(['auth:sanctum', 'verified'])->post('/HL7/get_last_hl7', [HL7Controller::class, 'get_last_hl7'])->name('get_last_hl7');

// getPDFfromBody, converts HTML + to a PDF document for display or download.

Route::middleware(['auth:sanctum', 'verified'])->post('/Utilities/getPDFfromBody', [UtilitiesController::class, 'getPDFfromBody'])->name('getPDFfromBody');

// emails a reports

Route::middleware(['auth:sanctum', 'verified'])->post('emailReport', [UtilitiesController::class, 'emailReport'])->name('emailReport');

// stoneviewer

Route::middleware(['auth:sanctum', 'verified'])->get('/stoneviewer', function () {
    $config = (new OrthancAPI())->getStoneConfigs();
    return view('stone_viewer')->with('data', $config);
})->name('stoneviewer');

// ** READER ROUTES BEGIN

Route::group(['middleware' => ['permission:reader_data']], function () {

// READERS PORTAL PAGE GET & POST
Route::middleware(['auth:sanctum', 'verified'])->match(['get', 'post'], '/readers_studies', function (Request $request) {

    if ($request->method == "POST" && !empty($request->input('orthanc_host'))) {
        OrthancAPI::setHost($request->input('orthanc_host'));
    }
    return view('readers_studies');
})->name('readers_studies');

});

// ** PROVIDER ROUTES BEGIN

Route::group(['middleware' => ['permission:provider_data']], function () {

    // PROTECTED BY 2-FACTOR
    Route::group(['middleware' => ['has2FaEnabled']], function () {
    
    // STUDY LIST GET & POST
    Route::middleware(['auth:sanctum', 'verified'])->match(['get', 'post'], '/referrers_studies', function (Request $request) {
        if ($request->method == "POST" && !empty($request->input('orthanc_host'))) {
            OrthancAPI::setHost($request->input('orthanc_host'));
        }
        $user = Auth::user();
        return view('referrers.referrers_studies')->with('doctor_id', $user->doctor_id);
    })->name('referrers_studies');
    

    // PROVIDER / REFERRER PROFILE
    Route::middleware(['auth:sanctum', 'verified'])->get('/referrers_profile', function () {
        $user = Auth::user();
        return view('referrers.referrers_profile')->with('doctor_id', $user->doctor_id);
    })->name('referrers_profile');

    // UPDATE PROFILE ITEMS
    Route::middleware(['auth:sanctum', 'verified'])->post('/referrers/updateprofileitem', [ReferringPhysicianController::class, 'updateprofileitem'])->name('/referrers/updateprofileitem');

    // EDIT PROVIDER LICENSE
    Route::middleware(['auth:sanctum', 'verified'])->post('/provider_licenses/edit', [ReferringPhysicianController::class, 'updatelicense'])->name('provider_licenses/edit');

    // ADD PROVIDER LICENSE
    Route::middleware(['auth:sanctum', 'verified'])->post('/provider_licenses/addlicense', [ReferringPhysicianController::class, 'addlicense'])->name('provider_licenses/addlicense');    

    // DELETE PROVIDER LICENSE
    Route::middleware(['auth:sanctum', 'verified'])->post('/provider_licenses/deletelicense', [ReferringPhysicianController::class, 'deletelicense'])->name('provider_licenses/deletelicense');   

    // LIST PROVIDER LICENSES
    Route::middleware(['auth:sanctum', 'verified'])->post('/provider_licenses/listlicenses', [ReferringPhysicianController::class, 'listlicenses'])->name('provider_licenses/listlicenses');   

    // PLACE ORDER PAGE
    Route::middleware(['auth:sanctum', 'verified'])->get('/referrers_placeorder', function () {
        $user = Auth::user();
        return view('referrers.referrers_placeorder')->with('doctor_id', $user->doctor_id);
    })->name('referrers_placeorder');

    // SUBMIT ORDER REQUEST
    Route::middleware(['auth:sanctum', 'verified'])->post('/referrers/submitorderrequest', [ReferringPhysicianController::class, 'submitorderrequest'])->name('referrers_submitorderrequest');

    // PLACED ORDERS
    Route::middleware(['auth:sanctum', 'verified'])->post('/referrers/placedorders_datatable', [ReferringPhysicianController::class, 'placedorders_datatable']);

    // REQUEST HISTORY DATATABLE
    Route::middleware(['auth:sanctum', 'verified'])->post('/referrers/requests_datatable', [ReferringPhysicianController::class, 'requests_datatable']);


    // SHARE LIST
    Route::middleware(['auth:sanctum', 'verified'])->post('/referrers/sharelist', function() {
        ReferringPhysician::sharelist();
    })->name('sharelist');

    // SHARE LIST SUBMIT
    Route::middleware(['auth:sanctum', 'verified'])->post('/referrers/share', [ReferringPhysicianController::class, 'share'])->name('referrers/share');
    
    // SHARED STUDY LIST
    Route::middleware(['auth:sanctum', 'verified'])->get('/shared_studies', [ReferringPhysicianController::class, 'shared_studies'])->name('shared_studies');
    
    // SHARED STUDIES DATATABLE
    Route::middleware(['auth:sanctum', 'verified'])->post('/sharedstudies_datatable', [ReferringPhysicianController::class, 'sharedstudies_datatable'])->name('/sharedstudies_datatable');        

    
    // ** PROVIDER ROUTES END
    });

});

// ** PATIENT ROUTES BEGIN

Route::group(['middleware' => ['permission:patient_data']], function () {

    // PROTECTED BY 2-FACTOR
    Route::group(['middleware' => ['has2FaEnabled']], function () {

    // PATIENT PORTAL PAGE GET & POST

    Route::middleware(['auth:sanctum', 'verified'])->match(['get', 'post'], '/patientportal/studies', function (Request $request) {
        if ($request->method == "POST" && !empty($request->input('orthanc_host'))) {
            OrthancAPI::setHost($request->input('orthanc_host'));
        }
        return view('patientportal.studies');
    })->name('patientportal/studies');

    Route::middleware(['auth:sanctum', 'verified'])->get('/patientportal/profile', function () {
        $user = Auth::user();
        return view('patientportal.profile')->with('mrn', $user->patientid);
    })->name('patientportal/profile');
    
    Route::middleware(['auth:sanctum', 'verified'])->post('/patients/updateprofileitem', [PatientsController::class, 'updateprofileitem']);
    
    Route::middleware(['auth:sanctum', 'verified'])->post('/patients/showhistory', [PatientsController::class, 'showhistory']);
    
    Route::middleware(['auth:sanctum', 'verified'])->post('/patients/showcontacts', [PatientsController::class, 'showcontacts']);
    
    Route::middleware(['auth:sanctum', 'verified'])->post('/patients/showemployer', [PatientsController::class, 'showemployer']);
    
    Route::middleware(['auth:sanctum', 'verified'])->post('/patients/showpreferences', [PatientsController::class, 'showpreferences']);
    
    Route::middleware(['auth:sanctum', 'verified'])->post('/patients/showinsurance', [PatientsController::class, 'showinsurance']);
    
    Route::middleware(['auth:sanctum', 'verified'])->post('/patients/addinsurance', [PatientsController::class, 'addinsurance']);
    
    Route::middleware(['auth:sanctum', 'verified'])->get('/patientportal/orders', function () {
        return view('patientportal/orders')->with('patientid', Auth::user()->patientid);
    })->name('patientportal/orders');

    Route::middleware(['auth:sanctum', 'verified'])->get('/patientportal/documents', function () {
    
        if(!Storage::disk('patients')->exists(Auth::user()->id)) {
            Storage::disk('patients')->makeDirectory(Auth::user()->patientid, 0775, true);
        }
        $dir_path =  Storage::disk('patients')->getAdapter()->getPathPrefix() . Auth::user()->patientid;
		$files = array_values(preg_grep('/^([^.])/', scandir($dir_path)));
		$filelist = [];
		foreach ($files as  $key => $file_folder) {

		if (is_file($dir_path .DIRECTORY_SEPARATOR. $file_folder)) {
		$filelist[$file_folder]["type"] = "file";
		$filelist[$file_folder]["mimetype"] = mime_content_type($dir_path .DIRECTORY_SEPARATOR. $file_folder);
		}
		else if (is_dir($dir_path .DIRECTORY_SEPARATOR. $file_folder)) {
		$filelist[$file_folder]["type"] = "dir";

		}

		}
		uasort($filelist, function($a, $b) {
    		return $a['type'] <=> $b['type'];
		});
        return view('patientportal/documents')->with('filelist', $filelist)->with('dir_path', $dir_path);
        
    })->name('patientportal/documents');
        
    Route::middleware(['auth:sanctum', 'verified'])->post('/patientportal/ordersdatatable', [PatientsController::class, 'ordersdatatable']);
    });
    
});

    
// ** ADMIN ROUTES BEGIN

Route::group(['middleware' => ['permission:admin_data']], function () {

    Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getStudyTechnique', function (Request $request) {
        $orthanc = new OrthancAPI();
        $orthanc->getProtocolSummary($request->input("uuid"));
    })->name('getStudyTechnique');

});


// PATIENTS CLASS RELATED ROUTES

// LIST PATIENTS LANDING PAGE, GET
Route::middleware(['auth:sanctum', 'verified'])->get('/patients/patients', [PatientsController::class, 'patient_list'])->name('patient_list');

// LIST PATIENTS VIA QUERY, POST
Route::middleware(['auth:sanctum', 'verified'])->post('/patients/search', [PatientsController::class, 'patient_search'])->name('patient_search');

// DISPLAY / GET THE PATIENT HISTORY FORM
Route::middleware(['auth:sanctum', 'verified'])->post('/patients/history', [PatientsController::class, 'patient_history'])->name('patient_history');

// GET THE ORDER FORM,  VIA GET (NEW), OR POST(EXISTING)
Route::middleware(['auth:sanctum', 'verified'])->match(['get', 'post'],'/orders/orderform', [OrdersController::class, 'orderform'])->name('orderform');

// GET DATATABLE FOR PATIENT LIST
Route::middleware(['auth:sanctum', 'verified'])->post('patients/datatable', [PatientsController::class, 'datatable'])->name('patients/datatable');

// Contact Form Mailer, from any page 
Route::post('/mail/webform', [EmailController::class, 'checkcaptcha'])->name('mail/webform');

// HL7 Controller Routes

Route::middleware(['auth:sanctum', 'verified'])->post('HL7/submit_report', [HL7Controller::class, 'submit_report'])->name('submit_report');


// OHIF VIEWER ROUTES, VIEWS ARE IN ohif/studylist and ohif/studyview in resources->views

Route::middleware(['auth:sanctum', 'verified'])->get('OHIFViewer/viewer/{id}', function() {

    $user = Auth::user();
    return view('ohif.OHIFViewer');

})->name('OHIFViewer');





// DEVTOOL PORTAL PAGE

//  Route::match(['get', 'post']
Route::middleware(['auth:sanctum', 'verified'])->match(['get', 'post'], '/devtool', function (Request $request) {

    if ($request->method == "POST" && !empty($request->input('orthanc_host'))) {
        OrthancAPI::setHost($request->input('orthanc_host'));
    }
    $user = Auth::user();
    Debugbar::error($user);
    $request->session()->flash('flash.banner', 'Yay it works!');
$request->session()->flash('flash.bannerStyle', 'success');
    return view('devtool');

})->name('devtool');


//  THING TO GET LIST OF MODALITIES LIST / JSON FO ROUTING
Route::middleware(['auth:sanctum', 'verified'])->post('get_modalities', function (Request $request) {

    if (RouteServiceProvider::$PACS) {
    	$orthanc = new OrthancAPI();
    	echo $orthanc->DICOMdestinations();
    }

})->name('get_modalities');



// OrthancDev/load_all_studies - THING TO FETCH ALL OF THE STUDIES BELONGING TO A PARTICULAR, constructs the query

Route::middleware(['auth:sanctum', 'verified'])->post('OrthancDev/load_all_studies', function (Request $request) {

    if (RouteServiceProvider::$PACS) {

        if (!isset($_POST['data-mrn']) || $_POST['data-mrn'] == "" ) {

        echo '[{"error":"MRN required, use Search Tool"}]';

        }
        else {

            $query = new stdClass();
            $query->Level = "Study";
            $query->Expand = true;
            $query->pagenumber = $_POST["page"];
            $query->itemsperpage = config('myconfigs.DEFAULT_OLD_STUDIES');
            $query->sortparam = "StudyDate";
            $query->reverse = 1;
            $query->widget = 1;
            $query->MetaData = new stdClass();
            $query->Local = new stdClass();
            $query->Tags = new stdClass();
            $query->Query = new stdClass();
            $query->Query->PatientID = $_POST['data-mrn'];
            $query->Query->LoadALL = true;


            if (empty($_POST['page'])) {
            $query->pagenumber = 1;
            }

        }

        Debugbar::error($query);
        $orthanc = new OrthancAPI();
        $user = Auth::user();
        return $orthanc->getStudiesArray ($query,$user);

    }

})->name('load_all_studies');

// THING TO LOAD THE UI FOR UPLOADING AN IMAGE OR PDF TO A STUDY

Route::middleware(['auth:sanctum', 'verified'])->post('/PACS/create_dicom', function (Request $request) {

    $user = Auth::user();
    Debugbar::error($user);
    return view('components/create_dicom');

})->name('create_dicom');

// THING TO ACTUALLY SEND THE IMAGE OR PDF TO THE ORTHANC SERVER.

Route::middleware(['auth:sanctum', 'verified'])->post('/PACS/attachMIMEToStudy', function(Request $request) {
    if (RouteServiceProvider::$PACS ==  "Orthanc") {
    $orthanc = new OrthancAPI();
    $orthanc->attachMIMEToStudy($request);
    }
})->name('attachMIMEToStudy');

// THING TO LOG THE REQUEST TO VIEW A STUDY AND RETURN THE URL

Route::middleware(['auth:sanctum', 'verified'])->post('/PACS/logViewStudy', function (Request $request) {

    if (RouteServiceProvider::$PACS==  "Orthanc") {
        $orthanc = new OrthancAPI();
        return $orthanc->logViewStudy($request);
     }
});

Route::middleware(['auth:sanctum', 'verified'])->post('downloadStudyUUID', function(Request $request) {
    if (RouteServiceProvider::$PACS ==  "Orthanc") {
    $orthanc = new OrthancAPI();
    $orthanc->downloadStudyUUID($request);
    }
})->name('downloadStudyUUID');

Route::middleware(['auth:sanctum', 'verified'])->post('loadallstudies', function(Request $request) {
    if (RouteServiceProvider::$PACS ==  "Orthanc") {
    $orthanc = new OrthancAPI();
    $orthanc->loadallstudies($request);
    }
})->name('loadallstudies');



// DEV TOOL & ORTHANC API Routes, mostly a group calling methods in OrthancAPI();

// GET PHPINFO PAGE AT NGINX ROOT
Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/PHPINFO', function() {
    $orthanc = new OrthancAPI();
    echo $orthanc->PHPINFO();
})->name('PHPINFO');

// getOrthancModalities, list of modalities to which studies can be routed, configured in orthanc.json
Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getOrthancModalities', function() {
    $orthanc = new OrthancAPI();
    echo $orthanc->getOrthancModalities();
})->name('getOrthancModalities');


Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/ServerStatus', function() {
    $orthanc = new OrthancAPI();
    echo $orthanc->ServerStatus();
})->name('ServerStatus');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/StopServer', function() {
    $orthanc = new OrthancAPI();
    echo $orthanc->StopServer();
})->name('StopServer');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/StartServer', function() {
    $orthanc = new OrthancAPI();
    echo $orthanc->StartServer();
})->name('StartServer');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getViewerLink', function(Request $request) {
    $orthanc = new OrthancAPI();
    $link =  $orthanc->getViewerLink($request);
    echo '[{"success":"true", "link":"' . $link . '"}]';
})->name('getViewerLink');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getOrthancConfigs', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->getOrthancConfigs($request->input('key'));
})->name('getOrthancConfigs');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getPatients', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->getPatients($request->input('uuid'));
})->name('getPatients');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getStudies', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->getStudies($request->input('uuid'));
})->name('getStudies');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getSeries', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->getSeries($request->input('uuid'));
})->name('getSeries');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getInstances', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->getInstances($request->input('uuid'), $request->input('withtags'));
})->name('getInstances');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getDICOMTagListforUUID', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->getDICOMTagListforUUID($request->input('uuid'));
})->name('getDICOMTagListforUUID');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getDICOMTagValueforUUID', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->getDICOMTagValueforUUID($request->input('uuid'), $request->input('tagcodes'));
})->name('getDICOMTagValueforUUID');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getInstanceDICOM', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->pydicom($request->input('uuid'));
})->name('getInstanceDICOM');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getInstancePNGPreview', function(Request $request) {
    $orthanc = new OrthancAPI();
    $raw = $orthanc->getInstancePNGPreview($request->input('uuid'), $request->input('pngjpg'));
    $image_data_base64 =  base64_encode ($raw);  // also image/jpeg
    echo '<img src="data:image/png;base64,' . $image_data_base64 . '" alt="img"/ >';
})->name('getInstancePNGPreview');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getInstanceJPGPreview', function(Request $request) {
    $orthanc = new OrthancAPI();
    $raw = $orthanc->getInstancePNGPreview($request->input('uuid'), $request->input('pngjpg'));
    $image_data_base64 =  base64_encode ($raw);  // also image/jpeg
    echo '<img src="data:image/jpg;base64,' . $image_data_base64 . '" alt="img"/ >';
})->name('getInstanceJPGPreview');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/downloadZipStudyUUID', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->downloadZipStudyUUID($request->input('uuid'));
})->name('downloadZipStudyUUID');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/downloadDCMStudyUUID', function(Request $request) {
    $orthanc = new OrthancAPI();
    $result = $orthanc->downloadDCMStudyUUID($request->input('uuid'));
ob_start();
    echo $result;
})->name('downloadDCMStudyUUID');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/pydicom', function(Request $request) {
    $orthanc = new OrthancAPI();
    $result = $orthanc->pydicom($request->input('uuid'));
ob_start();
    echo $result;
})->name('pydicom');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/studyCountByPatientId', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo json_encode($orthanc->studyCountByPatientId($_POST['PatientID']));
})->name('studyCountByPatientId');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getStudyArrayOfUUIDs', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->getStudyArrayOfUUIDs($_POST['getStudyArrayOfUUIDs']);
})->name('getStudyArrayOfUUIDs');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/getMetaDataValueForUUID', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->executeCURL('studies/' . $_POST['uuid'] . '/metadata?expand');
})->name('getMetaDataValueForUUID');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/setMetaDataValueForUUID', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->executeCURLPUTJSON($_POST['setvalue'], 'studies/' . $_POST['uuid'] . '/metadata/' . $_POST['metadatachoice']);
})->name('setMetaDataValueForUUID');


Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/performQuery', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->performQuery($request->input('queryLevel'), $request->input('query'), True, 100);
})->name('performQuery');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/studies/page', function (Request $request) {

    $fullQuery = new \stdclass();
    $fullQuery->Query = json_decode($request->input('studiespagequery'));
    $fullQuery->pagenumber = intval($request->input('pagenumber'));
    $fullQuery->itemsperpage = intval($request->input('itemsperpage'));
    $fullQuery->reverse = intval($request->input('reverse'));
    $fullQuery->sortparam = $request->input('sortparam');
    $fullQuery->widget = intval($request->input('widget'));
    $fullQuery->MetaData = json_decode($request->input('MetaData'));
    $fullQuery->Tags = json_decode($request->input('Tags'));
    $fullQuery->Expand =true;

    $fullQuery->Level = "Study";
    $orthanc = new OrthancAPI();
    echo  json_encode($orthanc->getStudiesArray ($fullQuery), JSON_PRETTY_PRINT);

})->name('/OrthancDev/studies/page');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/mwl/file/make', function(Request $request) {
    $orthanc = new OrthancAPI();
    echo $orthanc->saveTestMWL(json_encode($request->input()));
})->name('mwl/file/make');


// SUBGROUP REGARDING DOWNLOADING ISO'S AND ZIPS, ROUTING TO MODALITIES
// fetch_study
Route::middleware(['auth:sanctum', 'verified'])->post('/Studies/fetch_study', function (Request $request) {
    $orthanc = new OrthancAPI();
    $orthanc->fetch_study($request->input('id'), $request->input('uuid'));
})->name('fetch_study');

// OrthancDev/downloadStudyUUID

Route::middleware(['auth:sanctum', 'verified'])->post('OrthancDev/downloadStudyUUID', function (Request $request) {

    if (RouteServiceProvider::$PACS) {
    $user = Auth::user();
    $_POST = json_decode(file_get_contents('php://input'), true);
    	if (!isset($_POST["uuid"]) || empty($_POST["uuid"]) || !isset($_POST["command"]) || ($_POST["command"] != "iso" && $_POST["command"] != "zip")) {
    		echo '{"error":"Bad UUID or Bad Type"}';
    	}
    	else {
    	$orthanc = new OrthancAPI();
    	ob_end_clean();
    	if($_POST["command"] == "iso") echo $orthanc->downloadDCMStudyUUID($_POST["uuid"]);
    	if($_POST["command"]== "zip")  echo $orthanc->downloadZipStudyUUID($_POST["uuid"]);
    	}

    }

})->name('OrthancDev/downloadStudyUUID');

// Partially Working, loads the uploader view (self-contained with the passed in data)
Route::middleware(['auth:sanctum', 'verified'])->get('/Studies/upload_study', function(Request $request) {
    $orthanc = new OrthancAPI();
    $uploaderdata = array(
        "IPaddress" => gethostbyname($_SERVER['SERVER_NAME']),
        "passfor" => "upload",
        "data" => array (
                "userid" => Auth::user()->id,
                "user_name" => Auth::user()->name,
                "mrn" => $_GET['mrn'],
                "anon_normal" => false
        )
    );
    // echo '{"status","Migrating":' . json_encode($uploaderdata). '}';
    return view('dicomuploader/uploader')->with('data', $uploaderdata);;
})->name('upload_study');

// Partially Working, UploadZipPreProcess
Route::middleware(['auth:sanctum', 'verified'])->post('/PACSUploadStudies/UploadZipPreProcess', function(Request $request) {
    $PACSUpload = new PACSUploadStudies($request,'UploadZipPreProcess');
    echo $PACSUpload->json_response;
})->name('UploadZipPreProcess');

// Partially Working, UploadZipToPACS
Route::middleware(['auth:sanctum', 'verified'])->post('/PACSUploadStudies/UploadZipToPACS', function(Request $request) {
    $PACSUpload = new PACSUploadStudies($request,'UploadZipToPACS');
    echo $PACSUpload->json_response;
})->name('UploadZipToPACS');

// Partially Working, PACSupload

Route::middleware(['auth:sanctum', 'verified'])->post('/PACSUploadStudies/PACSupload', function(Request $request) {

    if ($request->has('FLAG')) {
        $PACSUpload = new PACSUploadStudies($request, "PACSuploadFinish");
    }
    else {
        $PACSUpload = new PACSUploadStudies($request, "PACSupload");
    }
    echo $PACSUpload->get_json_response();

})->name('PACSupload');


// RADIOLOGY REPORTING ROUTES, REORGANIZE LATER

Route::middleware(['auth:sanctum', 'verified'])->post('/Reports/radreport_templates_list', [ReportsController::class, 'radreport_templates_list'])->name('radreport_templates_list');

Route::middleware(['auth:sanctum', 'verified'])->post('/HL7/getallhl7_reports', [ReportsController::class, 'getallhl7_reports'])->name('getallhl7_reports');

Route::middleware(['auth:sanctum', 'verified'])->post('/Reports/choose_template', [ReportsController::class, 'choose_template'])->name('choose_template');

Route::middleware(['auth:sanctum', 'verified'])->post('/OrthancDev/addPDF', function(Request $request) {
    // Kind of had to 'hack' this.
    $orthanc = new OrthancAPI();
    parse_str($request->getContent(), $output);
    Debugbar::error($output);
    echo $orthanc->addPDF($output); //addPDF($method, $html, $base64, $author, $title, $studyuuid, $return, $attach, $id)
    // curl -k http://localhost:8042/pdfkit/htmltopdf -d '{"method":"base64","title":"BASE64 TO PDF","studyuuid":"e6596260-fdf91aa9-0257a3c2-4778ebda-f2d56d1b","base64":"JVBERi . . .","return":1,"attach":1}'
    /*
	Response is generally like this.
	{
   "attachresponse": {
      "status": {
         "ID": "5f2940a9-08c702ac-7f59bf2e-f5c33ae4-f4a66e6b",
         "ParentPatient": "6816cb19-844d5aee-85245eba-28e841e6-2414fae2",
         "ParentSeries": "cd86a7b0-4e41c903-021e3533-815c009f-2b62e502",
         "ParentStudy": "b9c08539-26f93bde-c81ab0d7-bffaf2cb-a4d0bdd0",
         "Path": "/instances/5f2940a9-08c702ac-7f59bf2e-f5c33ae4-f4a66e6b",
         "Status": "Success"
      },
      "error": "false"
   },
   "base64"

*/

})->name('addPDF');
