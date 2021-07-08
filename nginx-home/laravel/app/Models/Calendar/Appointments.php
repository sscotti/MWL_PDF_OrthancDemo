<?php

namespace App\Models\Calendar;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $fname
 * @property string     $lname
 * @property string     $accession_number
 * @property string     $order_id
 * @property string     $scheduled_station_aetitle
 * @property boolean    $device
 * @property string     $calendar_id
 * @property string     $mrn
 * @property string     $email
 * @property string     $alt_email
 * @property string     $mobile_phone_full
 * @property string     $alt_mobile_phone_full
 * @property Date       $start_date
 * @property Date       $end_date
 * @property int        $unix_start
 * @property int        $unix_end
 * @property string     $notes
 * @property int        $date_created
 * @property boolean    $reminder_sent
 * @property boolean    $repeat_flag
 * @property Date       $repeat_start
 * @property Date       $repeat_end
 * @property boolean    $allday
 * @property int        $repeat_interval
 * @property string     $repeat_year
 * @property string     $repeat_month
 * @property string     $repeat_day
 * @property string     $repeat_week
 * @property string     $repeat_weekday
 */
class Appointments extends Model
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
    protected $table = 'appointments';

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
        'fname', 'lname', 'accession_number', 'order_id', 'scheduled_station_aetitle', 'device', 'calendar_id', 'mrn', 'email', 'alt_email', 'mobile_phone_full', 'alt_mobile_phone_full', 'start_date', 'start_time', 'end_date', 'end_time', 'unix_start', 'unix_end', 'notes', 'status', 'date_created', 'reminder_sent', 'repeat_flag', 'repeat_start', 'repeat_end', 'allday', 'repeat_interval', 'repeat_year', 'repeat_month', 'repeat_day', 'repeat_week', 'repeat_weekday'
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
        'fname' => 'string', 'lname' => 'string', 'accession_number' => 'string', 'order_id' => 'string', 'scheduled_station_aetitle' => 'string', 'device' => 'boolean', 'calendar_id' => 'string', 'mrn' => 'string', 'email' => 'string', 'alt_email' => 'string', 'mobile_phone_full' => 'string', 'alt_mobile_phone_full' => 'string', 'start_date' => 'date', 'end_date' => 'date', 'unix_start' => 'int', 'unix_end' => 'int', 'notes' => 'string', 'date_created' => 'timestamp', 'reminder_sent' => 'boolean', 'repeat_flag' => 'boolean', 'repeat_start' => 'date', 'repeat_end' => 'date', 'allday' => 'boolean', 'repeat_interval' => 'int', 'repeat_year' => 'string', 'repeat_month' => 'string', 'repeat_day' => 'string', 'repeat_week' => 'string', 'repeat_weekday' => 'string'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'start_date', 'end_date', 'date_created', 'repeat_start', 'repeat_end'
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
