<?php

namespace App\Models\Calendar;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $patientid
 * @property string     $referring_physician_id
 * @property int        $email_address
 * @property int        $appointment_id
 * @property int        $report_id_notifify
 * @property int        $report_id
 * @property int        $accession_number
 * @property boolean    $email_sent
 * @property DateTime   $sent_when
 * @property int        $errors
 */
class Reminders extends Model
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
    protected $table = 'reminders';

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
        'patientid', 'referring_physician_id', 'email_address', 'appointment_id', 'report_id_notifify', 'report_id', 'accession_number', 'email_sent', 'sent_when', 'errors'
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
        'patientid' => 'string', 'referring_physician_id' => 'string', 'email_address' => 'int', 'appointment_id' => 'int', 'report_id_notifify' => 'int', 'report_id' => 'int', 'accession_number' => 'int', 'email_sent' => 'boolean', 'sent_when' => 'datetime', 'errors' => 'int'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'sent_when'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    // Scopes...

    // Functions ...

    // Relations ...
}
