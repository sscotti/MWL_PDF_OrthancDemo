<?php
namespace App\Actions\Orthanc;

use \DB;
use App\Actions\Orthanc\UtilityFunctions;
use Illuminate\Support\Facades\Auth;
use ReallySimpleJWT\Token;
use App\Helpers\DatabaseFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Orders\Orders;
use App\Models\Reports\Reports;

class OrthancAPI  {

    private $OrthancURL;
    public $result;
    public $server;
//  public $showAll;
    public $curlerror = false;
    public $curl_error_text;
    private static $Authorization;
    private static $Token;
    private static $Origin;
    private static $userprofileJWT;
    public $responsecode;
    private static $fulltags = true;

    public static function setHost($orthanc_host = false) {

        if (!$orthanc_host) {
            if (empty(config('myconfigs.DEFAULT_ORTHANC_HOST'))) echo "No Default Orthanc Configured.";
    		else session(['orthanc_host' => config('myconfigs.DEFAULT_ORTHANC_HOST')]);
        }
        session(['orthanc_host' => $orthanc_host]);
    }

    public function __construct() {

        Log::info("Constructor for app/Actions/OrthancAPI:  SessionHost:  " . session("orthanc_host"));

    	if(session("orthanc_host") == null ) {
    		Log::info("Setting server to Default in OrthancAPI.");
    		session(['orthanc_host' => config('myconfigs.DEFAULT_ORTHANC_HOST')]);
    	}

		self::$Authorization = config('myconfigs.API_Authorization');
		self::$Token =  config('myconfigs.API_Token');
		self::$Origin = self::my_server_url();
		// self::$userprofileJWT = Auth::user();

		self::$userprofileJWT = array (

		'id' => Auth::user()->id,
        'name' => Auth::user()->name,
        'email' => Auth::user()->email,
        'patientid' => Auth::user()->patientid,
        'doctor_id' => Auth::user()->doctor_id,
        'reader_id' => Auth::user()->reader_id,
        'user_roles' => Auth::user()->user_roles,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'origin' => self::$Origin
		);

    	$this->initServer(session("orthanc_host"));
//     	$this->showAll = false;
    }
	// Setup the Server local and remote REST API URLS, sets the flag for what kind of server it is also, local or remote.

	public function initServer($serverid) {

		if (!empty($serverid)) {
        $this->server = DB::table('orthanc_hosts')->where('id', $serverid)->first();
    	$this->OrthancURL = $this->server->api_url;

		}
		else {
			$this->serverid = false;
		}
		Log::info("initServer");

	}

	public function getAPIURL() {

        return $this->OrthancURL;

    }

    private static function my_server_url() {

        $server_name = $_SERVER['SERVER_NAME'];

        if (!in_array($_SERVER['SERVER_PORT'], [80, 443])) {
            $port = ":$_SERVER[SERVER_PORT]";
        } else {
            $port = '';
        }

        if (!empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }
        return $scheme.'://'.$server_name.$port;
    }

    public static function logVariable($var) {

		if (gettype($var) == "array" || gettype($var) == "object") {

			ob_start();
			echo json_encode($var, JSON_PRETTY_PRINT);
			$output = ob_get_clean();

		}
		else {
		$output = $var;
		}
		Log::info($output);
		return $output;

	}

	// Utility Function

    private static function validateDate($date, $format = 'Y-m-d') {
		$d = DateTime::createFromFormat($format, $date);
		// The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
		return $d && $d->format($format) === $date;
	}

	//  GENERIC THINGS TO VALIDATE AN ORTHANC UUID and DICOM tag codes

	private static function checkUUID($uuid) {

		preg_match('/[0-9a-fa-f]{8}\-[0-9a-fa-f]{8}\-[0-9a-fa-f]{8}\-[0-9a-fa-f]{8}\-[0-9a-fa-f]{8}/', $uuid, $matches);
		return (count($matches) == 1);
	}

	private static function checkTagCode($tagcode) {

		preg_match('/[0-9]{4}\-[0-9]{4}/', $tagcode, $matches);
		return (count($matches) == 1);
	}
/**
THESE ARE ALL FOR MAKING API CALL VIA THE NGINX PROXY WITH AUTHENTICATION, MUCH EAISER THAN THE OTHER WAY
NEED TO PASS AUTH in the Headers as a token and have the Origin of the Server here and on the NGINX server
Used for downloadDCMStudyUUID, downloadZipStudyUUID
**/

    private function processCURLResults(&$ch) {

		$this->result = curl_exec($ch);
		$this->responsecode =  curl_getinfo($ch,CURLINFO_HTTP_CODE);
		if (curl_errno($ch) || curl_getinfo($ch,CURLINFO_HTTP_CODE) != "200") {
			$this->curlerror = true;
			$this->curl_error_text = "Status:  " . curl_getinfo($ch,CURLINFO_HTTP_CODE) . ', Error: ' . curl_error($ch);
			curl_close($ch);
			return $this->result;
		}
		else {

			$this->curlerror = false;
			$this->curl_error_text = "No Errors";
			Log::info(curl_getinfo($ch));
			curl_close($ch);
			return $this->result;
		}

	}


	public function executeCURL($CURLOPT_URL) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->OrthancURL . $CURLOPT_URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_ENCODING , "gzip");
		Log::info("executeCURL:  " . $this->OrthancURL . $CURLOPT_URL);
		$headers = array();
		//$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		$headers[] = 'Authorization:' . self::$Authorization;
		$headers[] = 'Token:' . self::$Token;
		$headers[] = 'Origin:' . self::$Origin;
		//$headers[] = 'Accept-Encoding:gzip';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		return $this->processCURLResults($ch);

	}

	public function executeCURLPOSTJSON($JSONQuery, $url) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->OrthancURL . $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $JSONQuery);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_ENCODING , "gzip");
		Log::info("executeCURLPOSTJSON:  " . $this->OrthancURL . $url);
		Log::info("executeCURLPOSTJSON_Args:  " . $JSONQuery);
		$headers = array();
		$headers[] = 'Authorization:' . self::$Authorization;
		$headers[] = 'Token:' . self::$Token;
		$headers[] = 'Origin:' . self::$Origin;
		$headers[] = 'userprofileJWT:' . json_encode(self::$userprofileJWT);
		$headers[] = 'Accept-Encoding:gzip';
		// $headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		return $this->processCURLResults($ch);
	}
	
    public function executeCURLPUTJSON($JSONQuery, $url) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->OrthancURL . $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $JSONQuery);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_ENCODING , "gzip");
		Log::info("executeCURLPUTJSON:  " . $this->OrthancURL . $url);
		Log::info("executeCURLPUTJSON:  " . $JSONQuery);
		$headers = array();
		$headers[] = 'Authorization:' . self::$Authorization;
		$headers[] = 'Token:' . self::$Token;
		$headers[] = 'Origin:' . self::$Origin;
		$headers[] = 'userprofileJWT:' . json_encode(self::$userprofileJWT);
		$headers[] = 'Accept-Encoding:gzip';
		// $headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		return $this->processCURLResults($ch);
	}

	public function executeCURLPOSTJSON_NGINXADMIN($JSONQuery) {


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->server->nginx_admin_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $JSONQuery);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_ENCODING , "gzip");
		Log::info("OrthancModel->executeCURLPOSTNGINXAdmin:  " .  $this->server->nginx_admin_url);
		Log::info("Args:  " . $JSONQuery);
		$headers = array();
		$headers[] = 'Authorization:' . self::$Authorization;
		$headers[] = 'Token:' . self::$Token;
		$headers[] = 'Origin:' . self::$Origin;
		$headers[] = 'Accept-Encoding:gzip';
		self::logVariable("Headers:  " . json_encode($headers));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		return $this->processCURLResults($ch);

	}

	public  function executeCURL_checkViewerStatus() {


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->server->server_check);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_ENCODING , "gzip");
		self::logVariable("executeCURL:  " . $this->server->server_check);
		$headers = array();
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		$headers[] = 'Authorization:' . self::$Authorization;
		$headers[] = 'Token:' . self::$Token;
		$headers[] = 'Origin:' . self::$Origin;
		$headers[] = 'Accept-Encoding:gzip';

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		return $this->processCURLResults($ch);

	}

	public function performQuery ($level, $query, $expand, $limit = 100) {  // limit is 100 if not specified.

		$query = json_decode($query);
		$fullquery = new \stdClass();
		$fullquery->Level = $level;
		$fullquery->Expand = $expand;
		$fullquery->Limit = $limit;
		$fullquery->Query = $query;
		$postfields = json_encode($fullquery);
		$this->executeCURLPOSTJSON($postfields, 'tools/find');
		Log::info("tools/find:  " .$postfields);
		return $this->result;

	}

	public function DICOMdestinations() {

    	$this->executeCURL("modalities?expand=");
    	if ($this->curlerror === true) return '<option value="">Server Down</option>';
    	$destinations = json_decode($this->result);
    	$html = '<option value="" disabled selected="selected">Send</option>';
    	foreach ($destinations as $key => $destination) {
    		$html.= '<option value="' . $key . '">' .$key . '</option>';
    	}
    	return $html;

    }
    // passed
    public function viewerOnline() {


	}

	// passed
	public function ServerStatus() {

		// returns the time if valid
		return $this->executeCURL('tools/now-local');
	}
    // passed
    public function StartServer() {

		return $this->executeCURLPOSTJSON_NGINXADMIN('{"method":"StartServer","api_url":"' . $this->OrthancURL. '"}');
	}
	// passed
	public function StopServer() {
        // /tools/shutdown
		return $this->executeCURLPOSTJSON_NGINXADMIN('{"method":"StopServer","api_url":"' . $this->OrthancURL. '"}');
	}

	// passed
	public function Restart() {
        //tools/reset
		return $this->executeCURLPOSTJSON_NGINXADMIN('{"method":"Restart","api_url":"' . $this->OrthancURL. '"}');
	}

	// passed
	public function Conformance() {
        // tools/dicom-conformance
		return $this->executeCURLPOSTJSON_NGINXADMIN('{"method":"Conformance","api_url":"' . $this->OrthancURL. '"}');
	}
	// passed
	public function PHPINFO() {

		return $this->executeCURLPOSTJSON_NGINXADMIN('{"method":"PHPINFO","api_url":"' . $this->OrthancURL. '"}');
	}
	// passed
    public static function getServersArray() {

    	$query = 'SELECT * from orthanc_hosts';
    	return DB::table('orthanc_hosts')->get();
    	//return DatabaseFactory::selectByQuery($query, [])->fetchAll(\PDO::FETCH_OBJ);

    }
    // passed
    public static function createAPIandInfoFromServerID($id) {

    	$server = DB::table('orthanc_hosts')->where('id', $id)->first();
    	if ($server) {
            $APIStrings = new \stdClass();
            $APIStrings->api_url = $server->api_url;
            $APIStrings->server = $server;
            $APIStrings->display ='AET:  ' . $server->AET. '    Name:  ' . $server->server_name ;
            return $APIStrings;
    	}
    	else return false;

    }
	// passed
	public function getPatients($uuid = "") {

		if ($uuid == "" || self::checkUUID($uuid)) {
			$this->result = $this->executeCURL("patients/" . $uuid);
			return $this->result;

		}
		else return false;

	}
	// passed
	public function getStudies($uuid = "") {  // same as constructor for subsequent calls.

		if ($uuid == "" || self::checkUUID($uuid)) {
			$this->result = $this->executeCURL("studies/"  . $uuid);
			return $this->result;
		}
		else return false;
	}
	// passed
	public function getSeries($uuid = false) {  // same as constructor for subsequent calls.

		if ($uuid == "" || self::checkUUID($uuid)) {
			$this->result = $this->executeCURL("series/" . $uuid);
			return $this->result;
		}
		else return false;
	}
	// passed
	public function getInstances($uuid , $withtags) {

		if (self::checkUUID($uuid)) {

			if ($withtags == "simplified-tags" ||  $withtags == "tags") {
			$withtags = ('/' . $withtags );  // detailed info
			$suffix = $uuid . "/" . $withtags;
			}
			else $suffix = $uuid;
		}
		else {

			if (empty($uuid)) $suffix = "";
			else {
			return false;  // returns false if a bad uuid format.
			}
		}

		$this->result = $this->executeCURL("instances/" . $suffix);
		if(!$this->result) echo '[{"error":"No Results"}]';
        return $this->result;

	}
	// passed
	public function getDICOMTagListforUUID($uuid) {

		if (self::checkUUID($uuid)) {

			$this->executeCURL("instances/" . $uuid . '/content');
			return $this->result;  // also returns false if bad response
		}
		return false;
	}
	// passed
	public function getDICOMTagValueforUUID ($uuid, $tagcodes) {
        $tagcodes = explode("/", $tagcodes);
		if (self::checkUUID($uuid)) {

			$errors = false;
			foreach ($tagcodes as $tagcode)  {
				if (!self::checkTagCode($tagcode)) $errors = true;
			}
			if (!$errors) {
			$tagcodes = implode("/", $tagcodes);
			$this->executeCURL("instances/" . $uuid . '/content/' . $tagcodes);
			return $this->result;
			}
		}
		return false;  // gets here if bad uuid or errors in the tagcodes that were passes.

	}

	// passed
	public function getInstanceDICOM($uuid) {

		if (self::checkUUID($uuid)) {
			return $this->executeCURL("instances/" . $uuid . '/file');
		}
		return false;

	}

	public function pydicom($uuid) {

	 	// returns false or empty if no result
		if (self::checkUUID($uuid)) {
			return $this->executeCURL("pydicom/" . $uuid);
		}
		return false;

	 }

	// passed
	public function getInstancePNGPreview ($uuid, $pngjpg) {

		// returns false or empty if no result
		if (self::checkUUID($uuid)) {
			return $this->executeCURL("instances/" . $uuid . '/preview/', $pngjpg);
		}
		return false;

	}

	// passed
	public function downloadZipStudyUUID ($uuid) {

		$this->executeCURL("studies/" . $uuid . '/archive');
		return $this->result;

	}

	// passed
	public function downloadDCMStudyUUID ($uuid) {

		$this->executeCURL("studies/" . $uuid . '/media');
		return $this->result;

    }

    public function getStudyDetails ($uuid) {

	    $uuid = [$uuid];
	    $data = json_encode($uuid );
	    $data =  $this->executeCURLPOSTJSON($data, 'studies/arrayIDs');
		$result = json_decode($this->result);
		self::decodeOrthancStudies($result);
		return $result[0];

	}
	
	public function getProtocolSummary($uuid) {
	
	    $series = json_decode($this->executeCURL("studies/" . $uuid))->Series;
	    $protocols = [];
	    $seriesnames = [];
	    foreach($series as $sequence) {
	        $sequencedata = json_decode($this->executeCURL("series/" . $sequence))->MainDicomTags;
	        $protocols[] = isset($sequencedata->ProtocolName)?$sequencedata->ProtocolName:"None";
	        $seriesnames[] = isset($sequencedata->SeriesDescription)?$sequencedata->SeriesDescription:"None";
	    }
	    echo '{"protocols":' . json_encode($protocols). ',"names":' .json_encode($seriesnames) . '}';
	}

	public function getStudiesArray ($query, $patient = false, $doctor = false, $reader = false) {

		$jsonquery = json_encode($query);
        Log::info($jsonquery);
		$this->executeCURLPOSTJSON($jsonquery,'studies/page');

		if (!$this->curlerror) {

		$studiesarray = json_decode($this->result);
		Log::info($studiesarray);
		$widgetdata = $studiesarray[0];
		array_shift($studiesarray);
		if (count($studiesarray) > 0) {

			self::decodeOrthancStudies($studiesarray);
// 			Log::info("getStudiesArray, after decoding");
// 			Log::info($studiesarray);
// 			if (isset($query->Local)  && property_exists($query->Local, "reportstatus")) {
// 
// 			foreach ($studiesarray as $key => $study) {
// 				if ($study->reportstatus != $query->Local->reportstatus) unset($studiesarray[$key]);
// 			}
// 			}

		}
		array_unshift($studiesarray,$widgetdata);
		}
		else {
			return json_decode('{"error":"' .$this->curl_error_text . '"}');
		}
		$this->result = $studiesarray;
		//echo $limit;
		return $studiesarray;
		}

	public function loadallstudies ($request) {

    	$data = $request->input();
    	Log::info($request);
        if (!isset($data['data-mrn']) || $data['data-mrn'] == "" ) {

        echo '[{"error":"MRN required, use Search Tool"}]';

        }
        else {

        if (!empty($data['page'])) {
			$query = '{"Query":{"PatientID":"'. $data['data-mrn'] .  '"},"Level":"Study","Expand":true,"MetaData":{},"pagenumber":'.$data["page"].',"itemsperpage":' .config('myconfigs.DEFAULT_OLD_STUDIES') . ',"sortparam":"StudyDate","reverse":1,"widget":1}';
        }
        else {
        	$query = '{"Query":{"PatientID":"'. $data['data-mrn'] .  '"},"Level":"Study","Expand":true,"pagenumber":1,"itemsperpage":' .config('myconfigs.DEFAULT_OLD_STUDIES') . ',"sortparam":"StudyDate","reverse":1,"widget":1}';
        }
        }
        $studiesarray = $this->getStudiesArray (json_decode($query));
        if (count($studiesarray) == 1) {
        	echo '{"error":"No Studies"}';
        }
        else {
        echo json_encode($studiesarray);
		}
    }

	public static function decodeOrthancStudies(&$studyarray) {

	// TO GET raw JSON for testing SPWA
// 	error_log(json_encode($studyarray));
// 	die();
		// converts to local variable names and keeps some from the Orthanc Object.  More readable and names correspond to database.  Basically a map.  Example Orthanc Object at type.
		// imagecount and modalities are passed back from the remote server, but may be undefined.

		foreach ($studyarray as $study) {

			$study->LastUpdate_formatted = isset($study->LastUpdate)?date_create_from_format("Ymd\THis", $study->LastUpdate)->format("Y-m-d H:i:s"):""; // part of Orthanc, "\" required for literal character
			// imagecount and modality are passed back from the server
			$study->uuid = $study->ID;
			$study->patientid = isset($study->PatientMainDicomTags->PatientID)?$study->PatientMainDicomTags->PatientID:"Not Set";
			$study->alt_patientid = isset($study->PatientMainDicomTags->OtherPatientIDs)?$study->PatientMainDicomTags->OtherPatientIDs:"Not Set";
			$study->patient_birth_date = isset($study->PatientMainDicomTags->PatientBirthDate)?$study->PatientMainDicomTags->PatientBirthDate:"Not Set";
			$study->patient_name = isset($study->PatientMainDicomTags->PatientName)?$study->PatientMainDicomTags->PatientName:"Not Set";
			$study->patient_sex = isset($study->PatientMainDicomTags->PatientSex)?$study->PatientMainDicomTags->PatientSex:"-";;

			$study->accession_number = isset($study->MainDicomTags->AccessionNumber)?$study->MainDicomTags->AccessionNumber:"Not Set";
			$study->referring_physician = isset($study->MainDicomTags->ReferringPhysicianName)?$study->MainDicomTags->ReferringPhysicianName:"Not Set";
			$study->institution = isset($study->MainDicomTags->InstitutionName)?$study->MainDicomTags->InstitutionName:"Not Set";
			$study->study_date = isset($study->MainDicomTags->StudyDate)?$study->MainDicomTags->StudyDate:"Not Set";
			$study->study_time = isset($study->MainDicomTags->StudyTime)?$study->MainDicomTags->StudyTime:"Not Set";
			$study->study_description = isset($study->MainDicomTags->StudyDescription)?$study->MainDicomTags->StudyDescription:"Not Set";
			$study->StudyInstanceUID = $study->MainDicomTags->StudyInstanceUID;  // incorporate into description or get from order.  Should always be set
			// missing so far
			$study->created = date_create_from_format("Ymd His", $study->study_date . ' '  . mb_substr($study->study_time, 0, 6));
			if ($study->created) $study->created = $study->created->format("Y-m-d H:i:s");
            // $order = false;
			$order = Orders::getShortOrderByAccession($study->accession_number);  // should always exist if there is an order, but maybe not
			$study->indication = (!empty($order))?$order->indication:"No Order"; // migrate to order
			$study->coded_exam = (!empty($order))?$order->coded_exam:"[]"; // migrate to order
			$study->requested_procedure_id =  (!empty($order))?$order->requested_procedure_id:"No Order";  // migrate to order, part of the order
			$study->order_description =  (!empty($order))?$order->description:"No Order";  // migrate to order, part of the order
			$lastreport = Reports::getLastReportStatusByAccession($study->accession_number); // may or may not exist
			if ($lastreport != false && $lastreport != "error" ) {
				$study->reportstatus = $lastreport->newstatus;
			}
			else if ($lastreport == false) {
				$study->reportstatus = "NONE";
			}
			else if ($lastreport == "error") {
				$study->reportstatus = "???";
			}
			self::addReferrerNames($study);
			if (!self::$fulltags) {
			unset($study->Series);
			unset($study->MainDicomTags);
			unset($study->PatientMainDicomTags);
			}


		}
	}

	private static function addReferrerNames(&$study) {

		$names = explode("^",$study->referring_physician);
		(isset($names[0]))?$study->referring_physician_id = $names[0]:$study->referring_physician_id = "";
		(isset($names[1]))?$study->referrer_last_name = $names[1]:$study->referrer_last_name = "";
		(isset($names[2]))?$study->referrer_first_name = $names[2]:$study->referrer_first_name = "";
		(isset($names[3]))?$study->referrer_middle_name = $names[3]:$study->referrer_middle_name = "";
		(isset($names[4]))?$study->referrer_suffix_name = $names[4]:$study->referrer_suffix_name = "";

	}

	public function attachMIMEToStudy ($request) {

        Log::info($request);
        UtilityFunctions::attachMIMEToStudy($request, $this->server);
    }

    public function addPDF($request) {

        $request = (object)$request;
	    if ($request->method == "html") {
 	    $html = json_encode($request->html);
	    }
	    $jsonquery = '{"method":"' .$request->method.  '","html":' .$html . ',"base64":"' .$request->base64. '","title":"' .$request->title. '","studyuuid":"' .$request->studyuuid. '","return":' .$request->return. ',"attach":' .$request->attach. ',"author":"' .$request->author.  '"}';
	    $this->executeCURLPOSTJSON($jsonquery,'pdfkit/htmltopdf');
	    Log::info('pdfkit/htmltopdf');
        Log::info($jsonquery);
	    return $this->result;
	}

	public function getViewerLink($request) {
        // used from the dev tool page.
		$teststatus = $this->executeCURL_checkViewerStatus();
		
		$dt = \DateTime::createFromFormat("Ymd", substr($teststatus ,0, 8)); // 20210220T124946
		// true if valid data, othewise false an assume bad connection

		if ($dt) { 
            $study = json_decode($this->getStudies($request->input('uuid')));  // need the StudyInstanceUID if just have the uuid.
            return $this->server->osimis_viewer_link .'study=' . $study->MainDicomTags->StudyInstanceUID;
		}

		else {
			return false;  // set to error in curl call, has to be status or error
		}

	}

    public function downloadStudyUUID () {

    $_POST = json_decode(file_get_contents('php://input'), true);
    	if (!isset($_POST["uuid"]) || empty($_POST["uuid"]) || !isset($_POST["command"]) || ($_POST["command"] != "iso" && $_POST["command"] != "zip")) {
    		echo '{"error":"Bad UUID or Bad Type"}';
    	}
    	else {
    	    $result = UtilityFunctions::downloadStudyUUID($_POST["uuid"], $_POST["command"], $this);
    	}
    }

    public function logViewStudy($study) {  // $study is the $_POST or $_Request
    
// StudyInstanceUID	"1.2.276.0.50.192168001099.8252157.14547392.4"
// uuid	"061eccc3-089857f8-e667d555-7ce7f0d5-45b20ada"
// mrn	"DEV0000003"
// accession	"DEVACC00000002"
// description	"CT+Abdomen"

// 		$study_exists = $this->performQuery ("Studies", '{"StudyInstanceUID":"' . $study['StudyInstanceUID'] . '"}', true, $limit = 1);
// 		Log::info($this->curlerror);
// 		Log::info($this->curl_error_text);
// 		if ($this->curlerror != 0 && !empty($this->curl_error_text)) {
// 		    echo '[{"error":"' .$this->curl_error_text . '"}]';
// 		    die();
// 		}
// 
// 		$study_exists = count(json_decode($study_exists)) != 0;
// 
// 		if ($study_exists) {
// 
//             $payload = [
// 
//             'iss' => 'Orthanc PACS',
//             'sub' => 'Viewer Token',
//             'iat' => time(),
//             'uid' => 1,
//             'exp' => time() + 60 * 5,
//             'data' => $_POST
// 
//             ];
//             $secret = 'Hello&MikeFooBar123';
//             $token = Token::customPayload($payload, $secret);
//             setcookie("JWTVIEWER", $token, [
// 
//                 'expires' => time() + config('myconfigs.SESSION_RUNTIME'),
//                 'path' => config('myconfigs.COOKIE_PATH'),
//                 'domain' => config('myconfigs.COOKIE_DOMAIN'),
//                 'secure' => config('myconfigs.COOKIE_SECURE'),
//                 'httponly' => config('myconfigs.COOKIE_HTTP'),
//                 'samesite' => config('myconfigs.COOKIE_SAMESITE'),
//             ]);
        
            $link = $this->server->osimis_viewer_link .'study=' . $study['StudyInstanceUID'] . '&proxy=' .  $this->server->proxy_url;
//             echo '[{"viewstudy":"success","JWT":"' . $token . '","link":"' . $link .'"}]';
            echo '[{"viewstudy":"success","link":"' . $link .'"}]';

// 		}
// 		
// 		else if (!$study_exists && $this->curlerror = false) {
// 			echo '[{"error":"Study Not on PACS."}]';
// 		}
// 		else if ($this->curlerror = true)  {
// 			echo '[{"error":"Error Connecting with PACS."}]';
// 		}

	}


    // GET Default server if specific one not requested and Config is set, otherwise get the one specified.

    public function getAllHosts() {

        $query = 'SELECT * from orthanc_hosts';
	    $results = DB::select($query);
    	return $results;

    }

	public function getServer($serverid = false) {

	    if ($serverid == false) {

	        if (!isset($_SESSION["orthanc_host"])) {

    		    if (config('myconfigs.DEFAULT_ORTHANC_HOST') == "") {
    		        $this->server = false;
    		        Log::info("No Orthanc Server Configured, called from InitServer");
    		        die("No Default Orthanc Configured.");
    		    }

    		    $_SESSION["orthanc_host"] = config('myconfigs.DEFAULT_ORTHANC_HOST');
    	    }
    	}
    	else {
        $_SESSION["orthanc_host"] = $serverid;
        }
        $this->origin = self::my_server_url();
	    $query = 'SELECT * from orthanc_hosts WHERE id = ?';
	    $results = DB::select($query, [$_SESSION["orthanc_host"]]);
    	$this->server = (object)$results[0];
		Log::info($this->server);
	}

	public function fetch_study($id, $uuid) {

	    $ajaxresponse = [];
	    $response = json_decode($this->executeCURLPOSTJSON($uuid, '/modalities/' . $id . '/store/'));
        if (!isset($response->RemoteAet) && $response->RemoteAet != $id) {
            $ajaxresponse['status'] = "error";
            if (isset($response->HttpError)) {
                $ajaxresponse['message'] = $response->HttpError;
            }
            else  $ajaxresponse['message'] = 'Error with Request';
        }
        else {
            $ajaxresponse['status'] = "success";
            $ajaxresponse['message'] = $response->InstancesCount .  " image(s) sent to " .  $id;
        }
        echo json_encode($ajaxresponse);
	    return $ajaxresponse;
	}

	public function saveTestMWL($data) {
	
		return $this->executeCURLPOSTJSON($data, '/mwl/file/make');
	}

	public function deleteMWLfile($AccessionNumber) {

		return $this->executeCURLPOSTJSON(json_encode([$AccessionNumber]), 'mwl/file/delete');

	}

	public function getOrthancModalities() {

		$this->executeCURL("modalities?expand");
		return $this->result;

	}

	public function getOrthancConfigs($item) {
	    // ALL for ALL
		$this->executeCURL("get-configs/" . $item);
		return $this->result;

	}

	public function getStoneConfigs() {

		$this->executeCURL("get-configs/StoneWebViewer");
		return $this->result;

	}

	public function serverStatusWidget() {

    $html = '<style>.led-red {
  margin: 0 auto;
  width: 1em;
  height: 1em;
  background-color: #F00;
  border-radius: 50%;
  box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 7px 1px, inset #441313 0 -1px 9px, rgba(255, 0, 0, 0.5) 0 2px 12px;
  -webkit-animation: blinkRed 0.5s infinite;
  -moz-animation: blinkRed 0.5s infinite;
  -ms-animation: blinkRed 0.5s infinite;
  -o-animation: blinkRed 0.5s infinite;
  animation: blinkRed 0.5s infinite;
  display:inline-block;
}

@-webkit-keyframes blinkRed {
    from { background-color: #F00; }
    50% { background-color: #A00; box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 7px 1px, inset #441313 0 -1px 9px, rgba(255, 0, 0, 0.5) 0 2px 0;}
    to { background-color: #F00; }
}
@-moz-keyframes blinkRed {
    from { background-color: #F00; }
    50% { background-color: #A00; box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 7px 1px, inset #441313 0 -1px 9px, rgba(255, 0, 0, 0.5) 0 2px 0;}
    to { background-color: #F00; }
}
@-ms-keyframes blinkRed {
    from { background-color: #F00; }
    50% { background-color: #A00; box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 7px 1px, inset #441313 0 -1px 9px, rgba(255, 0, 0, 0.5) 0 2px 0;}
    to { background-color: #F00; }
}
@-o-keyframes blinkRed {
    from { background-color: #F00; }
    50% { background-color: #A00; box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 7px 1px, inset #441313 0 -1px 9px, rgba(255, 0, 0, 0.5) 0 2px 0;}
    to { background-color: #F00; }
}
@keyframes blinkRed {
    from { background-color: #F00; }
    50% { background-color: #A00; box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 7px 1px, inset #441313 0 -1px 9px, rgba(255, 0, 0, 0.5) 0 2px 0;}
    to { background-color: #F00; }
}


.led-green {
  margin: 0 auto;
  width: 1em;
  height: 1em;
  background-color: #ABFF00;
  border-radius: 50%;
  box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 7px 1px, inset #304701 0 -1px 9px, #89FF00 0 2px 12px;display: inline-block;}</style><div style="display:inline-block;padding: 0px 5px 0px 5px;background: white;color: black;border-radius: 5px;">Server Status:  ';

$status = $this->ServerStatus(); // RETURNS THE DATE IF IT MAKES IT THROUGH TO ORTHANC

if ($this->curlerror !== false) {

	$status = $this->curl_error_text .'<div class="led-red"></div>';
}
else {
	$dt = \DateTime::createFromFormat("Ymd", substr($status ,0, 8)); // 20210220T124946
	if (!$dt) {
		$status = json_decode($status)->curl_error .'<div class="led-red"></div>';
	}
	else  {
	$status = 'Connected to ' . $this->server->server_name . '<div class="led-green"></div>';
	}
}
$status .= '</div>';
$html .= $status;
return $html;


    }

    	// passed

	public function studyCountByPatientId($patientids) {

 		$result = json_decode($this->executeCURLPOSTJSON(json_encode($patientids), 'patient/studycounts'));
		$counts = [];
		foreach ($result as $key => $count) {
			$counts[$key] = $count;
		}
		return $counts;
	}
	
    public function studyCountByReferringPhysicianName($referrernames) {

 		$result = json_decode($this->executeCURLPOSTJSON(json_encode($referrernames), 'referrer/studycounts'));
		$counts = [];
		foreach ($result as $key => $count) {
			$counts[$key] = $count;
		}
		return $counts;
	}
	
	public function getStatistics() {
	    return $this->executeCURL('statistics');
// 	    { "CountInstances" : 432, "CountPatients" : 5, "CountSeries" : 32, "CountStudies" : 7, "TotalDiskSize" : "84179946", "TotalDiskSizeMB" : 80, "TotalUncompressedSize" : "84179946", "TotalUncompressedSizeMB" : 80 } 
	}

	public function getStudyArrayOfUUIDs ($uuids) {

	    $data = json_encode($uuids);
	    $data =  $this->executeCURLPOSTJSON($data, 'studies/arrayIDs');
		return $this->result;
	}

}
?>
