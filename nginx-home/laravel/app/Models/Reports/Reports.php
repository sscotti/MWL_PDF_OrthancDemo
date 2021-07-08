<?php

namespace App\Models\Reports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use \DB;

/**
 * @property string     $HL7_message
 * @property string     $orthanc_uuid
 * @property string     $mrn
 * @property string     $accession_number
 * @property int        $telerad_contract
 * @property string     $reader_id
 * @property string     $newstatus
 * @property string     $oldstatus
 * @property DateTime   $datetime
 * @property string     $htmlreport
 * @property string     $template_id
 * @property string     $json_request_orthanc_add_pdf
 * @property DateTime   $updated_at
 * @property int        $created_at
 */
class Reports extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql2';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'reports';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'HL7_message', 'orthanc_uuid', 'mrn', 'accession_number', 'telerad_contract', 'reader_id', 'newstatus', 'oldstatus', 'datetime', 'htmlreport', 'template_id', 'json_request_orthanc_add_pdf', 'updated_at', 'created_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'HL7_message' => 'string', 'orthanc_uuid' => 'string', 'mrn' => 'string', 'accession_number' => 'string', 'telerad_contract' => 'int', 'reader_id' => 'string', 'newstatus' => 'string', 'oldstatus' => 'string', 'datetime' => 'datetime', 'htmlreport' => 'string', 'template_id' => 'string', 'json_request_orthanc_add_pdf' => 'string', 'updated_at' => 'datetime', 'created_at' => 'timestamp'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'datetime', 'updated_at', 'created_at'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;

    // Scopes...

    // Functions ...

    private static function renderComponent($path) {
		// setup mostly for getting css, etc. from <includes.reportheader.blade.php
		return view($path);
	}

    private static function parseHL7($message) {

        Log::info(" parseHL7");
        Log::info($message);
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

	public static function getHeaderFooterFromHL7($segments) {

		// OBX11 has the observation status.
		$translatestatus = array("P" => "PRELIM", "F" => "FINAL", "C" => "ADDENDUM");
		$MSH = $segments['MSH'];
		$PID = $segments['PID'];
		$OBR = $segments['OBR'];
		// $ORC = $segments['ORC'];
		$OBX = $segments['OBX'];

		$referringphysician = $OBR[16][1] . (!empty($OBR[16][2])?" " . $OBR[16][2]:"") . " " . substr($OBR[16][0], strpos($OBR[16][0], ":") + 1) . " " . $OBR[16][4];
		$referringphysicianid = $OBR[16][0]; // better method to get referrer.
		// put below into config
		$dob = \DateTime::createFromFormat('Ymd', $PID[7][0]);
		(!$dob)?$dob = "Not available":$dob = $dob->format('M-d-Y');

		// $studydate = \DateTime::createFromFormat('YmdHis', $OBR[36][0]);
		if (strlen($OBR[36][0]) == 8) {
		    $studydate = \DateTime::createFromFormat('Ymd', $OBR[36][0]);
		    (!$studydate)?$studydate = "Not available":$studydate = $studydate->format('M-d-Y');
		}
		else if (strlen($OBR[36][0]) == 14) {
		    $studydate = \DateTime::createFromFormat('YmdHis', $OBR[36][0]);
		    (!$studydate)?$studydate = "Not available":$studydate = $studydate->format('M-d-Y H:i:s');
		}
// 		$studydate = \DateTime::createFromFormat('YmdHis', $OBR[36][0]);
// 		(!$studydate)?$studydate = "Not available":$studydate = $studydate->format('M-d-Y H:i:s');

		$reportdate = \DateTime::createFromFormat('YmdHis', $OBX[14][0]);
		(!$reportdate)?$reportdate = "Not available":$reportdate = $reportdate->format('M-d-Y H:i:s');
		$reportheadercss = self::renderComponent("includes.reportheader"); // Config::get("REPORT_CSS")
		$facility = ""; // FacilityModel::letterHeader(Config::get("DEFAULT_FACILITY_ID"), true);

		Log::info("getHeaderFooterFromHL7");
		Log::info($referringphysician);
		Log::info($referringphysicianid);
		Log::info($dob);
		Log::info($studydate);
		Log::info($reportdate);
		Log::info($reportheadercss);
		Log::info("OBR");
		Log::info(json_encode($OBR, JSON_PRETTY_PRINT));
		$patientname = (!empty($PID[5][0] )?$PID[5][0] :"") . ', ' . (!empty($PID[5][1] )?$PID[5][1] :"");

		$header = $reportheadercss . $facility .  '<div id = "reportnoheader"><table id = "header_info">
		<tr>
			<td id="report_name"> Patient Name: ' . $patientname . '</td>
			<td id="report_mrn"> Med Rec Number:  ' . $PID[3][0] . '</td>
			<td rowspan = "6" style="vertical-align:text-top;white-space:break-spaces;width:200px">Indication:  ' . $OBR[13][0]  . '</td>
		</tr>
		<tr>
			<td> DOB: ' . $dob .  '</td>
			<td> Sex: ' . $PID[8][0] .  '</td>
		</tr>
	<tr>
	<td> Accession Number:  '  . $OBR[2][0] .  '</td>
	<td> Date of Exam:  '  . $studydate .  '</td>
	</tr>
	<td> Referring Physician:  '  . $referringphysician .  '</td>
	<td> Referring Physician ID:  '  . $referringphysicianid .  '</td>
	</tr>
	<tr>
	<td> Interpreting Radiologist:  '  . $OBX[16][2] . (!empty($OBX[16][3])?" " . $OBX[16][3]:"") . " " . $OBX[16][1] . " " . $OBX[16][5] .  '<br>Interpreting Radiologist Profile ID:' . $OBX[16][0] . '</td>
	<td> Report Generated:  '  .  $reportdate .  '</td>
	</tr>

	<tr>
	<td colspan= "2"> Read Status:  '  . $translatestatus[$OBX[11][0]] .  '</td>
	</tr>
	</table>';
		$date = \DateTime::createFromFormat('YmdHis', $OBX[14][0]);
		$datetime = $date->format('Y-m-d H:i:s');
		$footer = '<div id = "sigblock">' . $translatestatus[$OBX[11][0]] .
	'<br>Electronically signed:<br><br>Reader Profile:  '  . $OBX[16][0] .  '<br>'  . $OBX[16][2] . (!empty($OBX[16][3])?" " . $OBX[16][3]:"") . " " . $OBX[16][1] . " " . $OBX[16][5] . '<br>'  . $datetime . '</div>';
		$markup['header'] = $header;
		$markup['footer'] = $footer;
		$markup['footer'] .= '<div id = "disclaimer">' . self::renderComponent("includes.reportdisclaimer") . '</div></div>';
		$markup['body'] = '<div class = "htmlmarkup" name="htmlmarkup">' . str_replace("\\.br\\", "<br>", $OBX[5][0]) . '</div>';
		$markup['readername'] = $OBX[16][2] . (!empty($OBX[16][3])?" " . $OBX[16][3]:"") . " " . $OBX[16][1] . " " . $OBX[16][5];
		$markup['readerid'] = $OBX[15][0];


		return $markup;
	}

    public static function getallhl7_reports($accession_number, $lastreport = null)  {


		$reports = self::getAllReportsByAccession($accession_number);
		// Log::info(json_encode($reports));
		$hl7 = [];
		$json = array("user_email" => Auth::user()->email);
		foreach ($reports as $report) {
			$hl7[] = self::parseHL7($report->HL7_message);
		}
		// Log::info(json_encode($hl7));
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
			return '{"reports":' .json_encode($json) . '}';
		}
		else return $json["hl7"];

	}

	public static function getAllReportsByAccession($accession_number)  {

  		$query = "SELECT * FROM reports WHERE accession_number = ? ORDER BY datetime DESC";
  		$params = [$accession_number];
  		// Log::info("getAllReportsByAccession");
  		$result = DB::connection('mysql2')->select($query,$params);
  		return $result;
  	}

  	public static function getLastReportStatusByAccession($accession_number)  {

  		$query = "SELECT newstatus FROM reports r1 WHERE datetime = (SELECT MAX(datetime) FROM reports r2 WHERE r1.accession_number = r2.accession_number) AND r1.accession_number = ?";
  		$params = [$accession_number];
  		// $result = DatabaseFactory::selectByQuery($query, $params)->fetchAll(PDO::FETCH_OBJ);
		$result = DB::connection('mysql2')->select($query,$params);
  		if (count($result) == 1) return $result[0];
  		if (count($result) == 0 ) return false;
  		if (count($result) > 1 ) return "error";

  	}

    // Relations ...
}
