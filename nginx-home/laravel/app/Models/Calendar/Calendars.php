<?php

namespace App\Models\Calendar;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string     $calendar_name
 * @property string     $device_name
 * @property int        $device_id
 * @property boolean    $device
 * @property string     $modality
 * @property string     $scheduled_station_aetitle
 * @property string     $MediaStorageSOPClassUID
 * @property boolean    $active
 */
class Calendars extends Model
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
    protected $table = 'calendars';

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
        'calendar_name', 'device_name', 'device_id', 'device', 'modality', 'scheduled_station_aetitle', 'MediaStorageSOPClassUID', 'active'
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
        'calendar_name' => 'string', 'device_name' => 'string', 'device_id' => 'int', 'device' => 'boolean', 'modality' => 'string', 'scheduled_station_aetitle' => 'string', 'MediaStorageSOPClassUID' => 'string', 'active' => 'boolean'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [

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
