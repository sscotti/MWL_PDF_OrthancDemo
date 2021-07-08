<?php
namespace App\Actions\Orthanc;
use \DB;
use Illuminate\Support\Facades\Auth;
use ReallySimpleJWT\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PACSUploadStudies

{
	// Trying to make this self-contained class so it could be somewhat portable.

	private static $dcmtk_path;
	private static $Authorization;
    private static $Token;
    private static $origin;

    private static $server;
    private static $OrthancURL;
    private $logfilepath;
    private $logfiletext = "";
    private $anonymize;
    private $altertags;
    private $PatientID;
    private $AccessionNumber;
    private $InstitutionName;
    private $globalerror;
    private $setMetaTags = true;
    private $user_id;
    private $user_name;
    private $getreport;

    private static $dcmdumpargs;
    private static $tagmap;
    private static $deleteAferAnonymization;
    private $request;


    public $json_response;
    public $curlerror;
    public $curl_error_text;
    public $result;
    public $responsecode;

	public function __construct(Request $request, $method)

	{
        $this->request = $request;
        Log::info($this->request);

    	if(session("orthanc_host") == null ) {

    		Log::info("PACSUploadStudies.  Setting server to Default.");
    		session(['orthanc_host' => config('myconfigs.DEFAULT_ORTHANC_HOST')]);
    	}

/*

THIS IS WHAT IT GETS IN THE REQUEST FOR FILE SENDING.
(
  'method' => 'UploadFolder',
  'timestamp' => '2021-03-04-17-03-55',
  'counter' => '5',
  'total' => '7',
  'type' => 'application/dicom',
  'webkitpath' => 'Fall_2/Ct_Abdomen - 11788761116134/COR_ABD_2059/IM-0003-0049.dcm',
  '_token' => '6ay9qZdjXkxpb672Th0waqQmyOIwkeFUlbnnbvx1',
  'anonymize' => 'false',
  'altertags' => 'false',
  'PatientID' => 'test',
  'AccessionNumber' => 'test',
  'InstitutionName' => 'test',
  'file' =>
  Illuminate\Http\UploadedFile::__set_state(array(
     'test' => false,
     'originalName' => 'IM-0003-0049.dcm',
     'mimeType' => 'application/dicom',
     'error' => 0,
     'hashName' => NULL,
  )),
)

THIS IS WHAT IT GETS ON THE FINISH, PROBABLY NEED TO ADD BAD SOME OF THE ANONYMIZE STUFF THERE

[2021-03-04 21:12:16] local.INFO: array (
  'FLAG' => 'FINISH',
  '_token' => 'U9JhQeouvceYssjbWYXU5RvZ4pXS2sIWOSuGxUbK',
  'UUIDs' =>
  array (
    0 => '061eccc3-089857f8-e667d555-7ce7f0d5-45b20ada',
    1 => 'e6596260-fdf91aa9-0257a3c2-4778ebda-f2d56d1b',
  ),
)
*/

		self::$server =  DB::table('orthanc_hosts')->where('id', session("orthanc_host"))->first();
		self::$OrthancURL = self::$server->api_url;
		self::$dcmtk_path = config('PATH_DCMTK');
		self::$Authorization = config('API_Authorization');
		self::$Token = config('API_Token');
		self::$origin = self::my_server_url();
		self::$deleteAferAnonymization = true;
		$this->anonymize = $this->request->input('anonymize'); // passed as false or true
		$this->altertags = $this->request->input('altertags');// passed as false or true
		$this->PatientID = $this->request->input('PatientID'); // If to be modified
		$this->AccessionNumber = $this->request->input('AccessionNumber'); // If to be modified
		$this->InstitutionName = $this->request->input('InstitutionName'); // If to be modified
		$this->user_id = Auth::user()->id; // If to be modified
		$this->user_name = Auth::user()->name; // If to be modified
		$this->globalerror = [];
		$this->getreport = $this->request->input('getreport');

		Log::info("PACSUploadStudies Constructor");

		$tagmapreference = array (

		"0020,000d" => 'StudyInstanceUID',
		"0008,0020" => 'StudyDate',
		"0008,0030" => 'StudyTime',
		"0008,0060" => 'Modality',
		"0008,0080" => 'InstitutionName',
		"0008,0090" => 'ReferringPhysicianName',
		"0008,1030" => 'StudyDescription',
		"0010,0020" => 'PatientID',
		"0008,0050" => 'AccessionNumber',
		"0010,0010" => 'PatientName',
		"0010,0030" => 'PatientBirthDate',
		"0010,0040" => 'PatientSex',
		"0010,1000" => 'RETIRED_OtherPatientIDs'
		);

		self::$tagmap = array (

		"0020,000d" => 'StudyInstanceUID',
		//"0008,0020" => 'StudyDate',
		//"0008,0030" => 'StudyTime',
		"0008,0060" => 'Modality',
		"0008,0080" => 'InstitutionName',
		//"0008,0090" => 'ReferringPhysicianName',
		//"0008,1030" => 'StudyDescription',
		"0010,0020" => 'PatientID',
		"0008,0050" => 'AccessionNumber',
		//"0010,0010" => 'PatientName',
		//"0010,0030" => 'PatientBirthDate',
		//"0010,0040" => 'PatientSex',
		//"0010,1000" => 'RETIRED_OtherPatientIDs'
		);


		self::$dcmdumpargs = "";
		foreach (self::$tagmap as $key => $value) {
			self::$dcmdumpargs.= ' +P ' . $value;
		}
        Log::info("Get Report" . $this->getreport);
		if ($this->getreport === 'TRUE') {
		    $this->PACSuploadFinish();
		}

        else {

            switch ($method) {

                case 'UploadZipPreProcess':
                    $this->UploadZipPreProcess();
                    break;
                case 'UploadZipToPACS':
                     $this->UploadZipToPACS();
                    break;
                case 'PACSupload':
                     $this->PACSupload();
                    break;
                case 'PACSuploadFinish':
                    $this->PACSuploadFinish();
                default:
                    //
            }
        }
	}
	
	public function get_json_response() {
	    return $this->json_response;
	}

	private static function logVariable($var) {

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

	private static function dcmtk_command($command) {

		//--logfile dcmlogfile.cfg
		echo exec(self::$dcmtk_path . $command, $output);
		return $output;
	}

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
			curl_close($ch);
			return $this->result;
		}

	}

	private function executeCURL($CURLOPT_URL) {


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,self::$OrthancURL . $CURLOPT_URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_ENCODING , "gzip");
		$headers = array();
		//$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		$headers[] = 'Authorization:' . self::$Authorization;
		$headers[] = 'Token:' . self::$Token;
		$headers[] = 'Origin:' . self::$origin;
		//$headers[] = 'Accept-Encoding:gzip';

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        return $this->processCURLResults($ch);

	}

	private function executeCURLPOSTJSON($JSONQuery, $url) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$OrthancURL . $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $JSONQuery);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$headers = array();
		$headers[] = 'Authorization:' . self::$Authorization;
		$headers[] = 'Token:' . self::$Token;
		$headers[] = 'Origin:' . self::$origin;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        return $this->processCURLResults($ch);
	}

	private function sendImageToOrthancWithExpect ($filePath, $type)	 {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$OrthancURL .'instances');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($filePath));
		$headers = array();
		$headers[] = 'Authorization:' . self::$Authorization;
		$headers[] = 'Token:' . self::$Token;
		$headers[] = 'Origin:' . self::$origin;
		$headers[] = 'Expect: ';
		$headers[] = 'Content-Type: ' . $type;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			$error = curl_error($ch);
			curl_close($ch);
			return '{"error":"' . $error . '"}';
		}
		curl_close($ch);
		return $result;

	}

	private static function setUserMetaDataTag($Level,$uuid, $tagindex, $tagvalue)	{

		// e.g. curl -k -H	 "Authorization:Bearer CURLTOKEN" -H	"Token:wxwzisme"	https://imacpacs.medical.ky:8000/api/studies/d9b6b774-e207cfaf-08130898-4b852ffe-5143b603/metadata/1024 -X PUT -d 'test'
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$OrthancURL . $Level . '/' . $uuid .'/metadata/' . $tagindex);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $tagvalue);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$headers = array();
		$headers[] = 'Authorization:' . self::$Authorization;
		$headers[] = 'Token:' . self::$Token;
		$headers[] = 'Origin:' . self::$origin;
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			$error = curl_error($ch);
			curl_close($ch);
			return '{"error":"' . $error . '"}';
		}
		curl_close($ch);
		return $result;
	}

	private static function getFullMetaTags($Level, $uuid) { // pass in nothing for $Index, ALL, or the shortname or numberic code.

		$populatedtags = json_decode(self::executeCURL($Level . '/' . $uuid . '/metadata'));
		$metadataarray = [];
		foreach ($populatedtags as $tag) {
			$metadataarray[$tag] = self::executeCURL($Level . '/' . $uuid . '/metadata/' . $tag);
		}
		return $metadataarray;
	}

	private static function setMetaTags($orthanc_uuid) {

		// UNDER DEVELOPMENT AS AN ALT TO USING THE RETIREDTAG
		self::setUserMetaDataTag("studies",$orthanc_uuid,1024, "1 or 0");
		self::setUserMetaDataTag("studies",$orthanc_uuid,1025, "PatientID");
		self::setUserMetaDataTag("studies",$orthanc_uuid,1026, "Accessionumber");
		self::setUserMetaDataTag("studies",$orthanc_uuid,1027, "HL7s");
		self::setUserMetaDataTag("studies",$orthanc_uuid,1028, "HighestReportStatus");
// 		self::logVariable(self::getFullMetaTags("studies", $orthanc_uuid));
	}

    // function to add the Modalitie(s) and the Instance count to the Standard result for a Study Query.

	private function Studies_Data_With_Modalities_Count($study_uuid) {

	    $studydata = json_decode($this->executeCURL("studies/"  . $study_uuid));
	    $instancecount = 0;
	    $modalities = [];
	    $series = [];
	    if (null !== $studydata->Series && count($studydata->Series) > 0); // could have 0
	    $series = $studydata->Series;
        foreach ($series as $seriesuuid) {
             $seriesdata = json_decode($this->executeCURL("series/"  . $seriesuuid));
             $instancecount = $instancecount + count($seriesdata->Instances);
             if (!in_array($seriesdata->MainDicomTags->Modality,$modalities)) $modalities[] = $seriesdata->MainDicomTags->Modality;
        }
	    $studydata->Modality = implode(",",$modalities);
	    $studydata->ImagesInAcquisition = $instancecount;
	    return $studydata;
	}
    // Not currently really used, but good reference for the studes/page query.
	private function Studies_Page_Query($Query) {

	    // Short form to just get detailed data by a simple query using that plug-in so that modalities and instance count comes back also.
	    // $Query, what to search for:  {"StudyDate":"20110303-20210228"}
	    // $Level, for now has to be Study
	    // $Expand, also set to true, although could change that.
	    // $Normalize, not really sure what that does, but false for now, see https://book.orthanc-server.com/users/rest.html#performing-queries-on-modalities for C-finds
	    // $pagenumber, int 1 to the end, defaults to 1 if not in query
	    // $itemsperpage int
	    // $sortparam, what to sort by, has to be in the indexed set of tags, defaults to StudyDate if left out.
	    // $reverse, int, 1 or 0.
	    // $widget, the selected widget, defaults to the only one for now, 1
	    // $Local, an object, does not apply to the CURL
	    // $MetaData, an object
	    // $Tags, also an object of deeper DICOM tags to search for, not fully implement.
	    // $Limit defaults to 200 if not specified.


	    $fullquery = new \stdClass();
	    $fullquery->Level = "Study";
		$fullquery->Expand = true;
		$fullquery->Normalize = false;
		$fullquery->pagenumber = 1;
		$fullquery->itemsperpage = 5;
		$fullquery->sortparam = 'StudyDate';
		$fullquery->reverse = 0;
		$fullquery->widget = 1;
		$fullquery->Query = $Query;
		$fullquery->Local =  new \stdClass();
		$fullquery->MetaData =  new \stdClass();
		$fullquery->Tags =  new \stdClass();
		$postfields = json_encode($fullquery);
		return $this->executeCURLPOSTJSON($postfields, 'studies/page');

	}

	private function writeStudySummaryToDatabase($orthanc_uuid) {

            $studydata = $this->Studies_Data_With_Modalities_Count($orthanc_uuid);
			// Verify Date Format for DB
			// $uniquestudies[$key]["Modality"],  need to be fixed or just omitted.
			$test = \DateTime::createFromFormat('Ymd', $studydata->MainDicomTags->StudyDate);
			if (!$test) $studydata->MainDicomTags->StudyDate = "19700101";
			if (!isset($studydata->PatientMainDicomTags->PatientBirthDate)) $studydata->PatientMainDicomTags->PatientBirthDate = "19700101";
			$test = \DateTime::createFromFormat('Ymd', $studydata->PatientMainDicomTags->PatientBirthDate);
			if (!$test) $studydata->PatientMainDicomTags->PatientBirthDate = "19700101";
            $data = [

                "uploader_id" => $this->user_id,
                "uploader_name" =>$this->user_name,
                "orthanc_uuid" => $studydata->ID,
                "StudyInstanceUID" => $studydata->MainDicomTags->StudyInstanceUID,
                "StudyDate" => $studydata->MainDicomTags->StudyDate,
                "StudyTime" => isset($studydata->MainDicomTags->StudyTime)?$studydata->MainDicomTags->StudyTime:"",
                "Modality" => $studydata->Modality,
                "ReferringPhysicianName" => isset($studydata->MainDicomTags->ReferringPhysicianName)?$studydata->MainDicomTags->ReferringPhysicianName:"",
                "InstitutionName" => isset($studydata->MainDicomTags->InstitutionName)?$studydata->MainDicomTags->InstitutionName:"",
                "StudyDescription" => isset($studydata->MainDicomTags->StudyDescription)?$studydata->MainDicomTags->StudyDescription:"",
                "PatientID" => isset($studydata->PatientMainDicomTags->PatientID)?$studydata->PatientMainDicomTags->PatientID:"",
                "AccessionNumber" => isset($studydata->MainDicomTags->AccessionNumber)?$studydata->MainDicomTags->AccessionNumber:"",
                "OtherPatientIDs" => isset($studydata->PatientMainDicomTags->OtherPatientIDs)?$studydata->PatientMainDicomTags->OtherPatientIDs:"",
                "PatientName" => isset($studydata->PatientMainDicomTags->PatientName)?$studydata->PatientMainDicomTags->PatientName:"",
                "PatientBirthDate" => $studydata->PatientMainDicomTags->PatientBirthDate,
                "PatientSex" => isset($studydata->PatientMainDicomTags->PatientSex)?$studydata->PatientMainDicomTags->PatientSex:"",
                "ImagesInAcquisition" => $studydata->ImagesInAcquisition,
                "upload_datetime" => date("Y-m-d H:i:s", time())
		    ];

			$id = DB::connection('mysql2')->table('dicom_uploads')->insertGetId($data);
		    return $data;

	}

	private function _Anonymize($study_uuid) {

		// Keep the original initially.
		// See http://dicom.nema.org/medical/dicom/current/output/html/part15.html#chapter_E for standard per NEMA

		$newdata = $this->executeCURLPOSTJSON ('{"Replace": {"StudyDate":"19700101","StudyTime":"000000"},"Keep": ["StudyDescription","SeriesDescription"],"KeepPrivateTags": true,"DicomVersion" : "2017c"}', 'studies/' . $study_uuid . '/anonymize');;
		$newdata = json_decode($newdata);
// 		self::logVariable($newdata);
		$new_uuid = $newdata->ID;
		//Below is to delete, if necessary
		//self::DeleteStudy($new_uuid);

		return $this->writeStudySummaryToDatabase($new_uuid);

	}

	public function PACSuploadFinish() {

	    // Sort of a "Stateless" setup to avoid using SESSIONS.  The client collects the unique study uuid's and then send the array of IDs at the end of the upload
	    // To get a "report", which actually could be a reusable feature to get a Report for any study.
        Log::info($this->request->UUIDs);
        $resultsummary = $this->_PostProcessUpload($this->request->UUIDs);
		$this->json_response = '{"status":"Folder Upload Summary","results":' . json_encode($resultsummary) . '}';
		return;

	}

	public function PACSupload () {

		// $_SESSION['DICOMUPLOAD'] set in the Class to collect the StudyInstanceUID's for the folder, as an array of values.
		// unless there are uploads at exactly the same second.	 Could add the ID in from of the timestapm also but it is for the user's session anyways.

		// set_time_limit (60);

		// initialize the session stuff for this upload.
		// do not implement anonymize, allow after upload, or just call when done, just to keep separate from this script.
		// [IPaddress] => 192.168.0.108
		// [passfor] => upload
		// [data] => Array ( [userid] => 1 [user_name] =>  [mrn] =>  [anonymize] => normal ) )
		//$_SESSION['uploaddata']['data']['anonymize']if(session("orthanc_host") == null )

		$KEY = 'DICOMUPLOAD'. $this->request->input('timestamp');

        Log::info(Session::get($KEY));

		// Extract file's data

		$allowed_mimetypes = array(
			// "application/pdf" => "pdf",
			"application/dicom" => "dcm"
		);

		$file_name		= $_FILES['file']['name'];
		$file_size		= $_FILES['file']['size'];
		$file_tmp		= $_FILES['file']['tmp_name'];
		$oldname		= $file_name ;
		$file_type		= mime_content_type($_FILES['file']['tmp_name']);

		if (isset($allowed_mimetypes[$file_type])) {

			$file_ext = $allowed_mimetypes[$file_type];
			$file_name = pathinfo($file_name)['filename'] . '.' . $file_ext;
		}

		else {
			$file_ext = "???";
		}

		// stuff above sets the actual mime type and then adds an approved extension.
		// for the AJAX responses.
		$file_object = new \stdclass();
		$file_object->name = $file_name;
		$file_object->size = $file_size;
		$file_object->type = $file_type;
		$file_object->ext = $file_ext;
		$file_object->status = "";

		$webkitpath = $_POST['webkitpath'];

		$curdir = config('myconfigs.PATH_DICOM_TMP_PARENT');
		$upload_root	= $curdir	 . DIRECTORY_SEPARATOR . $this->PatientID  . DIRECTORY_SEPARATOR . $this->request->input('timestamp');
		$upload_path = $upload_root . DIRECTORY_SEPARATOR . $webkitpath;
		$upload_dir = dirname($upload_path);
		$upload_path = $upload_dir . DIRECTORY_SEPARATOR . $file_name;
		$this->logfilepath =  $upload_dir . DIRECTORY_SEPARATOR . 'UploadFolder.log';
// 		self::logVariable($curdir);
		if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        // Get here when all of the files have been, get the request from the client.

        if (false) {


            $html = "";
            $orthanc_uuid_array = $request->session()->get($UUIDS);
            foreach ($orthanc_uuid_array as $orthanc_uuid) {

                $studydata = $this->writeStudySummaryToDatabase($orthanc_uuid);
                $html .= '<div style = "border: 1px solid black;">';

                foreach ($studydata as $name => $value) {
                    $html .= '<div><div style = "display:inline-block !important;width:200px;text-align:left;">' .$name .	 '</div><div style = "display:inline-block !important;width:400px;text-align:left;">' . $value .	'</div></div>';

                }
                $html .= '</div><hr>';

                // Anonymized Study

                if ($this->anonymize === true) {

                    $studydata = self::_Anonymize($orthanc_uuid);

                    foreach ($studydata as $name => $value) {
                    $html .= '<div><div style = "display:inline-block !important;width:200px;text-align:left;">' .$name .	 '</div><div style = "display:inline-block !important;width:400px;text-align:left;">' . $value .	'</div></div>';
                    }
                    $html .= '</div><hr>';
                }

            }

            $file_object->status = "Done";
            $request->session()->forget($KEY);
            $this->json_response = '{"file":' . json_encode($file_object) . ',"results":' . json_encode($html) .'}';
        }


        //$orthanc_uuid_array = [];  // contains unique uuid's for the upload
        // Checks to make sure the actual mime type, not just the extension, match one in the allowed list.

        if (!array_key_exists($file_type, $allowed_mimetypes)) {

            $file_object->status = "Skipping . .";
            $log_string .= "That Mime Type: ($file_type) is not allowed.\n";
            echo '{"file":' . json_encode($file_object) . ',"counter":"'	. $request->input('counter') .'"}';

            $log_string .= "That Mime Type: ($file_type) is not allowed.\n";
            //$error = true;
        }

        else {

            $orthanc_study_uuid = "";
            $success = move_uploaded_file($file_tmp, $upload_path);

            if ($success) {

                $this->logfiletext .= "The file " . basename($file_name) . " has been uploaded, processing . . .\n";

                if ($file_ext == "dcm" && basename($file_name) != "DICOMDIR.dcm" ) {
                    $result = self::_Process_and_Send(basename($file_name), $upload_path, $upload_dir);
                    if ($result) $orthanc_study_uuid = $result->ParentStudy;
                }
                else if (basename($file_name) == "DICOMDIR.dcm") {

                    $this->logfiletext .= "Stored DICOM Directory, not sent to PACS . . " . basename($file_name) . ".\n";
                }

                else {
                     $this->logfiletext .= "Non - .dcm file uploaded, not processed.\n";
                }

            }

            else {
            // failed to move the file to the directory for some reason.

                $this->logfiletext .= "Upload error for file: " . basename($file_name) . ".\n";
                $this->globalerror[] = "Upload error for file: " . basename($file_name) . ".\n";
            }
        }
        // Above just checks to make sure the file got uploaded and logs the results, error if there is some sort of upload error

        if (count($this->globalerror) > 0) {

            $request->session()->put($ABORTKEY,true);
            $file_object->status = "Aborting due to:	" . $file_name;
            // unset the upload
            $request->session()->forget($KEY);
            $this->logfiletext .= "There were " . count($this->globalerror) . "server errors.";
        }
        else {

            $file_object->status = "Uploaded";
            $this->json_response = '{"file":' . json_encode($file_object) . ',"counter":"'	. $this->request->input('counter') .'","UUID":"' . $orthanc_study_uuid . '","KEY":"' . $KEY . '","COUNTER":' . $this->request->input('counter') . '}';
        }
		file_put_contents($this->logfilepath , $this->logfiletext, FILE_APPEND );
	}

	public function UploadZipToPACS() {

		// Below is for just sending a raw zip
		// No logging to local file here
		if ($this->altertags == "true") {
			echo '{"status":"Not implemented, Orthanc does not really allow."}';
			die();
		}


		$result = $this->sendImageToOrthancWithExpect ($_FILES['file']['tmp_name'], "application/zip");
		// Orthanc returns an array of results, depending upon how many images were in the study
		Log::info($result);
		die();
		$result = json_decode($result);
		$studies = [];
		$AlreadyStored = 0;
		$errors = 0;

		foreach ($result as $instance) {

			if ($instance->Status != "Success") {

				if ($instance->Status == "AlreadyStored") {
					$AlreadyStored++;
				}
                else {
                    $errors++;
                     // $this->json_response = '{"status":"There were errors with the zip upload:  "' . json_encode($instance) . '""}';
                }
		    }

			if (!in_array($instance->ParentStudy, $studies)) $studies[] = $instance->ParentStudy;

		}
		// write study data to database for the initial push to orthanc, modalty & count are not available here.

		// Might want to now group them by unique orthanc_uuid or StudyInstanceUID
// php-fpm_1            | NOTICE: PHP message: {
// php-fpm_1            |    "ID" : "1fe830d5-e4b2b6b1-675a99df-95f0840e-7bdb1cd5",
// php-fpm_1            |    "ParentPatient" : "4481916d-55b353cc-ae4cbad4-4c0e7cbd-33c0b045",
// php-fpm_1            |    "ParentSeries" : "68faa980-ff3c8bb5-687d3e59-33ebd2a6-dfdaef63",
// php-fpm_1            |    "ParentStudy" : "0481b781-6bb968a2-f387954f-45ef4906-56380937",
// php-fpm_1            |    "Path" : "/instances/1fe830d5-e4b2b6b1-675a99df-95f0840e-7bdb1cd5",
// php-fpm_1            |    "Status" : "AlreadyStored"
// php-fpm_1            | }


		$resultsummary = $this->_PostProcessUpload ($studies);


		$this->json_response = '{"status":".zip uploaded to PACS.  Errors:  ' . $errors. '.  AlreadyStored:  ' . $AlreadyStored . '","results":' . json_encode($resultsummary) . '}';

	}

	public function UploadZipPreProcess () {

		// Simply unzips the archive to the working directory in the config, using mrn and data/time as subdirectory
		$zip = new \ZipArchive;
		$iszip = $zip->open($_FILES['file']['tmp_name']);
		$target = config('myconfigs.PATH_DICOM_TMP_PARENT') . '/' . $this->PatientID . '/' . Date("Y-m-d-H-i-s") . '/';
		$this->logfilepath = $target . "UploadZip.log";

		if ($iszip !== TRUE) {
			file_put_contents($this->logfilepath, "Error Extracting as .zip file.  Exiting with error.  No changes.", FILE_APPEND );
			die('{"status":"Error Extracting as .zip file."}');
		}
		$zip->extractTo($target);
		$zip->close();
		$this->logfiletext .= "File Unizpped to " . $target . "\n";
		$this->_Process_Directory($target);

	}

	private function _Process_Directory($target) {


		// iterate through the unzipped directory recursively.
		$fileSystemIterator = new \RecursiveDirectoryIterator($target);
		// these are the tagas that we want to extract from the instances if we are processing a directory as opposed to just sending to Orthanc
		$UUIDArray =[]; // group into unique studies.

		foreach (new \RecursiveIteratorIterator($fileSystemIterator) as $fileinfo) {
            /*
            object(SplFileInfo)#1628 (2) { ["pathName":"SplFileInfo":private]=> string(102) "/nginx-home/LaravelPortal/TMPUPLOADS/test/2021-02-28-17-29-21/0°__FSE_T2_ax_esaote_2/IM-0001-0001.dcm" ["fileName":"SplFileInfo":private]=> string(16) "IM-0001-0001.dcm" }
            */
			$filename = $fileinfo->getFilename();
			$upload_path = $fileinfo->getpathName();
			$file_type	= mime_content_type($upload_path);

			if ($file_type != "application/dicom" && $file_type != "directory") {

				// delete non-dicome files, will leave a DICOMDIR if presents though.
				unlink($upload_path);
				$this->logfiletext .=  "Excluding & Deleting non-dcm file " . $filename . "\n";
			}

			else if (strpos ($filename ,"DICOMDIR") !== false) {   // str_contains is new in php 8, skip a DICOMDIR file

				$this->logfiletext .=  "Skipping DICOMDIR file." . $filename . "\n";
			}

			else if ($file_type == "application/dicom")  {

				$result = self::_Process_and_Send($filename, $upload_path, $target);

				if ($result != false && !in_array($result->ParentStudy, $UUIDArray)) $UUIDArray[] = $result->ParentStudy;

			}

			else {

				$this->logfiletext .=  "Should not get here, must be a " . $file_type . "\n";
			}
		}
		$this->logfiletext .= "Finished Preprocessing, writing to database." . "\n" .  "There were " . count($this->globalerror) .  " error(s).\nClick Upload Summary for details." ;

		// OK to create and write to the file now because no more reading and unlinking
		file_put_contents($this->logfilepath, $this->logfiletext , FILE_APPEND );
		$resultsummary = $this->_PostProcessUpload ($UUIDArray);
		$this->json_response = '{"status":"Zip uploaded & processed.  ' . "There were " . count($this->globalerror) .  " error(s).<br><br>Click Upload Summary for details." . '","results":' . json_encode($resultsummary) .'}';

		}

	private function _Process_and_Send($filename, $upload_path, $targetdirectory) {


		// Get the old Tags so they can be saved in the retired tag.
		$this->logfiletext .=  "Processing file:  " . $filename . "\n";
		// read the dicom tags using dcmtk
		$proc = proc_open(self::$dcmtk_path . 'dcmdump -s' . self::$dcmdumpargs . ' ' . '"' . $upload_path . '"',[
		0 => ['pipe','r'],	// STDIN
		1 => ['pipe','w'],	// STDOUT
		2 => ['pipe',$targetdirectory.'dimcomprocesserror.log', "a"] // STDERR
		],$pipes);
		//fwrite($pipes[0], '');
		fclose($pipes[0]);
		$stdout = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		$exitcode = proc_close($proc);

		if ($exitcode == 0) {

			$stdout = str_replace("(no value available)", '[]', $stdout);  // kind of a hack, but think it works for primitive reading without using a library.
			preg_match_all('#\((.*?)\)#', $stdout, $tag);
			preg_match_all("/\[([^\]]*)\]/", $stdout, $vr);
			// get the outside study PatientID and AccessionNumber
			$indexForAccessionNumber = array_search('0008,0050', $tag[1]);
			$indexForPatientID = array_search('0010,0020', $tag[1]);
			$indexForInstitutionName = array_search('0008,0080', $tag[1]);
			$AccessionNumber = $vr[1][$indexForAccessionNumber];
			$PatientID = $vr[1][$indexForPatientID];
			$InstitutionName = $vr[1][$indexForInstitutionName];
			$RETIRED_OtherPatientIDs = $PatientID . "|" . $AccessionNumber . '|' . $InstitutionName;
			// put the name/value pairs fro $tagmap into array for later use
			$tag[1] = array_values($tag[1]);
			$result =[];
			foreach ($tag[1] as $key => $value) {
			$result[] = array ("tag" => $value, "shortname" => self::$tagmap[ $value], "value" => $vr[1][$key]);
			}
			$this->logfiletext .=  "Extracted Tags:  " . json_encode($result) . "\n";
			$instances[] = $result;

			// Change the MRN to the Internal MRN, Change the Accession to "OutsideStudy", and set (0010,1000), RETIRED_OtherPatientIDs to $PatientID . "|" . $AccessionNumber;
			// These can all apparently be done in one call, which is nice.
			// Always Backup the existing triplet, PatientID/AccessionNumber/InstitutionName in the RETIRED_OtherPatientIDs tag
			// If $this->altertags is true, then also alter the triplet, otherwise, just backup.
			if ($this->altertags == "false") {
				$args = self::$dcmtk_path . 'dcmodify -nb -i "(0010,1000)=' . $RETIRED_OtherPatientIDs . '" "' . $upload_path . '"';
			}
			else {
				$args = self::$dcmtk_path . 'dcmodify -nb -i "(0010,0020)=' .	$this->PatientID . '" -nb -i "(0008,0050)=' . $this->AccessionNumber . '" -nb -i "(0008,0080)=' . $this->InstitutionName . '" -nb -i "(0010,1000)=' . $RETIRED_OtherPatientIDs . '" "' . $upload_path . '"';
			}
			$proc = proc_open($args,[

			0 => ['pipe','r'],	// STDIN
			1 => ['pipe','w'],	// STDOUT
			2 => ['pipe',$targetdirectory . DIRECTORY_SEPARATOR . 'dimcomprocess.log', "a"] // STDERR
			],$pipes);
			//fwrite($pipes[0], '');
			fclose($pipes[0]);
			$stdout = stream_get_contents($pipes[1]);
			fclose($pipes[1]);

			$exitcode = proc_close($proc);

			if ($exitcode != 0) {

				$this->logfiletext .=  "Error Changing PatientID:  " . $filename . ".\n";
				$this->globalerror[] = "Error Changing PatientID:  " . $filename;
				return false;

			}

			else {

				// SEND THE PROCESSED INSTANCE TO ORTHANC
				$this->logfiletext .=  "Edited Tags with Modifications.  RETIRED_OtherPatientIDs now:  " . $RETIRED_OtherPatientIDs . ", sending to Orthanc:	 " . $filename . "\n";
				$result = self::sendImageToOrthancWithExpect ($upload_path, "application/dicom");
				$this->logfiletext .= "Orthanc Response:  " . json_encode($result) . "\n";
				return json_decode($result);
			}

	}

	}

	private static function _Get_HTMLSummary_From_Array($studydata) {

		$html = '<div style = "border: 1px solid black;">';
		foreach ($studydata as $name => $value) {
			$html .= '<div><div style = "display:inline-block !important;width:200px;text-align:left;">' .$name .	 '</div><div style = "display:inline-block !important;width:400px;text-align:left;">' . $value .	'</div></div>';
		}
		$html .= '</div><hr>';
		return $html;

	}

	// PROCEESSES AN ARRAY OF STUDY ID'S(uuids), writes to Database and handles the anonymization
	// _Anonymize creates the anonymized study, writes it to the database and returns the study data as the array that was inserted into the database.

	private function _PostProcessUpload ($UUIDArray) {

		$html = "";

		foreach ($UUIDArray as $orthanc_uuid) {

			if ($this->anonymize === true) {

				$html .= self::_Get_HTMLSummary_From_Array(self::_Anonymize($orthanc_uuid));
				if(self::$deleteAferAnonymization == true) {
				 	self::_DeleteStudy($orthanc_uuid);
				}
				else {
					$studydata = $this->writeStudySummaryToDatabase($orthanc_uuid);
					$html .= self::_Get_HTMLSummary_From_Array($studydata);
				}
			}

			else {
			$studydata = $this->writeStudySummaryToDatabase($orthanc_uuid);
			$html .= self::_Get_HTMLSummary_From_Array($studydata);
			}

		}
		return $html;

	}

	private static function _DeleteStudy($uuid) {

		// Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,self::$OrthancURL . 'studies/' . $uuid);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
// 		self::logVariable("DeleteStudy:	 " . self::$OrthancURL . 'studies/' . $uuid);
		$headers[] = 'Authorization:' . self::$Authorization;
		$headers[] = 'Token:' . self::$Token;
		$headers[] = 'Origin:' . self::$origin;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
// 		self::logVariable($result);
		if (curl_errno($ch)) {
			$error = curl_error($ch);
			curl_close($ch);
			return '{"error":"' . $error . '"}';
		}
		curl_close($ch);
		return $result;


	}
}

?>