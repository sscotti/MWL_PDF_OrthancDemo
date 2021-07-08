<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use \DB;

class PostgresOrthanc

{

	public static function getInstitutionNames() {
	
	    $query = "select DISTINCT value from maindicomtags where taggroup = 8 and tagelement = 128";
        $names = DB::connection('pgsql')->select($query,[]);
	    $html = '<option value="">All</option>';
	    foreach($names as $name) {
	        $html .='<option value="' . $name->value . '">' . $name->value . '</option>';
	    }
	    return $html;
	}

}
?>

