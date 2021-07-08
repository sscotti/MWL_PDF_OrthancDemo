<?php

namespace App\Models\Patients;

use Illuminate\Database\Eloquent\Model;
use \DB;

/**
 * @property int        $ins_id
 * @property string     $mrn
 * @property string     $ins_fname
 * @property string     $ins_lname
 * @property string     $ins_mname
 * @property Date       $ins_birth_date
 * @property string     $ins_sex
 * @property string     $ins_mobile_phone_country
 * @property string     $ins_mobile_phone
 * @property string     $ins_email
 * @property string     $ins_address_1
 * @property string     $ins_address_2
 * @property string     $ins_city
 * @property string     $ins_state
 * @property string     $ins_country
 * @property string     $ins_postal
 * @property string     $carrier_name
 * @property int        $carrier_id
 * @property string     $member_id
 * @property string     $group_id
 * @property string     $effective_date
 * @property string     $expiration_date
 * @property string     $priority
 * @property string     $relationship
 * @property string     $plan_name
 */
class Insurance extends Model
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
    protected $table = 'insurance';

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
        'ins_id', 'mrn', 'ins_fname', 'ins_lname', 'ins_mname', 'ins_birth_date', 'ins_sex', 'ins_mobile_phone_country', 'ins_mobile_phone', 'ins_email', 'ins_address_1', 'ins_address_2', 'ins_city', 'ins_state', 'ins_country', 'ins_postal', 'carrier_name', 'carrier_id', 'member_id', 'group_id', 'effective_date', 'expiration_date', 'priority', 'relationship', 'plan_name', 'co_pay_amount', 'co_pay_percent', 'deductible_amount'
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
        'ins_id' => 'int', 'mrn' => 'string', 'ins_fname' => 'string', 'ins_lname' => 'string', 'ins_mname' => 'string', 'ins_birth_date' => 'date', 'ins_sex' => 'string', 'ins_mobile_phone_country' => 'string', 'ins_mobile_phone' => 'string', 'ins_email' => 'string', 'ins_address_1' => 'string', 'ins_address_2' => 'string', 'ins_city' => 'string', 'ins_state' => 'string', 'ins_country' => 'string', 'ins_postal' => 'string', 'carrier_name' => 'string', 'carrier_id' => 'int', 'member_id' => 'string', 'group_id' => 'string', 'effective_date' => 'string', 'expiration_date' => 'string', 'priority' => 'string', 'relationship' => 'string', 'plan_name' => 'string'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'ins_birth_date'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    // Scopes...

    // Functions ...
    
    protected static function getPatientInsurancesByMRN ($mrn) {

		$insurances = self::where(["mrn" => $mrn])->orderBy('priority')->get();
		return $insurances;
    }

    // Relations ...
}
