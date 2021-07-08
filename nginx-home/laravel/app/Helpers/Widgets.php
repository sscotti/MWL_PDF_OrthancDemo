<?php
namespace App\Helpers;
use App\Actions\Orthanc\OrthancAPI;
use App\Helpers\DatabaseFactory;
use App\Helpers\PostgresOrthanc;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class Widgets  {

	private static $omitdelete;
	private static $auth;

    function __construct() {

    }

    public static function PACSSelectorTool($action) {

        $html = '<form class="row" id = "orthancselector" method = "POST" action = "' . $action . '">
        <select style = "width:200px;margin: auto;height:auto;" class="form-control" id="orthanc_host" name="orthanc_host">';
        $optionslist = "";
        $servers = OrthancAPI::getServersArray();

        if (count($servers) == 0) {
            $optionslist .= '<option disabled value="" selected="selected">You Do not have a Server Configured.</option>';
        }
        else if (count($servers) > 1  && empty(session('orthanc_host'))) {
            $optionslist.= '<option value="" selected="selected">SELECT A PACS VIEWER</option>';
        }
        foreach ($servers as $server) {

            $optionselected = "";

            if ( (!empty(session('orthanc_host')) && (session('orthanc_host') == $server->id)) || count($servers) == 1) {
                $optionselected = ' selected = "selected"';
            }
            $optionslist.='<option value="' . $server->id  . '"' .  $optionselected . '>' . $server->osimis_viewer_name  . '</option>';

        }
        $html .= $optionslist;
        $html .='</select><input type="hidden" name="_token" value="' . csrf_token() . '" /></form>';
        return $html;
    }
    
    
    public static function studiesSearchFormPatient () {
    
        $Referrers = DatabaseFactory::getReferrersSelectList('');
        $modalities = DatabaseFactory::getModalitySelectList();
    
    	return '<div class="row divtableheader widemedia">
			<div class="col-sm-2">Accession</div>
			<div class="col-sm-3">Description</div>
			<div class="col-sm-2">Referrer</div>
			<div class="col-sm-1">Modality</div>
			<div class="col-sm-2">Study Date</div>
			<div class="col-sm-2">Report Status</div>
		</div>

		<form name="searchform" id="searchform" class="divtable row">

            <div class="col-sm-2 nopadding"><span class="narrowmedia">Accession:  </span><input name="data-accession" data-orthanc = "AccessionNumber" class="searchparam" type="text"></div>
            <div class="col-sm-3 nopadding"><span class="narrowmedia">Description:  </span><input name="data-description" data-orthanc = "StudyDescription" class="searchparam" type="text"></div>
             <div class="col-sm-2 nopadding"><span class="narrowmedia">Referring:  </span><select name="data-referring_physician" id="data-referring_physician" data-orthanc = "ReferringPhysicianName" class="searchparam"><option value="">ALL</option>' . $Referrers . '</select></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">Modality:  </span><select name="data-modality" data-orthanc = "Modality" class="searchparam">' . $modalities . '</select></div>
            <div class="col-sm-2 nopadding"><span class="narrowmedia">Study Date:  </span><input name="data-studydate" id="data-studydate" data-orthanc = "StudyDate" class="searchparam datepicker" type="text"></div>
            <div class="col-sm-2 nopadding"><span class="narrowmedia"> Report Status:  </span><select name="data-reportstatus" id="data-reportstatus" data-orthanc = "reportstatus" class="searchparam"><option value = "">ALL</option><option value = "NONE">NONE</option><option value = "PRELIM">PRELIM</option><option value = "FINAL">FINAL</option><option value = "ADDENDUM">ADDENDUM</option></select></div>
		</form>';
    }
    
    public static function studiesSearchFormDocs () {

        $modalities = DatabaseFactory::getModalitySelectList();
        $InstitutionNames = '';
    
    	return '<div class="row divtableheader widemedia">
			<div class="col-sm-2">Name</div>
			<div class="col-sm-1">DOB</div>
			<div class="col-sm-1">Sex</div>
			<div class="col-sm-1">MRN</div>
			<div class="col-sm-2">Accession</div>
			<div class="col-sm-2">Description</div>
			<div class="col-sm-1">Modality</div>
			<div class="col-sm-1">Study Date</div>
			<div class="col-sm-1">Report Status</div>
		</div>

		<form name="searchform" id="searchform" class="divtable row">

            <div class="col-sm-2 nopadding"><span class="narrowmedia">Name:  </span><input name="data-name" data-orthanc = "PatientName"  class="searchparam" type="text"></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">DOB:  </span><input name="data-dob" id="data-dob" data-orthanc = "PatientBirthDate" class="searchparam datepicker" type="text"></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">Sex:  </span><select name="data-sex" id="data-sex" data-orthanc = "PatientSex" class="searchparam"><option value="">All</option><option value="M">M</option><option value="F">F</option><option value="O">O</option></select></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">MRN:  </span><input name="data-mrn" data-orthanc = "PatientID"  class="searchparam" type="text"></div>
            <div class="col-sm-2 nopadding"><span class="narrowmedia">Accession:  </span><input name="data-accession" data-orthanc = "AccessionNumber" class="searchparam" type="text"></div>
            <div class="col-sm-2 nopadding"><span class="narrowmedia">Description:  </span><input name="data-description" data-orthanc = "StudyDescription" class="searchparam" type="text"></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">Modality:  </span><select name="data-modality" data-orthanc = "Modality" class="searchparam">' . $modalities . '</select></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">Study Date:  </span><input name="data-studydate" id="data-studydate" data-orthanc = "StudyDate" class="searchparam datepicker" type="text"></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia"> Report Status:  </span><select name="data-reportstatus" id="data-reportstatus" data-orthanc = "reportstatus" class="searchparam"><option value = "">ALL</option><option value = "NONE">NONE</option><option value = "PRELIM">PRELIM</option><option value = "FINAL">FINAL</option><option value = "ADDENDUM">ADDENDUM</option></select></div>
		</form>
		
		<div class="row divtableheader widemedia">
			<div class="col-sm-12">AdditionalPatientHistory</div>
		</div>
		<form name="searchform" class="searchform divtable row">
		    <div class="col-sm-3"></div>
            <div class="col-sm-6 nopadding"><span class="narrowmedia">AdditionalPatientHistory:  </span><input name="data-history" id="data-history" class="searchparam" data-orthanc = "0010,21b0" form="searchform"></div>
            <div class="col-sm-3"></div>
        </form>';
    }


    public static function studiesSearchForm () {
    
        $Referrers = DatabaseFactory::getReferrersSelectList('');
        $modalities = DatabaseFactory::getModalitySelectList();
        $InstitutionNames = PostgresOrthanc::getInstitutionNames();
    
    	return '<div class="row divtableheader widemedia">
			<div class="col-sm-1">Name</div>
			<div class="col-sm-1">Age</div>
			<div class="col-sm-1">DOB</div>
			<div class="col-sm-1">Sex</div>
			<div class="col-sm-1">MRN</div>
			<div class="col-sm-1">Accession</div>
			<div class="col-sm-1">Description</div>
			<div class="col-sm-1">Referrer</div>
			<div class="col-sm-1">Modality</div>
			<div class="col-sm-1">Images</div>
			<div class="col-sm-1">Study Date</div>
			<div class="col-sm-1">Report Status</div>
		</div>

		<form name="searchform" id="searchform" class="divtable row">

            <div class="col-sm-1 nopadding"><span class="narrowmedia">Name:  </span><input name="data-name" data-orthanc = "PatientName"  class="searchparam" type="text"></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">Age:  </span><input name="data-age" id="data-age" class="searchparam" type="text"></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">DOB:  </span><input name="data-dob" id="data-dob" data-orthanc = "PatientBirthDate" class="searchparam datepicker" type="text"></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">Sex:  </span><select name="data-sex" id="data-sex" data-orthanc = "PatientSex" class="searchparam"><option value="">All</option><option value="M">M</option><option value="F">F</option><option value="O">O</option></select></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">MRN:  </span><input name="data-mrn" data-orthanc = "PatientID"  class="searchparam" type="text"></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">Accession:  </span><input name="data-accession" data-orthanc = "AccessionNumber" class="searchparam" type="text"></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">Description:  </span><input name="data-description" data-orthanc = "StudyDescription" class="searchparam" type="text"></div>
             <div class="col-sm-1 nopadding"><span class="narrowmedia">Referring:  </span><select name="data-referring_physician" id="data-referring_physician" data-orthanc = "ReferringPhysicianName" class="searchparam"><option value="">ALL</option>' . $Referrers . '</select></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">Modality:  </span><select name="data-modality" data-orthanc = "Modality" class="searchparam">' . $modalities . '</select></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">Images:  </span><input name="data-images" id="data-images" class="searchparam" type="text"></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia">Study Date:  </span><input name="data-studydate" id="data-studydate" data-orthanc = "StudyDate" class="searchparam datepicker" type="text"></div>
            <div class="col-sm-1 nopadding"><span class="narrowmedia"> Report Status:  </span><select name="data-reportstatus" id="data-reportstatus" data-orthanc = "reportstatus" class="searchparam"><option value = "">ALL</option><option value = "NONE">NONE</option><option value = "PRELIM">PRELIM</option><option value = "FINAL">FINAL</option><option value = "ADDENDUM">ADDENDUM</option></select></div>
		</form>
		
		<div class="row divtableheader widemedia">
			<div class="col-sm-2"></div>
			<div class="col-sm-2">Institution</div>
			<div class="col-sm-2">RETIRED_OtherPatientIDs</div>
			<div class="col-sm-4">AdditionalPatientHistory</div>
			<div class="col-sm-2"></div>
		</div>
		<form name="searchform" class="searchform divtable row">
		    <div class="col-sm-2"></div>
            <div class="col-sm-2 nopadding"><span class="narrowmedia">InstitutionName:  </span><select name="data-institution_name" id="institution_name-status" class="searchparam" data-orthanc = "InstitutionName" form="searchform">' . $InstitutionNames . '</select></div>
            <div class="col-sm-2 nopadding"><span class="narrowmedia">OtherPatientIDs:  </span><input name="data-otherpatientids" id="data-otherpatientids" class="searchparam" data-orthanc = "0010,1000" form="searchform"></div>
            <div class="col-sm-4 nopadding"><span class="narrowmedia">AdditionalPatientHistory:  </span><input name="data-history" id="data-history" class="searchparam" data-orthanc = "0010,21b0" form="searchform"></div>
            <div class="col-sm-2"></div>
        </form>';
    }

    public static function dateRadioSelector() {
    	$timepicker = "";
    	foreach (Config::get('TIME_RANGE_OPTS') as $key => $value) {
    		$timepicker .= $key . ': <input class = "changedate" name = "changedate" type = "radio" value = "' . $value . '">';

    	}

    	return '
            <div class="startenddate">
            <div class = "col-sm-12">
            <span>Begin Date</span><input type="text" class="rangedates datepicker begindate" value="" name="begindate"><input type="text" class="rangedates datepicker enddate" value="" name="enddate"><span>End Date</span>
            </div>
            <div class = "col-sm-12" style="text-align:center;">' . $timepicker . '      <button type="button" class ="clearsearchform uibuttonsmallred">Clear</button>
<button type="button" data-dbsearch class="uibuttonsmallred">Search Database</button></div></div>';

    }

    public static function dateRadioSelectorStudies($formname, $worklistcontainer, $dbsearch) {

    	return '
            <div class="startenddate">
            <div class = "col-sm-12">
            <span>Begin Date</span><input form = "' . $formname . '" type="text" class="rangedates datepicker begindate" value="" name="begindate"><input form = "' . $formname . '" type="text" class="rangedates datepicker enddate" value="" name="enddate"><span>End Date</span>
            </div>
            <div data-searchdb = "' . $dbsearch . '" class = "col-sm-12" style="text-align:center;">ALL: <input class = "changestudydate" name = "changestudydate" type = "radio" value = "0">10 years: <input class = "changestudydate" name = "changestudydate" type = "radio" value = "3650">  1 year: <input class = "changestudydate" name = "changestudydate" type = "radio" value = "365">  30 days:  <input class = "changestudydate" name = "changestudydate" type = "radio" value = "30">  7 days:  <input class = "changestudydate" name = "changestudydate" type = "radio" value = "7">  1 day:  <input class = "changestudydate" name = "changestudydate" type = "radio" value = "NOW">&nbsp;&nbsp;<input type="button" class ="clearsearchform uibuttonsmallred" value="Clear" title="Clear" data-target = "'  . $worklistcontainer .  '">
                        <button type="button" form = "' . $formname . '" class="uibuttonsmallred" data-dbsearch = "' . $dbsearch . '" data-target = "'  . $worklistcontainer .  '">Search Database</button></div></div>';

    }

    public static function orderSearchFormDocs() {

    return '<div class="row divtableheader widemedia">
	<div class="col-sm-2">Name</div>
	<div class="col-sm-2">MRN</div>
	<div class="col-sm-2">Accession</div>
	<div class="col-sm-2">Description</div>
	<div class="col-sm-1">Date</div>
	<div class="col-sm-1">Modality</div>
	<div class="col-sm-1">Status</div>
	<div class="col-sm-1">READ</div>
	</div>
	<div class="divtableheader row">
	<div class="col-sm-2 nopadding"><span class="narrowmedia">Name:  </span><input name="data-name" class="searchparam" type="text"></div>
	<div class="col-sm-2 nopadding"><span class="narrowmedia">MRN:  </span><input name="data-mrn" class="searchparam" type="text"></div>
	<div class="col-sm-2 nopadding"><span class="narrowmedia">Accession:  </span><input name="data-accession" class="searchparam" type="text"></div>
	<div class="col-sm-2 nopadding"><span class="narrowmedia">Description:  </span><input name="data-description" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Date:  </span><input name="data-date" id="data-date" class="searchparam datepicker" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Modality:  </span><input name="data-modality" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Status:  </span><select name="data-status" id="data-status" class="searchparam"><option value = "">ALL</option><option value = "NW">NW / New</option><option value = "XO">XO / Mod</option><option value = "SC">SC / Scheduled</option><option value = "IP">IP / Progress</option><option value = "CM">CM / Complete</option><option value = "CA">CA / Cancelled</option><option value = "NS">NS / No Show</option></select></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">READ:  </span><select name="data-read" id="data-read" class="searchparam"><option value = "">ALL</option><option value = "NONE">NONE</option><option value = "PRELIM">PRELIM</option><option value = "FINAL">FINAL</option><option value = "ADDENDUM">ADDENDUM</option></select></div></div>';
    }

    public static function requestsSearchForm() {

    return '<div class="row divtableheader widemedia">
	<div class="col-sm-1">Last</div>
	<div class="col-sm-1">First</div>
	<div class="col-sm-1">MRN</div>
	<div class="col-sm-1">DOB</div>
	<div class="col-sm-1">E-mail</div>
	<div class="col-sm-2">Phone Suffix</div>
	<div class="col-sm-2">Description</div>
	<div class="col-sm-1">Date</div>
	<div class="col-sm-1">Modality</div>
	<div class="col-sm-1">Referrer</div>

	</div>
	<div class="divtable row">
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Last:  </span><input name="data-lname" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">First:  </span><input name="data-fname" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">MRN:  </span><input name="data-patientid" id="data-patientid" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">DOB:  </span><input name="data-patient_birth_date" id="data-patient_birth_date" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">E-mail:  </span><input name="data-patient_email" id="data-patient_email" class="searchparam" type="text"></div>
	<div class="col-sm-2 nopadding"><span class="narrowmedia">Phone:  </span><input name="data-patient_phone" id="data-patient_phone" class="searchparam" type="text"></div>
	<div class="col-sm-2 nopadding"><span class="narrowmedia">Description:  </span><input name="data-requested_procedure" id="data-requested_procedure" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Date:  </span><input name="data-date" id="data-date" class="searchparam datepicker" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Modality:  </span><input name="data-modality" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Referrer:  </span><input name="data-referring_physician" id="data-referring_physician" class="searchparam" type="text"></div>
</div>';
    }

    public static function orderSearchFormOrders() {

    	return '<div class="row divtableheader widemedia">
	<div class="col-sm-1">Last</div>
	<div class="col-sm-1">First</div>
	<div class="col-sm-1">MRN</div>
	<div class="col-sm-1">Accession</div>
	<div class="col-sm-2">Description</div>
	<div class="col-sm-1">ApptID</div>
	<div class="col-sm-1">Referrer</div>
	<div class="col-sm-1">Date</div>
	<div class="col-sm-1">Modality</div>
	<div class="col-sm-1">Status</div>
	<div class="col-sm-1">READ</div>
	</div>
	<div class="divtable row">
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Last:  </span><input name="data-lname" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">First:  </span><input name="data-fname" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">MRN:  </span><input name="data-mrn" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Accession:  </span><input name="data-accession" class="searchparam" type="text"></div>
	<div class="col-sm-2 nopadding"><span class="narrowmedia">Description:  </span><input name="data-description" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Description:  </span><input name="data-apptid" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Referrer:  </span><input name="data-referring_physician" id="data-referring_physician" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Date:  </span><input name="data-date" id="data-date" class="searchparam datepicker" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Modality:  </span><input name="data-modality" class="searchparam" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Status:  </span><select name="data-status" id="data-status" class="searchparam"><option value = "">ALL</option><option value = "NW">NW / New</option><option value = "XO">XO / Mod</option><option value = "SC">SC / Scheduled</option><option value = "IP">IP / Progress</option><option value = "CM">CM / Complete</option><option value = "CA">CA / Cancelled</option><option value = "NS">NS / No Show</option></select></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">READ:  </span><select name="data-read" id="data-read" class="searchparam"><option value = "">ALL</option><option value = "NONE">NONE</option><option value = "PRELIM">PRELIM</option><option value = "FINAL">FINAL</option><option value = "ADDENDUM">ADDENDUM</option></select></div></div>';
    }

    public static function orderSearchFormPatients() {

    return '<div class="row divtableheader widemedia">
	<div class="col-sm-2">Accession</div>
	<div class="col-sm-3">Description</div>
	<div class="col-sm-2">Date</div>
	<div class="col-sm-1">Modality</div>
	<div class="col-sm-2">Status</div>
	<div class="col-sm-2">READ</div>
	</div>
	<div class="divtable row">

	<div class="col-sm-2 nopadding"><span class="narrowmedia">Accession:  </span><input name="data-accession" class="searchparam" type="text"></div>
	<div class="col-sm-3 nopadding"><span class="narrowmedia">Description:  </span><input name="data-description" class="searchparam" type="text"></div>
	<div class="col-sm-2 nopadding"><span class="narrowmedia">Date:  </span><input name="data-date" id="data-date" class="searchparam datepicker" type="text"></div>
	<div class="col-sm-1 nopadding"><span class="narrowmedia">Modality:  </span><select name="data-modality" data-orthanc="Modality" class="searchparam"><option value="">All</option><option value="MR">MR</option>&gt;<option value="PT">PT</option><option value="CT">CT</option><option value="US">US</option><option value="NM">NM</option><option value="CR">CR</option></option><option value="OT">OT</option><</select></div>
	<div class="col-sm-2 nopadding"><span class="narrowmedia">Status:  </span><select name="data-status" id="data-status" class="searchparam"><option value = "">ALL</option><option value = "NW">NW / New</option><option value = "XO">XO / Mod</option><option value = "SC">SC / Scheduled</option><option value = "IP">IP / Progress</option><option value = "CM">CM / Complete</option><option value = "CA">CA / Cancelled</option><option value = "NS">NS / No Show</option></select></div>
	<div class="col-sm-2 nopadding"><span class="narrowmedia">READ:  </span><select name="data-read" id="data-read" class="searchparam"><option value = "">ALL</option><option value = "NONE">NONE</option><option value = "PRELIM">PRELIM</option><option value = "FINAL">FINAL</option><option value = "ADDENDUM">ADDENDUM</option></select></div></div>';
    }

    public static function studiesContainer() {

    	return '<!-- DIV FOR THE PAGINATION THINGY RETURNED FROM THE PYTHON SCRIPT -->
		<div id = "widget"></div>
		<div class="row divtable widemedia worklistheader" data-sorttarget = "#studieswrapper">
			<div class="col-sm-3 nopadding">
				<div class="col-sm-6 padding" data-sort-param="data-name" data-sort-order="up"><span>Name / View</span></div>
				<div class="col-sm-6 padding" data-sort-param="data-age" data-sort-order="up"><span>Age/DOB/Reports</span></div>
			</div>
			<div class="col-sm-3 nopadding">
				<div class="col-sm-2 padding" data-sort-param="data-sex" data-sort-order="up"><span>Sex</span></div>
				<div class="col-sm-5 padding" data-sort-param="data-mrn" data-sort-order="up"><span>MRN</span></div>
				<div class="col-sm-5 padding" data-sort-param="data-accession" data-sort-order="up"><span>Accession</span></div>
			</div>
			<div class="col-sm-3 nopadding">
				<div class="col-sm-8 padding" data-sort-param="data-description" data-sort-order="up"><span>Description</span></div>
				<div class="col-sm-2 padding" data-sort-param="data-modality" data-sort-order="up"><span>Type</span></div>
				<div class="col-sm-2 padding" data-sort-param="data-images" data-sort-order="up"><span>#</span></div>
			</div>
			<div class="col-sm-3 nopadding">
			<div class="col-sm-4 padding" data-sort-param="data-indication" data-sort-order="up"><span>History</span></div>
			<div class="col-sm-8 padding" data-sort-param="data-studydate" data-sort-order="down"><span>Study Date</span></div>
			</div>
		</div>
		<div id="studieswrapper"></div>';
    }

    public static function checkAuth($path) {

		self::$omitdelete = true;
		$validuserdocs = false;
		// patient privileges, patient and in their directory.
		if ((count(array_intersect(Session::get("user_roles"), [1])) > 0)) {
			$validroot = Config::get('PATH_USERDOCS_ROOT') . Session::get('patientid');
			$validuserdocs = (strpos($path, $validroot) !== false);
			$text = $validuserdocs?"true":"false";
			self::$omitdelete = true;
			Log::info('root:  ' . $validroot . '  path:'  . $path . '  validpath:' .$text  );
		}

		$validstaffforuser = false;
		// staff privileges for user patient docs
		if ((count(array_intersect(Session::get("user_roles"), [3,4,5,6,7,8])) > 0)) {
			$validroot = Config::get('PATH_USERDOCS_ROOT');
			$validstaffforuser = (strpos($path, $validroot) !== false);
			if ($path == $validroot) $validstaffforuser = false;  // don't allow browsing the root folder
			// could set to allow superuser to do that.
			$text = $validstaffforuser?"true":"false";
			Log::info('root:  ' . $validroot . '  path:'  . $path . '  validpath:' .$text  );
			self::$omitdelete = false;
		}

		$validreferrerdocs = false;
		// referrer privileges, referrer, for referrerdocs
		if ((count(array_intersect(Session::get("user_roles"), [2])) > 0)) {
			$validroot = Config::get('PATH_REFERRERDOCS_ROOT');
			$validreferrerdocs = (strpos($path, $validroot) !== false);
			$text = $validreferrerdocs?"true":"false";
			self::$omitdelete = true;
			Log::info('root:  ' . $validroot . '  path:'  . $path . '  validpath:' .$text  );
		}

		$validstaffdocs = false;
		// staffdocs.
		if ((count(array_intersect(Session::get("user_roles"), [3,4,5,6,7,8])) > 0)) {
			$validroot = Config::get('PATH_STAFFDOCS_ROOT');
			$validstaffdocs = (strpos($path, $validroot) !== false);
			$text = $validstaffdocs?"true":"false";
			Log::info('root:  ' . $validroot . '  path:'  . $path . '  validpath:' .$text  );
			self::$omitdelete = false;
		}
		self::$auth = ($validuserdocs || $validstaffforuser || $validreferrerdocs || $validstaffdocs);

	}

    public static function listFilesInPath($path, $callback) {

    	// callback is for the billing page mostly, execute after deleting
    	$errors = "";
    	Log::info($path);
    	// checkAuth sets the $omitdelete based on access level.
    	$authorized = self::checkAuth($path);
		if (!self::$auth) {

			$errors .= 'Not authorized.  ';
		}

    	if ($errors == "" && !file_exists($path)) {
    		$errors .= 'Directory does not exist.';
		}
		if ($errors != "") {
			if ($_SERVER['REQUEST_METHOD'] == "GET") {
				echo '<span class="errormessage">' . $errors . '</span>';
				// don't die because it'll kill a GET load
			}
			else {
				echo '[{"error":"' . $errors . '"}]';
				// for the jQuery error handler
			}
			return false;

		}
		else {

		$files = array_values(preg_grep('/^([^.])/', scandir($path)));
		$filelist = [];

		foreach ($files as  $key => $file_folder) {

		if (is_file($path  . $file_folder)) {
		$filelist[$file_folder]["type"] = "file";
		$filelist[$file_folder]["mime type"] = mime_content_type($path . $file_folder);
		}
		else if (is_dir($path . $file_folder)) {
		$filelist[$file_folder]["type"] = "directory";

		}

		}
		uasort($filelist, function($a, $b) {
    		return $a['type'] > $b['type'];
		});

		$html =  '<div id="filelist">';
		$html .= '<h4 style = "text-align:center;margin:auto;">File List for Folder ' . basename($path)  .  '</h4><hr>';
		$html .= '<div class = "row"><div class = "type col-sm-4">Name</div><div class = "type col-sm-2">Type</div><div class = "type col-sm-6">Action</div></div>';

		if (!empty($path)) $html .= '<div class = "row"><div class = "type col-sm-4">../</div><div class = "type col-sm-2">Parent Directory</div><div class = "type col-sm-6"><button data-path = "' . $path . '" type="button" style = "font-size:9px;" class="uibuttonsmallred upfolder">Up</button></div></div>';


		foreach ($filelist as $key => $value) {
		$html .= '<div class = "row">';
		$html .= '<div class = "type col-sm-4">' . $key . '</div>';
		if ($value['type'] == "directory") $icon = '<i class="fas fa-folder-open"></i>';
		if ($value['type'] == "file") $icon = '<i class="fas fa-file"></i>';
		$html .= '<div class = "type col-sm-2">' . $icon . '</div>';
		// File

		if ($value['type'] == "file") {
		$html .= '<div class = "type col-sm-6"><button data-path = "' . $path . $key. '" type="button" style = "font-size:9px;" class="uibuttonsmallred viewwidgetdoc">View Document</button>';
		$html .= '<button data-path = "' . $path . $key. '" type="button" style = "font-size:9px;" class="uibuttonsmallred downloadwidgetdoc">Download Document</button>';
		if (!self::$omitdelete) {
		$html .= '<button data-callback = "' . $callback. '" data-path = "' . $path . $key. '" type="button" style = "font-size:9px;" class="uibuttonsmallred deletewidgetdoc">Delete Document</button>';
		}
		$html .= '</div></div>';
		}
		// Folder
		else {
			$html .= '<div class = "type col-sm-6">
			<button data-path = "' . $path  . $key. '/' . '" type="button" style = "font-size:9px;" class="uibuttonsmallred openfolder">Open</button></div></div>';
		}
		}

		$html .= '</div>';
		return $html;
		}
    }

    public static function ICD10DX() {

        return '<a href = "https://www.icd10data.com" target="_blank" class = "uibuttonsmallred" style = "display:block;margin-bottom:-20px;">ICD10data.com</a><div class = "widget uibuttonsmallred" style = "cursor:pointer;" >Search Diagnosis Code ICD-10</div><form style="display:none;" class="form-horizontal" role="form">
<input name = "codetype" data-codetype = "dx" type="hidden" value = "dx" />
<input autocomplete="off" class = "col-sm-2 livesearch" name = "searchfor[]"  data-controller = "/Utilities/searchicd10" data-codetype = "dx" type="text" placeholder="Search" />
<input autocomplete="off" class = "col-sm-2 livesearch" name = "searchfor[]"  data-controller = "/Utilities/searchicd10" data-codetype = "dx" type="text" placeholder="Search" />
<input autocomplete="off" class = "col-sm-2 livesearch" name = "searchfor[]"  data-controller = "/Utilities/searchicd10" data-codetype = "dx" type="text" placeholder="Search" />
<div class ="searchresults"></div>
<button id = "addicddx">Add ICD DX:<i class="fas fa-plus"></i></button></form>';
    }

    public static function ICD10Proc() {

        return '<a tabindex="0" data-content="Click text to toggle ICD-10 procedures code tool" data-toggle="popover" data-trigger="focus" title=""></a>
        <div class = "widget" style = "cursor:pointer;">Procedure Code ICD-10</div><form style="display:none;" class="form-horizontal" role="form">
<input name = "codetype" data-codetype = "dx" type="hidden" value = "pcs" />
<input autocomplete="off" class = "col-sm-2 livesearch" name = "searchfor[]"  data-controller = "/Utilities/searchicd10" data-codetype = "pcs" type="text" placeholder="Search" />
<input autocomplete="off" class = "col-sm-2 livesearch" name = "searchfor[]"  data-controller = "/Utilities/searchicd10" data-codetype = "pcs" type="text" placeholder="Search" />
<input autocomplete="off" class = "col-sm-2 livesearch" name = "searchfor[]"  data-controller = "/Utilities/searchicd10" data-codetype = "pcs" type="text" placeholder="Search" />
<input data-codetype = "pcs" data-input = "icd10pcs" type="text" placeholder="Result" />
<div class ="searchresults"></div>
<button id = "addicdproc">Add IDC Proc:<i class="fas fa-plus"></i></button></form>	';
    }

    public static function RX() {

        return '<div class = "widget uibuttonsmallred" style = "cursor:pointer;">Search RXNORM</div><form style="display:none;" class="form-horizontal" role="form">

<input name = "codetype" data-codetype = "rx" type="hidden" value = "rx" />
<input autocomplete="off" class = "col-sm-2 livesearch" data-controller = "/Utilities/searchRX" data-codetype = "rx" name = "searchfor[]" type="text" placeholder="Name" />
<input autocomplete="off" class = "col-sm-2 livesearch" data-controller = "/Utilities/searchRX" data-codetype = "rx" name = "searchfor[]" type="text" placeholder="Name" />
<input autocomplete="off" class = "col-sm-2 livesearch" data-controller = "/Utilities/searchRX" data-codetype = "rx" name = "searchfor[]" type="text" placeholder="Name" />
<input autocomplete="off" class = "col-sm-2 livesearch" data-controller = "/Utilities/searchRX" data-codetype = "rx" name = "searchfor[]" type="text" placeholder="Name" />
<input data-codetype = "rx" data-input = "rx" type="text" placeholder="Result" />
<div class ="searchresults"></div>
<button id = "addrx">Add RX:<i class="fas fa-plus"></i></button></form>';
    }

    public static function serverStatus() {

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
$orthanc = new OrthancAPI();
$status = $orthanc->ServerStatus(); // RETURNS THE DATE IF IT MAKES IT THROUGH TO ORTHANC
Log::info($status);
if ($orthanc->curlerror !== false) {

	$status = $orthanc->curl_error_text .'<div class="led-red"></div>';
}
else {
	$dt = \DateTime::createFromFormat("Ymd", substr($status ,0, 8)); // 20210220T124946
	if (!$dt) {
		$status = json_decode($status)->curl_error .'<div class="led-red"></div>';
	}
	else  {
	$status = 'Connected to ' . $orthanc->server->server_name . '<div class="led-green"></div>';
	}
}
$status .= '</div>';
$html .= $status;
return $html;


    }

    public static function studiesLengend() {

        $html =  '
<div style = "display:inline-block;" id = "togglelegend" class = "uibuttonsmallred">Toggle Legend</div><div id="studieslegend" style = "display:none;">
<div class="row">
<div class = "col-sm-12 iconinfo">
<div>Click on an Icon below for more information</div>
<a tabindex="0" data-content="Click on the Icon in the far left column below to view a study in the browser" data-toggle="popover" data-trigger="focus"><img src="/images/view_images.png" >View Study</a>

<a tabindex="0" data-content="Click on the reports Icon below to view a report for the study if one exists.  The status must be PRELIM, FINAL or ADDENDUM." data-toggle="popover" data-trigger="focus"><img src="/images/report.png" >Reports</a>

<a tabindex="0" data-content="Click on the paperclip icon to attach a .pdf, .png, or .jpg document to a study."  data-toggle="popover" data-trigger="focus"><img src="/js/create_dicom/img/studydoc.png" alt="CreateDicom">Add Document</a>

<a tabindex="0" data-toggle = "popover" data-trigger="focus" data-content = "Click on the Icon below to send a study to one of the configured destinations."><span class="uibuttonsmallred" style="padding-right:5px;">Fetch</span></a>

<a tabindex="0" data-content="The &ldquo;.DCM&rdquo; download contains the images and a DICOMDIR file for the study, as a .zip file." data-toggle="popover" data-trigger="focus"><span class="uibuttonsmallred">"DCM"</span></a>

<a tabindex="0" data-content="The &ldquo;ZIP&rdquo; download contains the raw dicom files." data-toggle="popover" data-trigger="focus"><span class="uibuttonsmallred">"ZIP"</span></a>

<a tabindex="0" data-content="Click on the Icon below to display a list of all studies for a patient" data-toggle = "popover" data-trigger="focus"><span class="uibuttonsmallred">"ALL"</span></a>
</div>
</div>
<div class="row" style = "margin:20px 0px 20px 0px;">
<div class = "col-sm-12 iconinfo">
<a tabindex="0" data-content="If present, the &ldquo;Share Note&rdquo; button displays the shared message for the shared study." data-toggle="popover" data-trigger="focus"><span class="uibuttonsmallred">"Share Note"</span></a>
<a tabindex="0" data-content="If present, the &ldquo;Share&rdquo; button allows you to share the study with another provider in the list of providers. " data-toggle="popover" data-trigger="focus"><span class="uibuttonsmallred">"Share"</span></a>

<a tabindex="0" data-content="If present, the &ldquo;Patient History&rdquo; button will display the patient diagnoses, medications, and brief medical history." data-toggle="popover" data-trigger="focus"><span class="uibuttonsmallred">"Patient History"</span></a>

</div>
</div></div>';
return $html;
}

	public static function studyRowSelectorPatients() {

		$html = '<form id = "studycountselect" style="display:inline-block;"><select id = "itemsperpage" name = "itemsperpage">';

		    foreach (config('myconfigs.STUDY_COUNT_ARRAY') as $option) {

		    	$html .= '<option value = "' . $option . '">' . $option . '  Row(s) Per Page</option>';
		    }

            $html .= '</select><select id = "sortparam" name = "sortparam">
			<option value = "AccessionNumber" >AccessionNumber</option>
			<option value = "StudyDescription" >StudyDescription</option>
			<option value = "StudyDate">StudyDate</option>
			<option value = "ReferringPhysicianName" >ReferringPhysicianName</option>
		</select><select id = "reverse" name = "reverse">
			<option value = "0" >Ascending</option>
			<option value = "1">Descending</option>
		</select></form>';
            return $html;

	}

	public static function studyRowSelector() {

		$html = '<form id = "studycountselect" style="display:inline-block;"><select id = "itemsperpage" name = "itemsperpage">';

		    foreach (config('myconfigs.STUDY_COUNT_ARRAY')as $option) {
		    	$html .= '<option value = "' . $option . '">' . $option . '  Row(s) Per Page</option>';
		    }

            $html .= '</select>		<select id = "sortparam" name = "sortparam">
			<option value = "PatientName" >PatientName</option>
			<option value = "PatientID">PatientID</option>
			<option value = "AccessionNumber" >AccessionNumber</option>
			<option value = "StudyDescription" >StudyDescription</option>
			<option value = "StudyDate">StudyDate</option>
			<option value = "ReferringPhysicianName" >ReferringPhysicianName</option>
		</select>		<select id = "reverse" name = "reverse">
			<option value = "0" >Ascending</option>
			<option value = "1">Descending</option>
		</select></form>';
            return $html;

	}

    public static function pagination ($limit, $current_page, $count) {

	$total_pages = ceil($count / $limit);
	$links = '<div>';
    if ($total_pages >= 1 && $current_page <= $total_pages) {
    	$active = "";
    	if ($current_page == 1) $active = "pageactive";
        $links .= '<a data-page = "1" class = "' . $active  . '" href="">1</a>';
        $active = "";
        $i = max(2, $current_page - 5);
        if ($i > 2) $links .= " ... ";
        for ($i = $i; $i < min($current_page + 6, $total_pages); $i++) {
        	if ($current_page == $i) $active = "pageactive";
            $links .= '<a data-page = "' . $i . '" class = "' . $active  . '"  href="">' . $i . '</a>';
            $active = "";
        }
        if ($i != $total_pages) $links .= " ... ";
        if ($current_page == $total_pages) $active = "pageactive";
        $links .= '<a data-page = "' . $total_pages . '" class = "' . $active  . '" href="">' . $total_pages . '</a>';
    }
    $links .= '<span class = "totalperpage"> Total per page:  ' . $limit . '</span>';
    $links .= '</div>';
    return $links;
    }

}
?>
