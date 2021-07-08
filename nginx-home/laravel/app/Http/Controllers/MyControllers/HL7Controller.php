<?php

namespace App\Http\Controllers\MyControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Aranyasen\HL7;

use \DB;
use Illuminate\Support\Facades\Auth;
use ReallySimpleJWT\Token;
use Illuminate\Support\Facades\Log;

use App\Actions\Orthanc\OrthancAPI;
use App\Models\TeleradContracts;
use App\Models\Patients\Patients;
use App\Models\Facility;
use App\Models\Reports\Reports;

use Illuminate\View\Component;

class HL7Controller extends Controller

{

// Alternate HL7 library to replace mine, mostly the HL7Model

	/**
	 * Construct this object by extending the basic Controller class.

	 */

	private $SENDING_APP;
    private $SENDING_FACILITY;
    private $RECEIVING_APP;
    private $RECEIVING_FACILITY;
    private $FACILITY;
    private $HL7Version = "2.8";
    private $request;
    private $reader_id;

	public function __construct(Request $request)

	{

		$this->request = $request;
		$this->reader = auth()->user();
		Log::info($this->request);

		$this->FACILITY = Facility::where('id', 1)->first();
        // Log::info($this->FACILITY);

		$this->SENDING_APP = "RIS^" . config('app.APP_URL') . "^DNS"; //"RIS^" . $_SERVER['HTTP_HOST'] . "^DNS";
		$this->SENDING_FACILITY = $this->FACILITY->name . "^" . config('app.APP_URL') . "^DNS"; //$this->FACILITY->name . "^" . $_SERVER['HTTP_HOST'] . "^DNS";
		$this->RECEIVING_APP = "RIS-ORTHANC^" . config('app.APP_URL') . "^DNS"; //"RIS-ORTHANC^" . $_SERVER['HTTP_HOST'] . "^DNS";
		$this->RECEIVING_FACILITY = 'RIS-ORTHANC^' . config('app.APP_URL') . '^DNS'; //'RIS-ORTHANC^' . $_SERVER['HTTP_HOST'] . '^DNS';

		$_SESSION["jsonmessages"]["HL7"] = [];  //begingging of json object, single one.
		// VERY IMPORTANT: Only allow readers, staff, biller, admin and superadmin to access this controller.
		// Auth::checkAuthentication([1,2,3,4,5,6,7,8]);  // basically any user, some features are further disabled below

	}

	private static function renderComponent($path) {
		// setup mostly for getting css, etc. from <includes.reportheader.blade.php
		return view($path);
	}

	public static function getAge($dob) {

		$from = new DateTime($dob);
		$to   = new DateTime('today');
		return $from->diff($to)->y;

	}

	public function parseHL7($message) {

	$parsemessage = [];
	$segments = explode("\r", $message);  // may have to adjust EDITOS

	foreach ($segments as $segment) {

		$fields = explode("|", $segment);
		$segmentname = $fields[0];
		if ($segmentname == "MSH") $offset = -1;
		else $offset = 0;

		foreach ($fields as $fieldindex => $field) {
			if ($segmentname != "MSH" || $fieldindex != 1) {
			$components = explode("^", $field);
			}
			else {
			$components = [$field];
			}

			foreach ($components as $componentindex => $component) {
				$parsemessage[$segmentname][$fieldindex + $offset][$componentindex] = $component;
			}
		}
	}
	return $parsemessage;

	}

	public function submit_report () {


		Log::info(auth()->user());
		if (!empty(auth()->user()->reader_id)) {  // has a reader_id and reader privileges
		$ORTHANC = new OrthancAPI();
		$studydata = $ORTHANC->getStudyDetails($this->request->uuid);
		Log::info(json_encode($studydata,JSON_PRETTY_PRINT));

        // Will have to adjust things so that a reader can have more than one contract configured, but works for now.
        $contract = TeleradContracts::where('reader_id', auth()->user()->reader_id)->first();
        Log::info($contract);

		$readername = $contract->doc_lastname . '^' . $contract->doc_firstname . '^' . $contract->doc_middlename . '^^^' . $contract->doc_suffix;
		// one of these might be not needed.
		$readerid = $contract->id;
		$reader_id = $contract->reader_id;
		$template_id= $this->request->template_id;
		if (empty($template_id)) $template_id = "No Template";
		$newstatus =$this->request->newstatus;
		$oldstatus = $this->request->oldstatus;


        $patientdata = Patients::where('mrn', $studydata->patientid)->first();
        Log::info($patientdata);


		// $patientdata = PatientModel::getPatientByMRNLocal($studydata->patientid);  // Grabs Pattient Data from Local Database
		if ($studydata->requested_procedure_id == "No Order") {  // could add a check for the studydata when the MWL works
			$_SESSION["jsonmessages"]["HL7"][] = '{"error":"No Order for this Accession, No changes."}';
			// die(json_encode($_SESSION["jsonmessages"]["HL7"]));
		}
		$studydata->study_description = str_replace("&", "and", $studydata->study_description);  // & breaks HL7.

		//  BUILD THE HL7 ORU MESSAGE !!



		$msg = new HL7\Message(null, array ('SEGMENT_SEPARATOR' => "\r", 'SEGMENT_ENDING_BAR' => false));
		$msh = new HL7\Segments\MSH();
		$msg->addSegment($msh);
		$msh->setSendingApplication($this->SENDING_APP);
		$msh->setSendingFacility($this->SENDING_FACILITY);
		$msh->setReceivingApplication($this->RECEIVING_APP);
		$msh->setReceivingFacility($this->RECEIVING_FACILITY);
		$msh->setMessageType("ORU^R01");
		$msh->setTriggerEvent("ORU_R01");
		$msh->setProcessingId("P");
		$msh->setVersionId($this->HL7Version);
		$msh->setSequenceNumber("1");
		$msh->setAcceptAcknowledgementType("AL");
		$msh->setApplicationAcknowledgementType("AL");
		$msh->setCountryCode("US");
		$msh->setCharacterSet("UNICODE");
		$msh->setCountryCode($this->FACILITY->country_code_3);
		$msh->setPrincipalLanguage("en");

		// Could also use the Study Data instead of that retrieved from the RIS ?

		$patientname = $patientdata->last . '^' . $patientdata->first . '^' . $patientdata->mname;
		$patientphone1 = $patientdata->mobile_phone_country . ' ' . $patientdata->mobile_phone;
		$patientphone2 = $patientdata->alt_mobile_phone_country . ' ' . $patientdata->alt_mobile_phone;
		$patientaddress = $patientdata->address_1 . '^' . $patientdata->address_2 . '^' . $patientdata->city. '^' . $patientdata->state . '^' . $patientdata->postal. '^' . $patientdata->country;
		$patientemail1 = $patientdata->email;
		$patientemail2 = $patientdata->alt_email;



		// USING THE DICOM TAGS FROM THE STUDY ONLY DATABASE BASED ON THE MATCHING MRN FOR THE STUDY

		$pid = new HL7\Segments\PID();
		$msg->addSegment($pid);
		$pid->setID("1");
		$pid->setPatientID($studydata->patientid); // DEV0000005^Check Digit^Check Digit Scheme^Assigning Authority^Identifier Type Code^Assigning Facility"
		$pid->setPatientIdentifierList($studydata->patientid . '^^^'.$this->FACILITY->name.'^MR');
		$pid->setAlternatePatientID($studydata->patientid);
		$pid->setPatientName($studydata->patient_name); // Last^First^Middle^Suffix^Prefix^Degree"
		$pid->setDateTimeOfBirth($studydata->patient_birth_date);
		$pid->setSex($studydata->patient_sex);
		$pid->setPatientAlias("Alias");
		$pid->setPatientAddress($patientaddress);  //"StreetAddress1^StreetAddress2^City^State^Postal Code^Country"
		$pid->setCountryCode($patientdata->country);
		$pid->setPhoneNumberHome($patientphone1 .'^PRN^PH^' . $patientemail1);
		$pid->setPhoneNumberBusiness($patientphone2 .'^WPN^PH^' . $patientemail2);
		$pid->setPrimaryLanguage("Language");
		$pid->setMaritalStatus($patientdata->marital_status);
		$pid->setReligion("");
		$pid->setPatientAccountNumber($studydata->patientid . '^^^'.$this->FACILITY->name.'^MR');
		$pid->setSSNNumber('');

		$pv1 = new HL7\Segments\PV1();
		$msg->addSegment($pv1);
		$pv1->setPatientClass("O"); // O is for outpatient, see https://hl7-definition.caristix.com/v2/HL7v2.8/Tables/0004
// 		$pv1->setAttendingDoctor($order->referring_physician_id . $order->referring_physician );
// 		$pv1->setReferringDoctor($order->referring_physician_id . $order->referring_physician);
// 		$pv1->setServicingFacility($order->sending_facility);


		$coded_exam = json_decode($studydata->coded_exam);


		// https://hl7-definition.caristix.com/v2/HL7v2.3/Segments/ORC



		$report = str_replace("\n", "<br>", $this->request->report); // convert the returns to HTML
		$report =preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F\x0D]/', '', $report);  // control characters
		$report = str_replace("&nbsp;", " ", $report);  // html_entity_decode leaves nbsp alone
		$report = html_entity_decode($report);  // converts html entities back to unencoded values.
		// strip out ^, \, ~
		// change & to and
		$report = preg_replace("/[\^\\\~]/", "", $report);  // for HL7
		$report = preg_replace("/[\&]/", "and", $report); // for HL7

		$obr = new HL7\Segments\OBR();
		$msg->addSegment($obr);
		$obr->setID(1);
		$obr->setPlacerOrderNumber($studydata->accession_number);
		$obr->setFillerOrderNumber($studydata->accession_number);
		$obr->setUniversalServiceID($studydata->requested_procedure_id . '^' . $studydata->study_description . '^LB^' . $studydata->coded_exam . '^' . $studydata->study_description . '^L'); //"ID^TEXT^CODING^ALT^TEXT^CODE"
		//$obr->setPriority($order->priority);
		$obr->setRequestedDatetime($studydata->study_date . substr(str_replace(":", "", $studydata->study_time),0,6));
		$obr->setObservationDateTime(date("YmdHis"));
		$obr->setRelevantClinicalInfo($studydata->indication);
		$obr->setOrderingProvider($studydata->referring_physician); //"ID^LAST^FIRST^MIDDELE^SUFFIX^PREFIX^DEGREE"
		$obr->setFillerField1(implode(",",$studydata->modalities));
		$translate = array("PRELIM" => "P", "FINAL" => "F", "ADDENDUM" => "C");
		$obr->setResultStatus($translate[$newstatus]);
		//$obr->setOrderCallbackPhoneNumber($doctor->mobile_phone_country . $doctor->mobile_phone ."^WPN^PH^" .$doctor->email);  NEED TO FIX THIS
		$obr->setDiagnosticServSectID("RAD");
		$obr->setScheduledDateTime($studydata->study_date . substr(str_replace(":", "", $studydata->study_time),0,6));

/*

OBR-25

A	Some, but not all, results available
C	Correction to results
F	Final results; results stored and verified. Can only be changed with a corrected result.
I	No results available; specimen received, procedure incomplete
O	Order received; specimen not yet received
P	Preliminary: A verified early result is available, final results not yet obtained
R	Results stored; not yet verified
S	No results available; procedure scheduled, but not done
X	No results available; Order canceled.
Y	No order on record for this test. (Used only on queries)
Z	No record of this patient. (Used only on queries)

*/
/*
Orcer controls

AF	Order/service refill request approval
CA	Cancel order/service request
CH	Child order/service
CN	Combined result
CR	Canceled as requested
DC	Discontinue order/service request
DE	Data errors
DF	Order/service refill request denied
DR	Discontinued as requested
FU	Order/service refilled, unsolicited
HD	Hold order request
HR	On hold as requested
LI	Link order/service to patient care problem or goal
MC	Miscellaneous Charge - not associated with an order
NA	Number assigned
NW	New order/service
OC	Order/service canceled
OD	Order/service discontinued
OE	Order/service released
OF	Order/service refilled as requested
OH	Order/service held
OK	Order/service accepted & OK
OP	Notification of order for outside dispense
OR	Released as requested
PA	Parent order/service
PR	Previous Results with new order/service
PY	Notification of replacement order for outside dispense
RE	Observations/Performed Service to follow
RF	Refill order/service request
RL	Release previous hold
RO	Replacement order
RP	Order/service replace request
RQ	Replaced as requested
RR	Request received
RU	Replaced unsolicited
SC	Status changed
SN	Send order/service number
SR	Response to send order/service status request
SS	Send order/service status request
UA	Unable to accept order/service
UC	Unable to cancel
UD	Unable to discontinue
UF	Unable to refill
UH	Unable to put on hold
UM	Unable to replace
UN	Unlink order/service from patient care problem or goal
UR	Unable to release
UX	Unable to change
XO	Change order/service request
XR	Changed as requested
XX	Order/service changed, unsol.

ORC-5

A	Some, but not all, results available
CA	Order was canceled
CM	Order is completed
DC	Order was discontinued
ER	Error, order not found
HD	Order is on hold
IP	In process, unspecified
RP	Order has been replaced
SC	In process, scheduled

*/
		// cannot use ^~\& in report text.

		//OBX  https://hapifhir.github.io/hapi-hl7v2/v25/apidocs/ca/uhn/hl7v2/model/v25/segment/OBX.html


		//     OBX-1: Set ID - OBX (SI) optional
		//     OBX-2: Value Type (ID) optional
		//     OBX-3: Observation Identifier (CE)
		//     OBX-4: Observation Sub-ID (ST) optional
		//     OBX-5: Observation Value (Varies) optional repeating
		//     OBX-6: Units (CE) optional
		//     OBX-7: References Range (ST) optional
		//     OBX-8: Abnormal Flags (IS) optional repeating
		//     OBX-9: Probability (NM) optional
		//     OBX-10: Nature of Abnormal Test (ID) optional repeating
		//     OBX-11: Observation Result Status (ID)
		//     OBX-12: Effective Date of Reference Range (TS) optional
		//     OBX-13: User Defined Access Checks (ST) optional
		//     OBX-14: Date/Time of the Observation (TS) optional
		//     OBX-15: Producer's ID (CE) optional
		//     OBX-16: Responsible Observer (XCN) optional repeating
		//     OBX-17: Observation Method (CE) optional repeating
		//     OBX-18: Equipment Instance Identifier (EI) optional repeating
		//     OBX-19: Date/Time of the Analysis (TS) optional


		$obx = new HL7\Segments\OBX();
		$msg->addSegment($obx);
		$obx->setID(1);
		$obx->setValueType('ED');
		$obx->setObservationSubId($template_id);
		$obx->setObservationIdentifier($studydata->requested_procedure_id . '^' . $studydata->study_description . '^LB^' . $studydata->coded_exam . '^' . $studydata->study_description . '^L');
		$obx->setObservationValue($report);
		// $obx->setObserveResultStatus($newstatus);
		$translate = array("PRELIM" => "P", "FINAL" => "F", "ADDENDUM" => "C");
		$obx->setObserveResultStatus($translate[$newstatus]);
/*
C	Record coming over is a correction and thus replaces a final result
D	Deletes the OBX record
F	Final results; Can only be changed with a corrected result.
I	Specimen in lab; results pending
N	Not asked; used to affirmatively document that the observation identified in the OBX was not sought when the universal service ID in OBR-4 implies that it would be sought.
O	Order detail description only (no result)
P	Preliminary results
R	Results entered -- not verified
S	Partial results. Deprecated. Retained only for backward compatibility as of V2.6.
U	Results status change to final without retransmitting results already sent as 'preliminary.' E.g., radiology changes status from preliminary to final
W	Post original as wrong, e.g., transmitted for wrong patient
X	Results cannot be obtained for this observation

*/
		$obx->setDateTimeOfTheObservation(date("YmdHis"));
		$obx->setProducersId($readerid);
		$obx->setResponsibleObserver($readerid . '^' . $readername);
		$obx->setProducersId($readerid);
		$obx->setObservationMethod('');
		$obx->setEquipmentInstanceIdentifier('');
		$obx->setDateTimeOfAnalysis(date("YmdHis"));

		$txa = new HL7\Segments\TXA();
		$msg->addSegment($txa);
		$txa->setSetID(1);
		$txa->setDocumentType("DI");
		$txa->setPrimaryActivityProvider($readerid . '^' . $readername);
		$txa->setTranscriptionDateTime(date("YmdHis"));
		$txa->setTranscriptionistCodeName($readerid . '^' . $readername);
		$txa->setUniqueDocumentNumber($studydata->accession_number . '-' . date("YmdHis"));
		$txa->setDocumentCompletionStatus("AU");
		$txa->setDocumentTitle($newstatus . ' report for ' . $studydata->accession_number);
/*
AU	Authenticated
DI	Dictated
DO	Documented
IN	Incomplete
IP	In Progress
LA	Legally authenticated
PA	Pre-authenticated
*/
		$txa->setDocumentChangeReason($newstatus);  // using this for status, PRELIM, FINAL, ADDENDUM

	/*
    AR	Autopsy report
	CD	Cardiodiagnostics
	CN	Consultation
	DI	Diagnostic imaging
	DS	Discharge summary
	ED	Emergency department report
	HP	History and physical examination
	OP	Operative report
	PC	Psychiatric consultation
	PH	Psychiatric history and physical examination
	PN	Procedure note
	PR	Progress note
	SP	Surgical pathology
	TS	Transfer summary
	*/
		$txa->setDocumentContentPresentation("AP");
		$txa->setDocumentTitle("Title");
	/*
	AP	Other application data, typically uninterpreted binary data (HL7 V2.3 and later)
	AU	Audio data (HL7 V2.3 and later)
	FT	Formatted text (HL7 V2.2 only)
	IM	Image data (HL7 V2.3 and later)
	multipart	MIME multipart package
	NS	Non-scanned image (HL7 V2.2 only)
	SD	Scanned document (HL7 V2.2 only)
	SI	Scanned image (HL7 V2.2 only)
	TEXT	Machine readable text document (HL7 V2.3.1 and later)
	TX	Machine readable text document (HL7 V2.2 only)
	*/

// 		$nte = new NTE();
// 		$msg->addSegment($nte);
// 		$nte->setID(1);
// 		$nte->setSourceOfComment("SOURCE");
// 		$nte->setComment("COMMENT");
// 		$nte->setCommentType("TYPE");
        Log::info("NEW:   " .$msg->toString(true));
		$message =  $msg->toString(true);
		// This is sort of legacy code to call the parseHL7, but sitll convenient.
		$parsedMessage = $this->parseHL7($message);
		$parsedMessage['study_date'] = $studydata->study_date;
		$parsedMessage['referring_physician'] = $studydata->referring_physician;
		$parsedMessage['referring_physician_id'] = "";
		$parsedMessage['study_time'] = $studydata->study_time;
		$parsedMessage['study_description'] = $studydata->study_description;
		$parsedMessage['modality'] = $studydata->modalities;
		$parsedMessage['indication'] = "";
		$headerfooter = self::getHeaderFooterFromHL7($parsedMessage);
		$report = $headerfooter["header"] . $headerfooter["body"] . $headerfooter["footer"];

// 		vs.
//
        $insertreport = Reports::create([

            'HL7_message' => $message,
            'orthanc_uuid' => $this->request->uuid,
            'mrn' => $studydata->patientid,
            'accession_number' => $studydata->accession_number,
            'telerad_contract' => auth()->user()->reader_id,
            'reader_id' => auth()->user()->reader_id,
            'oldstatus' => $oldstatus,
            'newstatus' => $newstatus,
            'htmlreport' => $report,
            'datetime' => date("Y-m-d H:i:s", time()),
            'template_id' => $this->request->template_id
        ]);


		// Use the Accesssion Number to get info from local database, pass the htmlreport
		//$id = DatabaseFactory::insertarray("reports", $array, null);

		$id = $insertreport->id;

		Log::info("Inserting Report into DB" . $message);
		Log::info("Insertion ID in Reports:  " . $id);


		// Handles notification of Patient and Referring Physician about new report.

		if (config('myconfigs.SEND_SMS_NOTIFICATIONS') == true) {
		    ReportsModel::sendNotifications($studydata->accession_number);
		    DB::table("SENDING SMS notifications");
		}

		/*
		"Value:": {
        "patient": "{\"status\":\"{\"statusCode\":200,\"effectiveUri\":\"https:\\\/\\\/sns.us-east-2.amazonaws.com\",\"headers\":{\"x-amzn-requestid\":\"12467858-1112-5795-be25-20c782433e02\",\"content-type\":\"text\\\/xml\",\"content-length\":\"294\",\"date\":\"Thu, 11 Feb 2021 20:18:24 GMT\"},\"transferStats\":{\"http\":[[]]}}\":\"MessageId\":\"dfb59a51-b9cf-5f10-bfc6-c2ce2a9e1687\"}",
        "referrer": "{\"status\":\"{\"statusCode\":200,\"effectiveUri\":\"https:\\\/\\\/sns.us-east-2.amazonaws.com\",\"headers\":{\"x-amzn-requestid\":\"3fe03119-d122-5ca9-bb10-69864273c839\",\"content-type\":\"text\\\/xml\",\"content-length\":\"294\",\"date\":\"Thu, 11 Feb 2021 20:18:24 GMT\"},\"transferStats\":{\"http\":[[]]}}\":\"MessageId\":\"f94ba14c-5fd5-513c-a005-f9d3cc8405ac\"}"
    	}
    	*/


		// HTML markup for report, attach PDF.

		if (config('myconfigs.REPORT_PDF') == 1) {

			$result = $ORTHANC->addPDF([

			    "method" => "html",
			    "html" => $report,
			    "base64" => "" ,
			    "title" => $newstatus,
			    "studyuuid" => $this->request->uuid,
			    "return" => 0,
			    "attach" => 1,
			    "author" => $headerfooter['readerid']
			]);

			$result = json_decode($result);

            Log::info("Sent for PDF to Orthanc" . json_encode($result, JSON_PRETTY_PRINT));

            if (isset($result->error)) {
				$error = $result->error;
				$error = preg_replace('/[\x00-\x1F\x7F]/', '', $error);
				$error = json_encode("Report Saved Locally, unable to save to PACS, " . addslashes($error));
				$_SESSION["jsonmessages"]["HL7"][] =  '{"status":' . $error . '}';
			}
			else if (isset($result->HttpError)) {
				$error = $result->HttpError . ', ' . $result->OrthancError . ', ' . $result->Uri;
				$_SESSION["jsonmessages"]["HL7"][] =  '{"status":"' . $error . '"}';
			}
			else {
				$_SESSION["jsonmessages"]["HL7"][] = '{"status":"Saved Locally and to PACS"}';
			}
		}

		else {
			$_SESSION["jsonmessages"]["HL7"][] =  '{"status":"Report Saved Locally"}';
		}
		}
		else {

			$_SESSION["jsonmessages"]["HL7"][] = '{"status":"Reader ID & Status needed to report"}';
		}
		echo json_encode($_SESSION["jsonmessages"]["HL7"]);
	}

	public function get_last_hl7() {


		$reports = $this->getallhl7_reports(true);
		$report = "";
		if (count($reports) == 0) {
			echo '[{"error":"No reports"}]';
		}
		else {
            $last = $reports[0];
            // check to verify that report belongs to a provider, and if not, a patient (only can see FINAL or ADDENDUM)
            
            $doctor_id = explode(":", $last['OBR'][16][0], 2);
            $doctor_id = $doctor_id[0];
            
            if (Auth::user()->doctor_id == $doctor_id  || (Auth::user()->patientid == $last['PID'][3][0] && $last['TXA'][21][0] != 'PRELIM')) {
                echo '[{"HL7":' . json_encode($last["header"] . $last["body"] . $last["footer"]) . ',"email":"' . Auth::user()->email . '"}]';
            }
            else {
            // If it is fromshared or staff pages then it just shows the report.
                echo '[{"error":"Not Your Study, or report not Final."}]';
		    }
		}

	}

	public static function searchForId($id, $array) {
		foreach ($array as $key1 => $value1) {  //  key1 is the segment, key2 is the position of the searched for value in the segment.
			foreach ($value1 as $key2 => $value2) {
			if ($value2 === $id) {
			   return array("segment"=> $key1, "field" => $key2);
			}
			}
		}
		return null;

	}

	public static function getHeaderFooterFromHL7($segments) {

		// OBX11 has the observation status.
		$translatestatus = array("P" => "PRELIM", "F" => "FINAL", "C" => "ADDENDUM");
		$MSH = $segments['MSH'];
		$PID = $segments['PID'];
		$OBR = $segments['OBR'];
		// $ORC = $segments['ORC'];
		$OBX = $segments['OBX'];
		$referringphysician = $OBR[16][1] . (!empty($OBR[16][2])?" " . $OBR[16][2]:"") . " " . substr($OBR[16][0], strpos($OBR[16][0], ":") + 1) . " " . (!empty($OBR[16][4])?" " . $OBR[16][4]:"");
		$referringphysicianid = $OBR[16][0]; // better method to get referrer.
		// put below into config
		$dob = \DateTime::createFromFormat('Ymd', $PID[7][0]);
		(!$dob)?$dob = "Not available":$dob = $dob->format('M-d-Y');

		$studydate = \DateTime::createFromFormat('YmdHis', $OBR[36][0]);
		(!$studydate)?$studydate = "Not available":$studydate = $studydate->format('M-d-Y H:i');

		$reportdate = \DateTime::createFromFormat('YmdHis', $OBX[14][0]);
		(!$reportdate)?$reportdate = "Not available":$reportdate = $reportdate->format('M-d-Y H:i');

		$facilityheader = Facility::letterHeader(config('myconfigs.DEFAULT_FACILITY_ID'), true);
		$css = self::renderComponent('includes.reportheader');
		$patientname = (!empty($PID[5][0] )?$PID[5][0] :"") . ', ' . (!empty($PID[5][1] )?$PID[5][1] :"");
		$header = '<div id = "reportnoheader"><table id = "header_info">
		<tr>
			<td id="report_name"> Patient Name: ' . $PID[5][0] . ', ' . $PID[5][1] . '</td>
			<td id="report_mrn"> Med Rec Number:  ' . $PID[3][0] . '</td>
		</tr>
		<tr>
			<td> DOB: ' . $dob .  '</td>
			<td> Sex: ' . $PID[8][0] .  '</td>
		</tr>
	<tr>
	<td> Accession Number:  '  . $OBR[2][0] .  '</td>
	<td> Date of Exam:  '  . $studydate .  '</td>
	</tr>
	<tr>
	<td> Referring Physician:  '  . $referringphysician .  ', ID:  ' . $referringphysicianid . '</td>
	<td> Radiologist:  '  . $OBX[16][2] . (!empty($OBX[16][3])?" " . $OBX[16][3]:"") . " " . $OBX[16][1] . " " . $OBX[16][5] .  ', ID: ' . $OBX[16][0] . '</td>
	</tr>
	<tr>
	<td> Report Date:  '  .  $reportdate .  '</td>
	<td colspan= "2"> Read Status:  '  . $translatestatus[$OBX[11][0]] .  '</td>
	</tr>

	<tr>
	<td colspan= "2">Indication:  ' . $OBR[13][0]  . '</td>
	</tr>
	</table>';
		$date = \DateTime::createFromFormat('YmdHis', $OBX[14][0]);
		$datetime = $date->format('Y-m-d H:i');
        $footer = '<div id = "sigblock">' . $translatestatus[$OBX[11][0]] . 
	'<br>Electronically signed:<br><br>Interpreting Radiologist:  '  . $OBX[16][2] . (!empty($OBX[16][3])?" " . $OBX[16][3]:"") . " " . $OBX[16][1] . " " . $OBX[16][5] . 'ID:  ' . $OBX[16][0] . '<br>'  . $datetime . '</div>';
        $markup['facilityheader'] = $facilityheader;
	    $markup['css'] = $css;
		$markup['header'] = $facilityheader . $css . $header;
		$markup['footer'] = $footer;
		$markup['footer'] .= '<div id = "disclaimer">' . self::renderComponent("includes.reportdisclaimer") . '</div></div>';
		$markup['body'] = '<div class = "htmlmarkup" name="htmlmarkup">' . str_replace("\\.br\\", "<br>", $OBX[5][0]) . '</div>';
		$markup['readername'] = $OBX[16][2] . (!empty($OBX[16][3])?" " . $OBX[16][3]:"") . " " . $OBX[16][1] . " " . $OBX[16][5];
		$markup['readerid'] = $OBX[15][0];

		return $markup;
	}

	public function getallhl7_reports($lastreport = null)  {


		$reports = Reports::getAllReportsByAccession($_POST['accession_number']);
		$hl7 = [];
		$json = array("user_email" => Auth::user()->email);

		foreach ($reports as $report) {
			$hl7[] = $this->parseHL7($report->HL7_message);
		}
		$json['hl7'] = $hl7;
		//DatabaseFactory::logVariable($hl7);
		foreach ($hl7 as $key => $value) {

			$segments = $value;
			//$segments['study_date'] = $order->scheduled_procedure_step_start_date;
			//$segments['study_time'] = $order->scheduled_procedure_step_start_time;
			//$segments['study_description'] = $order->description;
			//$segments['modality'] = $order->modality;
			//$segments['indication'] =$order->indication;
			$headerfooter = self::getHeaderFooterFromHL7($segments);
			$json['hl7'][$key]['header'] = $headerfooter['header'];
			$json['hl7'][$key]['footer'] = $headerfooter['footer'];
			$json['hl7'][$key]['body'] = $headerfooter['body'];

		}
		if ($lastreport == null) {
		$_SESSION["jsonmessages"]["HL7"][] = '{"reports":"getallhl7_reports call"}';
		 echo json_encode(['{"reports":' .json_encode($json) . '}']);
		}
		else return $json["hl7"];

	}

	public static function getUnixStartEnd($scheduleddate, $scheduledtime, $length) {

		//DatabaseFactory::logVariable($scheduleddate .$scheduledtime, );
		$date = date_create_from_format("YmdHis", $scheduleddate . $scheduledtime );
		$length = $length * 60;
		$date = $date->getTimestamp();
		$startend = array("start" => $date, "end" => $date + $length);
		return $startend;

	}

	public function submit_order() {

		$errors = "";

		if (!isset($_POST['fname']) || $_POST['fname'] == "" ) $errors.= 'No First Name.<br>';
		if (!isset($_POST['lname']) || $_POST['lname'] == "" ) $errors.= 'No Last Name.<br>';
		if (!isset($_POST['patient_birth_date']) || $_POST['patient_birth_date'] == "" ) $errors.= 'No DOB<br>';
		if (!isset($_POST['description']) || $_POST['description'] == "" ) $errors.='No Procedure(s).<br>';
		if (!isset($_POST['patient_sex']) || $_POST['patient_sex'] == "" ) $errors.= 'No Sex.<br>';
		if (!isset($_POST['patientid']) || $_POST['patientid'] == "" ) $errors.= 'No MRN.<br>';
		if (!isset($_POST['referring_physician']) || $_POST['referring_physician'] == "" ) $errors.= 'No Ordering Physician.<br>';
		if (!isset($_POST['indication']) || $_POST['indication'] == "" ) $errors.= 'No Indication.<br>';
		if (!isset($_POST['device']) || $_POST['device'] == "" ) $errors.= 'No Device.<br>';

		if ($errors == "") { // no errors decode the exam info, could make into function later

			/*
			if (in_array($_POST['status'], array("CA","NS","IP","CM"))) {

				$messages = [];
				$affected = OrderModel::updateStatusByAccession($_POST['accession_number'], $_POST['status']);
				if (in_array($_POST['status'], array("CA","NS"))) {
					// remove it from the calendar
					$affected = DatabaseFactory::deletetablerowbyKeyValue("appointments", "accession_number", $_POST['accession_number']);
					if ($affected != 0) $messages[] = '{"status":"Appointment Deleted"}';
					$query = 'UPDATE orders SET apptid = "" WHERE accession_number = ? ORDER BY timestamp DESC LIMIT 1';
					DatabaseFactory::selectByQuery($query, [$_POST['accession_number']]);
				}
				$messages[] = '{"status":"Status Changed","accession_number":"'  . $_POST['accession_number'] .  '", "appointment_id": "Deleted"}';
				$deleted = (new OrthancModel())->deleteMWLfile($_POST['accession_number']);
				if ($deleted) $messages[] = '{"status":"MWL deleted"}';
				echo json_encode($messages);
				die();
			}
			*/

			$order = new OrderModel();

			//  There are a lot of checks here.  All actions create an HL7 message for auditing, etc.

			//     NW - new order. PENDING
			//     XO - change an order same as placing an order
			//     CA - cancel an order. DISCONTINUED
			//     CM - Completed. COMPLETED
			//     IP - In Progress
			//     NS - No Show
/*
Some, but not all, results available
CA	Order was canceled
CM	Order is completed
DC	Order was discontinued
ER	Error, order not found
HD	Order is on hold
IP	In process, unspecified
RP	Order has been replaced
SC	In process, scheduled
*/
			// Get the accession and ajust the status
			if($_POST['accession_number'] == '' && $_POST['status'] == "NW") {  // might have an accession if they resubmit
			 $order->accession_number = $order->getNewAccession();
			}
			else if($_POST['accession_number'] == '' && $_POST['status'] != "NW") {
				die('[{"error":"No Accession and Order not New"}]');
			}
			else if($_POST['accession_number'] != '' && $_POST['status'] == "NW") {  // must be an modificaiton with NW setting
				$_POST['status'] = "XO";
				$order->accession_number = $_POST['accession_number'];
			}
			else {  // must have an accession and not a new order
				$order->accession_number = $_POST['accession_number'];
			}


			$scheduledtime = "";
			$scheduleddate = "";
			$scheduled = false;
			$dateforbilling = false;
			// set the time and date to correct format for AMBRA and MySQL

			if ($_POST['scheduled_procedure_step_start_date'] != '' && $_POST['scheduled_procedure_step_start_time'] != '' ) {
				$scheduleddate = strtotime($_POST['scheduled_procedure_step_start_date']);
				$scheduleddate = date("Ymd", $scheduleddate);
				$order->scheduled_procedure_step_start_date = $scheduleddate;
				$scheduledtime =  substr(str_replace(":", "", $_POST['scheduled_procedure_step_start_time']), 0,4) . "00";  // for th HL7 messages and database.
				$order->scheduled_procedure_step_start_time = $scheduledtime;  // for the local database, seconds
				$scheduled = true;
				$dateforbilling = $_POST['scheduled_procedure_step_start_date'];
				}

			else {

				$order->scheduled_procedure_step_start_date = null;
				$order->scheduled_procedure_step_start_time = null;

			}

			if ($scheduled == true && $_POST['status'] != "NS"  &&  $_POST['status'] != "CA" ) {

			$startend = self::getUnixStartEnd($order->scheduled_procedure_step_start_date, $order->scheduled_procedure_step_start_time, $order->exam_length);
			$result = AppointmentsModel::appointmentTaken($startend["start"], $startend["end"],$order->accession_number, $order->calendar_id);

			if($result > 0) {

				die('[{"error": "There is already an appointment in that slot."}]');
			}

			}

			// This was changed to be the requested_procedure_id

			$examdata = ExamsModel::getExamsByQuery('SELECT * from exams WHERE requested_procedure_id = ?', [$_POST['description']], $dateforbilling)[0];
			$order->coded_exam = json_encode($examdata);
			$order->timestamp = date("Y-m-d H:i:s");
			$order->requested_procedure_id = $_POST['description'];
			$order->coding_scheme = $examdata->code_type;
			$order->description = $examdata->exam_name;
			$order->exam_length = $examdata->exam_length;
			$order->modality = $examdata->modality;

			// Updating a scheduled exam with CM or IP, some validation checks to make sure some items match the existing appointment.

			if ($_POST['status'] == "CM"  ||  $_POST['status'] == "IP" ) {
				$query = 'SELECT * from appointments WHERE accession_number = ?';
				$params = [$order->accession_number];
				$result = DatabaseFactory::selectByQuery($query,$params);
				if ($result->rowCount() == 1) {
					$result = $result->fetchAll(PDO::FETCH_OBJ)[0];
					if (str_replace("-","",$result->start_date) != $scheduleddate || str_replace(":","",$result->start_time) != $scheduledtime || ($result->unix_end - $result->unix_start)  != $order->exam_length *60) {
						die('[{"error": "Cannot change scheduled Date/Time with CM or IP, only to change Status"}]');
					}
					$order->apptid = $result->id;
					DatabaseFactory::logVariable($result);
				}
				else {
					die('[{"error": "Cannot use CM or IP if no scheduled exam."}]');
				}
			}

			$order->priorauth = $_POST['priorauth'];

			$object = json_decode($_POST['device']);
			$order->scheduled_station_aetitle = $object->scheduled_station_aetitle;
			$order->calendar_id = $object->id;
			// {"id":"1","device_id":"1","device_name":"MR-Esaote","modality":"MR","scheduled_station_aetitle":"AETITLE_MRI"}


			$doctor = DoctorsModel::getDoctorByIdentifier($_POST['referring_physician']);
			$order->referring_physician_id = $doctor->identifier;
			$order->referring_physician = $doctor->lname . "^" . $doctor->fname. '^' . $doctor->mname. '^' . $doctor->provider_suffix . '^' . $doctor->prefix . '^' . $doctor->degree;
			$order->patient_birth_date = date("Ymd", strtotime($_POST['patient_birth_date']));
			$order->patient_name = $_POST['lname'] . '^' . $_POST['fname'] . '^' . $_POST['mname'] ;
			$order->patient_fname = $_POST['fname'];
			$order->patient_lname= $_POST['lname'];
			$order->patient_mname = $_POST['mname'];
			$order->patient_sex = $_POST['patient_sex'];
			$order->patientid = $_POST['patientid'];
			$order->sending_facility = $this->FACILITY->name;  // prepopulated
			$order->indication = $_POST['indication'];
			$order->ourstatus = $_POST['status'];

			$order->related_employment= (isset($_POST['related_employment']) ?$_POST['related_employment']:"off");
			$order->related_auto = (isset($_POST['related_auto']) ?$_POST['related_auto']:"off");
			$order->related_otheraccident = (isset($_POST['related_otheraccident']) ?$_POST['related_otheraccident']:"off");
			$order->related_emergency= (isset($_POST['related_emergency']) ?$_POST['related_emergency']:"off");
			$order->related_drugs= (isset($_POST['related_drugs']) ?$_POST['related_drugs']:"off");
			$order->related_pregnancy= (isset($_POST['related_pregnancy']) && $_POST['related_pregnancy'] != ""  ? $_POST['related_pregnancy']:NULL);

			$order->employed= (isset($_POST['employed']) ?$_POST['employed']:"off");
			$order->employed_student= (isset($_POST['employed_student']) ?$_POST['employed_student']:"off");
			$order->employed_other= (isset($_POST['employed_other']) ?$_POST['employed_other']:"off");
			$order->illness_date= (isset($_POST['illness_date']) && $_POST['illness_date'] != ""  ? $_POST['illness_date']:NULL);
			$order->priority = $_POST['priority'];

			//die();
			$patient = new PatientModel();

			$patient = PatientModel::getPatientByMRNLocal ($_POST['patientid']);  // would be uuid for AMBRA, later or by MRN


			$msg = new HL7\Message(null, array ('SEGMENT_SEPARATOR' => "\r", 'SEGMENT_ENDING_BAR' => false));
			$msh = new HL7\Segments\MSH();
			$msg->addSegment($msh);
			$msh->setSendingApplication($this->SENDING_APP);
			$msh->setSendingFacility($this->SENDING_FACILITY);
			$msh->setReceivingApplication($this->RECEIVING_APP);
			$msh->setReceivingFacility($this->RECEIVING_FACILITY);
			$msh->setMessageType("OMI^O23");
			$msh->setTriggerEvent("OMI_O23");
			$msh->setProcessingId("P");
			$msh->setVersionId($this->HL7Version);
			$msh->setSequenceNumber("1");
			$msh->setAcceptAcknowledgementType("AL");
			$msh->setApplicationAcknowledgementType("AL");
			$msh->setCountryCode("US");
			$msh->setCharacterSet("UNICODE");
			$msh->setCountryCode($this->FACILITY->country_code_3);
			$msh->setPrincipalLanguage("en");

			//PID

			// Marital status
			/*

			A 	Annulled	Marriage contract has been declared null and to not have existed
			D 	Divorced	Marriage contract has been declared dissolved and inactive
			I 	Interlocutory	Subject to an Interlocutory Decree.
			L 	Legally Separated
			M 	Married	A current marriage contract is active
			P 	Polygamous	More than 1 current spouse
			S 	Never Married	No marriage contract has ever been entered
			T 	Domestic partner	Person declares that a domestic partner relationship exists.
			U 	unmarried	Currently not in a marriage contract.
			W 	Widowed	The spouse has died
			*/

			$pid = new HL7\Segments\PID();
			$msg->addSegment($pid);
			$pid->setID("1");
			$pid->setPatientID($order->patientid ); // DEV0000005^Check Digit^Check Digit Scheme^Assigning Authority^Identifier Type Code^Assigning Facility"
			$pid->setPatientIdentifierList($order->patientid  . '^^^'.$this->FACILITY->name.'^MR');
			$pid->setAlternatePatientID($order->patientid);
			$pid->setPatientName($order->patient_name); // Last^First^Middle^Suffix^Prefix^Degree"
			$pid->setDateTimeOfBirth($order->patient_birth_date);
			$pid->setSex($order->patient_sex);
			$pid->setPatientAlias("alias");
			$pid->setPatientAddress($patient->address_1 .'^' . $patient->address_2 . '^' . $patient->city  . '^' . $patient->state . '^' .  $patient->postal. '^' . $patient->country);  //"StreetAddress1^StreetAddress2^City^State^Postal Code^Country"
			$pid->setCountryCode($patient->country);
			$pid->setPhoneNumberHome($patient->mobile_phone_country . '-' . $patient->mobile_phone . '^' . 'PRN^PH^' . $patient->email);
			$pid->setPhoneNumberBusiness($patient->alt_mobile_phone_country . '-' . $patient->alt_mobile_phone . '^' . 'WPN^PH^' . $patient->alt_email);
			$pid->setPrimaryLanguage("Language");
			$pid->setMaritalStatus($patient->marital_status);
			$pid->setPatientAccountNumber($order->patientid. '^^^'.$this->FACILITY->name.'^MR');



			$pv1 = new HL7\Segments\PV1();
			$msg->addSegment($pv1);
			$pv1->setPatientClass("O"); // O is for outpatient, see https://hl7-definition.caristix.com/v2/HL7v2.8/Tables/0004
			$pv1->setAttendingDoctor($order->referring_physician_id . '^'. $order->referring_physician );
			$pv1->setReferringDoctor($order->referring_physician_id . '^'. $order->referring_physician);
			$pv1->setServicingFacility($this->FACILITY->name);
			$pv1->setAdmitDateTime($scheduleddate . $scheduledtime);


			$orc = new HL7\Segments\ORC();
			$msg->addSegment($orc);
			$orc->setOrderControl($order->ourstatus);
			$orc->setPlacerOrderNumber($order->accession_number);
			$orc->setFillerOrderNumber($order->accession_number);
			$orc->setOrderStatus($order->ourstatus);
			$orc->setQuantityTiming($scheduleddate . $scheduledtime);
			$orc->setDateTimeofTransaction( date("YmdHis"));
			$orc->setOrderingProvider($order->referring_physician_id . '^' . $order->referring_physician); // "ID^LAST^FIRST^MIDDELE^SUFFIX^PREFIX^DEGREE"
			$orc->setCallBackPhoneNumber($doctor->mobile_phone_country . '-' . $doctor->mobile_phone . '^WPN^PH^' . $doctor->email);  //"PHONE^CODE(WPN)^TYPE(PH)^EMAIL"
			$orc->setOrderEffectiveDateTime(date("YmdHis"));


			 $comment = "";
			// get the right status us and HL7.

			if ($order->ourstatus == "SC" && $scheduled == false) $order->ourstatus = "XO";  // change it to XO if not scheduled.
			if (($order->ourstatus == "SC" || $order->ourstatus == "NW" || $order->ourstatus == "XO") && $scheduled == true) $order->ourstatus = "SC";

			/* SET ID 1 WITH OBR AND NTE */
			$obr = new HL7\Segments\OBR();
			$msg->addSegment($obr);
			$obr->setID(1);
			$obr->setPlacerOrderNumber($order->accession_number);
			$obr->setFillerOrderNumber($order->accession_number);
			$obr->setUniversalServiceID($order->requested_procedure_id . '^'  . $order->description . '^LB^' . $order->coded_exam  . '^' . $order->description . '^L'); //"ID^TEXT^CODING^ALT^TEXT^CODE"
			$obr->setPriority($order->priority);
			$obr->setRequestedDatetime(date("YmdHis"));
			$obr->setRelevantClinicalInfo($order->indication);
			$obr->setOrderingProvider($order->referring_physician_id . '^' . $order->referring_physician); //"ID^LAST^FIRST^MIDDLE^SUFFIX^PREFIX^DEGREE"
			$obr->setOrderCallbackPhoneNumber($doctor->mobile_phone_country . '-' . $doctor->mobile_phone . '^WPN^PH^' . $doctor->email);
			$obr->setDiagnosticServSectID("RAD");
			$obr->setQuantityTiming($scheduleddate . $scheduledtime);  // "^^Duration^Start Date/Time^End Date/Time^Priority^"
			$obr->setScheduledDateTime($scheduleddate .  $scheduledtime);
			$obr->setField(44,$order->requested_procedure_id . '^'  . $order->description . '^LB^' . $order->coded_exam  . '^' . $order->description . '^L'); // "MAINID^TEXT^SYSTEM^ALT^TEXT^SYSTEM"
			$obr->setField(45,$order->requested_procedure_id . '^'  . $order->description . '^LB^' . $order->coded_exam  . '^' . $order->description . '^L');  // "MAINID^TEXT^SYSTEM^ALT^TEXT^SYSTEM"


			if ($order->ourstatus == "SC") {

				$order->StudyInstanceUID = Config::get('DEFAULT_DICOM_ROOT');
				$query = 'SELECT id, device_id FROM calendars WHERE device = 1 AND active = 1 AND scheduled_station_aetitle = ?';
				$params = [$order->scheduled_station_aetitle];
				$id = DatabaseFactory::selectByQuery($query, $params)->fetch(PDO::FETCH_OBJ)->device_id;
				$order->StudyInstanceUID .= $id . "." . $order->scheduled_procedure_step_start_date . $order->scheduled_procedure_step_start_time;


				$ipc = new HL7\Segments\IPC();
				$msg->addSegment($ipc);
				$ipc->setAccessionIdentifier($order->accession_number);
				$ipc->setRequestedProcedureId($order->requested_procedure_id);
				$ipc->setStudyInstanceUid($order->StudyInstanceUID);
				$ipc->setScheduledProcedureStepId("setScheduledProcedureStepId");
				$ipc->setModality($order->modality);
				$ipc->setProtocolCode($order->requested_procedure_id);
				$ipc->setScheduledStationName("setScheduledStationName");
				$ipc->setScheduledProcedureStepLocation("setScheduledProcedureStepLocation");
				$ipc->setScheduledStationAeTitle($order->scheduled_station_aetitle);

			}

			$order->message = $msg->toString(true);  // new class
			//DatabaseFactory::logVariable($order->message);



	//  CODE TO INSERT INTO LOCAL Database, left out description since it is in the proedure code, move to class function.

			$order->orderedbyuser_id = Session::get('user_id');
			$order->orderbyuser_name = Session::get('user_name');
			// ISO
			// 1.3 identified-organization,
			// 1.3.6 dod,
			// 1.3.6.1 internet,
			// 1.3.6.1.4 private,
			// 1.3.6.1.4.1 IANA enterprise numbers,
			// 1.3.6.1.4.1.343 Intel Corporation
	// 			In this example, the root is:
	//
	// 			1 Identifies ISO
	// 			2 Identifies ANSI Member Body
	// 			840 Country code of a specific Member Body (U.S. for ANSI)
	// 			xxxxx Identifies a specific Organization.(assigned by ANSI)
	//
	// 			In this example the first two components of the suffix relate to the identification of the device:
	//
	// 			3 Manufacturer defined device type
	// 			152 Manufacturer defined serial number
	//
	// 			The remaining four components of the suffix relate to the identification of the image:
	//
	// 			235 Study number
	// 			2 Series number
	// 			12 Image number
	// 			187636473 Encoded date and time stamp of image acquisition

			$insert = $order->insertNewOrder('orders', $order);  // always insert a new order message for record keeping.
			$_SESSION["jsonmessages"]["HL7"][] = '{"status":"Added to Database"}';

			if (in_array($_POST['status'], array("CA","NS","IP","CM"))) {

				// $affected = OrderModel::updateStatusByAccession($_POST['accession_number'], $_POST['status']);
				if (in_array($_POST['status'], array("CA","NS"))) {
					// remove it from the calendar
					$affected = DatabaseFactory::deletetablerowbyKeyValue("appointments", "accession_number", $order->accession_number);
					if ($affected != 0) $messages[] = '{"status":"Appointment Deleted"}';
					$query = 'UPDATE orders SET apptid = "" WHERE accession_number = ? ORDER BY timestamp DESC LIMIT 1';
					DatabaseFactory::selectByQuery($query, [$order->accession_number]);
					$_SESSION["jsonmessages"]["HL7"][] = '{"status":"Status Changed","accession_number":"'  . $order->accession_number  .  '", "appointment_id": "Deleted"}';
				}
				else { // Must be IP or CM, apptid was grabbed from the appointments at the start of the method

					$_SESSION["jsonmessages"]["HL7"][] = '{"status":"Status Changed","accession_number":"'  . $order->accession_number  .  '", "appointment_id": "' . $order->apptid . '"}';
				}
				$deleted = (new OrthancModel())->deleteMWLfile($order->accession_number);
				if ($deleted) $_SESSION["jsonmessages"]["HL7"][] = '{"status":"MWL deleted"}';
				echo json_encode($_SESSION["jsonmessages"]["HL7"]);
				die();
			}

			if  ($order->ourstatus == "XO") {

				$affected = DatabaseFactory::deletetablerowbyKeyValue("appointments", "accession_number", $order->accession_number);
				if ($affected != 0) $_SESSION["jsonmessages"]["HL7"][] =  '{"status":"Appointment Deleted","appointment_id": "Deleted"}';
				$deleted = (new OrthancModel())->deleteMWLfile($order->accession_number);
				if ($deleted) $_SESSION["jsonmessages"]["HL7"][] = '{"status":"MWL deleted"}';

			}

			else if ($order->ourstatus == "SC") {


				//  Update or Insert an appointment if the exam is in SC status

				$mwl = new MWLModel($order, "sendOrthancHL7");
				$payload = $mwl->payload;
				$response = $mwl->sendToOrthanc($payload);
				$_SESSION["jsonmessages"]["HL7"][] = $response;


				//     NW - new order. PENDING
				//     XO - change an order same as placing an order
				//     CA - cancel an order. DISCONTINUED
				//     CM - Completed. COMPLETED
				//     IP - In Progress
				//     NS - No Show

				$appointment = new AppointmentsModel();
				$appointment->fname = $order->patient_fname;
				$appointment->lname = $order->patient_lname;
				$appointment->accession_number = $order->accession_number;
				$appointment->order_id = $insert;
				$appointment->scheduled_station_aetitle = $order->scheduled_station_aetitle;
				$appointment->device = 1;
				$appointment->mrn = $order->patientid;
				$appointment->email = $patient->email;
				$appointment->alt_email = $patient->alt_email;
				$appointment->mobile_phone_full = $patient->mobile_phone_country . '-' . $patient->mobile_phone;
				$appointment->alt_mobile_phone_full = $patient->alt_mobile_phone_country . '-' . $patient->alt_mobile_phone;
				$appointment->reminder_sent = 0;

				$appointment->start_date= $scheduleddate;  // YYYYMMDD
				$appointment->start_time = $scheduledtime;  //HHMMSS
				$startend = self::getUnixStartEnd($appointment->start_date, $appointment->start_time, $order->exam_length);
				$appointment->unix_start = $startend['start'];
				$appointment->unix_end = $startend['end'];
				$appointment->end_date = date("Ymd", $startend['end']);
				$appointment->end_time = date("His", $startend['end']);

				$appointment->calendar_id = $order->calendar_id;
				$appointment->notes= $order->referring_physician. $order->referring_physician_id . ", Exam:  " . $order->description . ", Indication:  " . $order->indication;
				$appointment->status = $order->ourstatus;
				$appointment = (array) $appointment;

				$query = "SELECT * from appointments WHERE accession_number = ?";
				$params = [$order->accession_number];
				$result = DatabaseFactory::selectByQuery($query, $params);

				if ($result->rowcount() == 0) {

				$id = DatabaseFactory::insertarray("appointments", $appointment);
				$query = 'UPDATE orders SET apptid = ? WHERE accession_number = ? ORDER BY timestamp DESC LIMIT 1';
				DatabaseFactory::selectByQuery($query, [$id,$order->accession_number]);
				$_SESSION["jsonmessages"]["HL7"][] = '{"status": "Appointment Added","appointment_id":"' . $id . '"}';

				}
				else if ($result->rowcount() == 1) {  //  assumes there is only 1, which would be true.

					$appointment["id"] = $result->fetch()->id;
					DatabaseFactory::update("appointments", $appointment, null, "WHERE id = ?", [$appointment["id"]]);
					$query = 'UPDATE orders SET apptid = ? WHERE accession_number = ? ORDER BY timestamp DESC LIMIT 1';
					DatabaseFactory::selectByQuery($query,[$appointment["id"], $order->accession_number]);
					$_SESSION["jsonmessages"]["HL7"][] = '{"status": "Appointment Updated", "appointment_id":"' .$appointment["id"] . '"}';

				}
				else if  ($result->rowcount() > 1) {
				   $_SESSION["jsonmessages"]["HL7"][] = '{"error": "There is more than one appointment for that order ?"}';

				}

		}

		    $_SESSION["jsonmessages"]["HL7"][] = '{"accession_number": "' . $order->accession_number . '"}';
			$_SESSION["jsonmessages"]["HL7"][] =  '{"ourstatus":"'  . $order->ourstatus .  '"}';
			echo json_encode($_SESSION["jsonmessages"]["HL7"]);
		}

		else  {

			echo json_encode(array('{"error":"' . $errors . '"}'));  //JSON reaponse
		}

	}

	public function sendTestMWL() {

		echo MWLModel::TestMWL();

	}

}

?>

