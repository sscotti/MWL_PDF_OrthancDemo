<?php

namespace App\Models\Patients;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use \DB;

/**
 * @property string     $mrn
 * @property string     $last
 * @property string     $first
 * @property string     $mname
 * @property string     $alias
 * @property string     $icd10codes
 * @property string     $medications
 * @property string     $surgical_history
 * @property string     $medical_history
 * @property string     $medications_text
 * @property Date       $birth_date
 * @property string     $sex
 * @property string     $mobile_phone_country
 * @property string     $mobile_phone
 * @property string     $alt_mobile_phone_country
 * @property string     $allergies
 * @property string     $alt_mobile_phone
 * @property string     $email
 * @property string     $alt_email
 * @property string     $address_1
 * @property string     $address_2
 * @property string     $city
 * @property string     $state
 * @property string     $country
 * @property string     $postal
 * @property string     $patient_notes
 * @property string     $uuid
 * @property DateTime   $date_created
 * @property string     $marital_status
 * @property int        $facility_id
 * @property boolean    $appt_reminders
 * @property boolean    $reports_notification
 * @property boolean    $send_reports
 */
class Patients extends Model

{

    protected $connection= 'mysql2';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'patients';

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
        'mrn', 'last', 'first', 'mname', 'alias', 'icd10codes', 'medications', 'surgical_history', 'medical_history', 'medications_text', 'birth_date', 'sex', 'mobile_phone_country', 'mobile_phone', 'alt_mobile_phone_country', 'allergies', 'alt_mobile_phone', 'email', 'alt_email', 'address_1', 'address_2', 'city', 'state', 'country', 'postal', 'patient_notes', 'uuid', 'date_created', 'marital_status', 'facility_id', 'appt_reminders', 'reports_notification', 'send_reports'
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
        'mrn' => 'string', 'last' => 'string', 'first' => 'string', 'mname' => 'string', 'alias' => 'string', 'icd10codes' => 'string', 'medications' => 'string', 'surgical_history' => 'string', 'medical_history' => 'string', 'medications_text' => 'string', 'birth_date' => 'date:Y-m-d', 'sex' => 'string', 'mobile_phone_country' => 'string', 'mobile_phone' => 'string', 'alt_mobile_phone_country' => 'string', 'allergies' => 'string', 'alt_mobile_phone' => 'string', 'email' => 'string', 'alt_email' => 'string', 'address_1' => 'string', 'address_2' => 'string', 'city' => 'string', 'state' => 'string', 'country' => 'string', 'postal' => 'string', 'patient_notes' => 'string', 'uuid' => 'string', 'date_created' => 'datetime', 'marital_status' => 'string', 'facility_id' => 'int', 'appt_reminders' => 'boolean', 'reports_notification' => 'boolean', 'send_reports' => 'boolean'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'birth_date', 'date_created'
    ];

    protected $dateFormat = 'Y-m-d';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    // Scopes...

    // Functions ...

    // Relations ...

    public function get($key)
    {
        return $this->$key;
    }
    public function set($key, $value)
    {
        return $this->$key = $value;
    }

    public static function patient_history($PatientID)
    {
        Log::info("patient_history");
        $patient = self::where(["mrn" => $PatientID])->first();
        Log::info( $patient);
        //return view('patients.history')
    }
    
    public static function getAvatar($mrn) {
    
        $query = "SELECT profile_photo_path FROM users WHERE patientid = ?";
        return  asset('storage/'. DB::connection('mysql')->select($query,[$mrn])[0]->profile_photo_path);
    }

}
