<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use \DB;

class DatabaseFactory

{

    public static function insertarray($table, $array) {  // passing in the object or array, table and items to omit,  adding new patient / row in database
    
        if (gettype($array) != "array") $array = (array)$array;
        $result = DB::connection('mysql2')->table($table)->insertGetId($array);
        echo '{"status":"' . $result . '"}'; 
    }

    public static function getOrderPriorities ($selected = null) {

    	$query = "SELECT * from order_priorities ORDER BY id";
        $fetch = DB::connection('mysql2')->select($query,[]);
        $priority = '<option disabled selected value="">Select option</option>';
        foreach ($fetch as $row) {
            $priority .= '<option value="' . $row->hl7_code . '"';
            if ($selected == $row->hl7_code) {
                $priority.= ' selected';
            }
            $priority.= '>' . $row->text . '</option>';
        }
        Log::info($priority);
        return $priority;

    }
    public static function getReferrersSelectList($identifier) {

        $query = "SELECT * from referring_physician ORDER BY lname";
        $referrers = DB::connection('mysql2')->select($query,[]);
    	$html ="";
        foreach ($referrers as $row) {

            $html .= '<option value="' . $row->identifier . '"';
            if ($identifier == $row->identifier) $html .= "selected";
            $html .= '>'. $row->identifier . '-' . $row->lname . '^' . $row->fname . '</option>';

        }
        return $html;
    }
    
    public static function getModalitySelectList() {

        $query = "SELECT * from modalities ORDER BY modality";
        $result = DB::connection('mysql2')->select($query,[]);
        $html = '<option value ="" selected>ALL</option>';
        foreach($result as $modality) {
            $html.= '<option value ="' . $modality->modality .  '">' . $modality->modality . '</option>';
        }
        return $html;

    }

    public static function getAppointentsByAccessionNumber($accession_number) {

        $query = 'SELECT id from appointments WHERE accession_number = ?';
        $params = [$accession_number];
        $result = DB::connection('mysql2')->select($query,$params);
        return $result;

    }

    public static function getDeviceList () {

		$query = 'SELECT id, device_id, device_name, modality, scheduled_station_aetitle from calendars WHERE device = 1 AND active = 1';
		$result = DB::connection('mysql2')->select($query,[]);
        return $result;
    }

    public static function getExamList() {

		$query = "SELECT * from exams ORDER BY 'cpt'";
		$result = DB::connection('mysql2')->select($query,[]);
        return $result;

    }
    
    public static function getPhoneCountries($selected = null, $option = true, $codetonumber = false) {

        // selected is the one selected, $options is to generate the select list, $codetonumber is to return the number for an ISO code

        if ($option == true) {

        $query = "SELECT * from country_dialing_codes ORDER BY display_order";
        $dialingcodes = DB::connection('mysql2')->select($query,[]);

        $result = '<option disabled selected value="">
					Select Country
		</option>';
		foreach ($dialingcodes as $row) {

				$result.= '<option value="' . $row->iso . '"';
            if ($selected == $row->iso) {
                $result.= ' selected';
            }
                $result.= '>' . $row->nicename . ' - ' . $row->phonecode . ' - ' . $row->iso . '</option>';
        }
        return $result;
        }
        // get just one
        
        else {
        $query = "SELECT phonecode from country_dialing_codes ORDER WHERE iso = ?";
        $prefix = DB::connection('mysql2')->select($query,[])->first();
        return $prefix;
        }

    }
    
    public static function getSpecialties() {
    
        $query = "SELECT * from specialties ORDER BY specialty";
        return DB::connection('mysql2')->select($query,[]);

    }
    
    public static function getStates($selected = null) {

        $query = "SELECT * from states ORDER BY name";
        $fetchstates = DB::connection('mysql2')->select($query,[]);
         $states = '<option disabled selected value="">
					Select option
		</option>';
        $states .= '<option value="OS">
					Outside Of USA
		</option>';
        foreach ($fetchstates as $row) {
            $states.= '<option value="' . $row->state . '"';
            if ($selected == $row->state) {
                $states.= ' selected';
            }
                $states.= '>' . $row->name . '</option>';
        }
        return $states;
    }
    
    public static function getCountries($selected = null) {

        $query = "SELECT * from countries ORDER BY display_order";
        $fetchcountries = DB::connection('mysql2')->select($query,[]);
        $countries = '<option disabled selected value="">
					Select option
		</option>';
        foreach ($fetchcountries as $row) {
            $countries.= '<option value="' . $row->country_iso . '"';
            if ($selected == $row->country_iso) {
                $countries.= ' selected';
            }
                $countries.= '>' . $row->country_name . '</option>';
        }
        return $countries;

    }
    
    public static function getProviderType($selected = null) {
    
    	$query = "SELECT * from provider_types ORDER BY description";
        $fetch = DB::connection('mysql2')->select($query,[]);
        $types = '<option disabled selected value="">
					Select option
		</option>';
        foreach ($fetch as $row) {
            $types .= '<option value="' . $row->id . '"';
            if ($selected == $row->id) {
                $types.= ' selected';
            }
            	$suffix = "";
            	if (!empty($row->degree)) $suffix = ', ' . $row->degree;
                $types.= '>' . $row->description . $suffix .  '</option>';
        }
        return $types;
    
    }
    
    public static function getlicenseTypes($selected = null) {

        $query = "SELECT * from license_types ORDER BY type";
        $fetchtypes = DB::connection('mysql2')->select($query,[]);
        $types = '<option selected disabled selected value="">
					Select option
		</option>';
        foreach ($fetchtypes as $row) {
            $types.= '<option value="' . $row->type . '"';
            if ($selected == $row->type) {
                $types.= ' selected';
            }
                $types.= '>' . $row->type . '</option>';
        }
        return $types;
    }
    
    public static function getTaxonomySelect($selected ) {
    
    	$query = "SELECT * from taxonomy ORDER BY Classification, Specialization";
        $fetchlist = DB::connection('mysql2')->select($query,[]);
    	$list = '<option disabled selected value="">
					Select option
		</option>';
    	foreach ($fetchlist as $row) {
    		$list .= '<option value ="' . $row->Code . '"';
    		if ($selected == $row->Code) {
                $list.= ' selected';
            }
            $list.= '>' . $row->Code . ' - ' . $row->Classification . ' - ' . $row->Specialization . '</option>';

    	
    	}
    	return $list;

    }
    
    public static function getExams() {

        $query = "SELECT * from exams ORDER BY cpt, exam_name, group_name";
        return DB::connection('mysql2')->select($query,[]);

    }
    
    public static function getMaritalStatusTypes ($selected = null) {
    
    	$query = "SELECT * from marital_types";
        $fetchlist = DB::connection('mysql2')->select($query,[]);
        $types = '<option disabled selected value="">
					Select option
		</option>';
        foreach ($fetchlist as $row) {
            $types .= '<option value="' . $row->hl7_code . '"';
            if ($selected == $row->hl7_code) {
                $types.= ' selected';
            }
        	$types.= '>' . $row->description .  '</option>';
        }
        return $types;
    
    }
    
    public static function getCarrierList($carrierid = false) {
    
        $query = "SELECT * from insurance_carriers ORDER BY carrier_name";
        $fetchlist = DB::connection('mysql2')->select($query,[]);
        $carriers = '<option disabled selected value="">
					Select option
		</option>';
        foreach ($fetchlist as $row) {
            $carriers .= '<option value="' . $row->id . '"';
            if ($carrierid == $row->id) {
                $carriers.= ' selected';
            }
        	$carriers.= '>' . $row->carrier_name .  '</option>';
        }
        return $carriers;
		
    }

}
?>
