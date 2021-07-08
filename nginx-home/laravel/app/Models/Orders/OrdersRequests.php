<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $priority
 * @property boolean    $active
 * @property string     $patient_fname
 * @property string     $patient_lname
 * @property string     $patient_mname
 * @property Date       $patient_birth_date
 * @property string     $patient_sex
 * @property string     $patientid
 * @property string     $patient_email
 * @property string     $patient_phone_ctry
 * @property string     $patient_phone
 * @property string     $referring_physician_id
 * @property string     $referring_physician_fname
 * @property string     $referring_physician_lname
 * @property string     $referring_physician_phone_ctry
 * @property string     $referring_physician_phone
 * @property string     $referring_physician_email
 * @property string     $provider_type_text
 * @property string     $indication
 * @property int        $orderedbyuser_id
 * @property string     $orderbyuser_name
 * @property DateTime   $timestamp
 * @property Date       $scheduled_procedure_step_start_date
 * @property string     $requested_procedure_id
 * @property string     $related_employment
 * @property string     $related_auto
 * @property string     $related_otheraccident
 * @property string     $related_emergency
 * @property string     $related_drugs
 * @property Date       $related_pregnancy
 * @property string     $employed
 * @property string     $employed_student
 * @property string     $employed_other
 * @property Date       $illness_date
 */
class OrdersRequests extends Model
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
    protected $table = 'orders_requests';

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
        'priority', 'active', 'patient_fname', 'patient_lname', 'patient_mname', 'patient_birth_date', 'patient_sex', 'patientid', 'patient_email', 'patient_phone_ctry', 'patient_phone', 'referring_physician_id', 'referring_physician_fname', 'referring_physician_lname', 'referring_physician_phone_ctry', 'referring_physician_phone', 'referring_physician_email', 'provider_type_text', 'indication', 'orderedbyuser_id', 'orderbyuser_name', 'timestamp', 'scheduled_procedure_step_start_date', 'scheduled_procedure_step_start_time', 'requested_procedure_id', 'related_employment', 'related_auto', 'related_otheraccident', 'related_emergency', 'related_drugs', 'related_pregnancy', 'employed', 'employed_student', 'employed_other', 'illness_date'
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
        'priority' => 'string', 'active' => 'boolean', 'patient_fname' => 'string', 'patient_lname' => 'string', 'patient_mname' => 'string', 'patient_birth_date' => 'date:Y-m-d', 'patient_sex' => 'string', 'patientid' => 'string', 'patient_email' => 'string', 'patient_phone_ctry' => 'string', 'patient_phone' => 'string', 'referring_physician_id' => 'string', 'referring_physician_fname' => 'string', 'referring_physician_lname' => 'string', 'referring_physician_phone_ctry' => 'string', 'referring_physician_phone' => 'string', 'referring_physician_email' => 'string', 'provider_type_text' => 'string', 'indication' => 'string', 'orderedbyuser_id' => 'int', 'orderbyuser_name' => 'string', 'timestamp' => 'datetime', 'scheduled_procedure_step_start_date' => 'date:Y-m-d', 'requested_procedure_id' => 'string', 'related_employment' => 'string', 'related_auto' => 'string', 'related_otheraccident' => 'string', 'related_emergency' => 'string', 'related_drugs' => 'string', 'related_pregnancy' => 'date', 'employed' => 'string', 'employed_student' => 'string', 'employed_other' => 'string', 'illness_date' => 'date'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'patient_birth_date', 'timestamp', 'scheduled_procedure_step_start_date', 'related_pregnancy', 'illness_date'
    ];
    
    protected $dateFormat = 'Y-m-d';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;

    // Scopes...

    // Functions ...

    // Relations ...
}
